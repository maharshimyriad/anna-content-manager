<?php
/**
 * Dynamic page content for theme pages created via Anna Page Scaffolder.
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Scaffolded_Page_Content {

	/**
	 * Register meta boxes for scaffolded pages.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function register_scaffolded_page_meta_boxes( $post ) {
		if ( ! function_exists( 'anna_get_scaffolded_pages' ) ) {
			return;
		}

		foreach ( anna_get_scaffolded_pages() as $page ) {
			$slug     = $page['slug'] ?? '';
			$template = $page['template_file'] ?? '';
			$code     = $page['code'] ?? '';
			$title    = $page['title'] ?? '';

			if ( ! $slug || ! $template || ! $code ) {
				continue;
			}

			$matches = ( $slug === $post->post_name || $template === get_page_template_slug( $post->ID ) );
			if ( ! $matches ) {
				continue;
			}

			add_meta_box(
				'anna_content_scaffold_page_' . $code,
				sprintf(
					/* translators: %s: page title */
					__( 'Anna %s Page Content', 'anna-baylis' ),
					$title
				),
				array( $this, 'render_scaffolded_page_meta_box' ),
				'page',
				'normal',
				'high',
				array( 'page_config' => $page )
			);
		}
	}

	/**
	 * @param WP_Post $post Post object.
	 * @param array   $box  Meta box args.
	 */
	public function render_scaffolded_page_meta_box( $post, $box ) {
		$page = $box['args']['page_config'] ?? array();
		$code = $page['code'] ?? '';
		if ( ! $code ) {
			return;
		}

		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data   = $this->get_scaffold_page_content_with_defaults( $post->ID, $page );
		$this->maybe_backfill_scaffolded_page_meta( $post->ID, $data, $page );

		$group = 'anna_content_' . $code . '_page';

		echo '<p>' . esc_html__( 'Edit page copy and images. These fields override theme defaults for this page only.', 'anna-baylis' ) . '</p>';

		foreach ( $page['sections'] ?? array() as $section ) {
			echo '<h3>' . esc_html( (string) ( $section['label'] ?? '' ) ) . '</h3>';
			echo '<table class="form-table">';
			foreach ( $section['fields'] as $key => $field ) {
				$this->render_scaffolded_field_row( $group, $key, $field, $data[ $key ] ?? '' );
			}
			echo '</table>';
		}
	}

	/**
	 * @param string $group Field group.
	 * @param string $key   Field key.
	 * @param array  $field Field config.
	 * @param mixed  $value Value.
	 */
	private function render_scaffolded_field_row( $group, $key, $field, $value ) {
		$label = $field['label'] ?? $key;
		$type  = $field['type'] ?? 'text';

		switch ( $type ) {
			case 'textarea':
				$this->render_textarea_field( $group, $key, $label, (string) $value, 5 );
				break;
			case 'media':
				$this->render_media_field( $group, $key, $label, absint( $value ) );
				break;
			case 'url':
				$this->render_text_field( $group, $key, $label, (string) $value );
				break;
			case 'select':
				$this->render_scaffolded_select_field( $group, $key, $label, (string) $value, $field['choices'] ?? array() );
				break;
			default:
				$this->render_text_field( $group, $key, $label, (string) $value );
		}
	}

	/**
	 * @param string              $group   Field group.
	 * @param string              $key     Field key.
	 * @param string              $label   Label.
	 * @param string              $value   Value.
	 * @param array<string,string> $choices Choices.
	 */
	private function render_scaffolded_select_field( $group, $key, $label, $value, $choices ) {
		$id = sanitize_key( $group . '_' . $key );
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $group ); ?>[<?php echo esc_attr( $key ); ?>]">
					<?php foreach ( $choices as $choice_key => $choice_label ) : ?>
						<option value="<?php echo esc_attr( $choice_key ); ?>" <?php selected( $value, (string) $choice_key ); ?>><?php echo esc_html( $choice_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param int $post_id Post ID.
	 */
	private function save_scaffolded_page_content( $post_id ) {
		if ( ! function_exists( 'anna_get_scaffolded_pages' ) ) {
			return;
		}

		foreach ( anna_get_scaffolded_pages() as $page ) {
			$code  = $page['code'] ?? '';
			$group = 'anna_content_' . $code . '_page';
			if ( ! $code || ! isset( $_POST[ $group ] ) || ! is_array( $_POST[ $group ] ) ) {
				continue;
			}

			$input = wp_unslash( $_POST[ $group ] );
			update_post_meta( $post_id, '_anna_content_' . $code . '_page', $this->sanitize_scaffolded_page_content( $input, $page ) );
		}
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $code    Page code.
	 * @return array<string, mixed>
	 */
	public function get_scaffold_page_content( $post_id, $code ) {
		$config = function_exists( 'anna_get_scaffolded_page' ) ? anna_get_scaffolded_page( $code ) : null;
		if ( ! $config ) {
			return array();
		}
		return $this->get_scaffold_page_content_with_defaults( $post_id, $config );
	}

	/**
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $config  Page config.
	 * @return array<string, mixed>
	 */
	private function get_scaffold_page_content_with_defaults( $post_id, $config ) {
		$code        = $config['code'] ?? '';
		$defaults_fn = 'anna_get_' . $code . '_default_content';
		$defaults    = function_exists( $defaults_fn ) ? $defaults_fn() : array();
		$stored      = get_post_meta( absint( $post_id ), '_anna_content_' . $code . '_page', true );
		$stored      = is_array( $stored ) ? $stored : array();
		$merged      = wp_parse_args( $stored, $defaults );

		foreach ( $defaults as $key => $default_value ) {
			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $default_value, $key ) ) {
					$merged[ $key ] = $default_value;
				}
			}
		}

		return $merged;
	}

	/**
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $data    Content.
	 * @param array<string, mixed> $config  Page config.
	 */
	private function maybe_backfill_scaffolded_page_meta( $post_id, $data, $config ) {
		$code    = $config['code'] ?? '';
		$post_id = absint( $post_id );
		if ( ! $code || ! $post_id || get_post_meta( $post_id, '_anna_scaffold_meta_backfilled_' . $code, true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_' . $code . '_page', true );
		$stored  = is_array( $stored ) ? $stored : array();
		$changed = false;

		foreach ( $data as $key => $value ) {
			if ( ! array_key_exists( $key, $stored ) || $this->is_blank_section_value( $stored[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $value, $key ) ) {
					$stored[ $key ] = $value;
					$changed        = true;
				}
			}
		}

		if ( $changed ) {
			update_post_meta( $post_id, '_anna_content_' . $code . '_page', $stored );
		}
		update_post_meta( $post_id, '_anna_scaffold_meta_backfilled_' . $code, 1 );
	}

	/**
	 * @param array<string, mixed> $input  Raw POST.
	 * @param array<string, mixed> $config Page config.
	 * @return array<string, mixed>
	 */
	private function sanitize_scaffolded_page_content( $input, $config ) {
		$code        = $config['code'] ?? '';
		$defaults_fn = 'anna_get_' . $code . '_default_content';
		$defaults    = function_exists( $defaults_fn ) ? $defaults_fn() : array();
		$out         = array();

		foreach ( $config['sections'] ?? array() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$type = $field['type'] ?? 'text';
				$raw  = $input[ $key ] ?? '';

				if ( 'media' === $type ) {
					$out[ $key ] = absint( $raw );
				} elseif ( 'url' === $type ) {
					$out[ $key ] = esc_url_raw( $raw );
				} elseif ( 'textarea' === $type ) {
					$out[ $key ] = sanitize_textarea_field( $raw );
				} else {
					$out[ $key ] = sanitize_text_field( $raw );
				}
			}
		}

		return wp_parse_args( $out, $defaults );
	}
}
