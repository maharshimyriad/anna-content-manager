<?php
/**
 * Flexible page templates: section layout + per-page content (scaffolded pages).
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Scaffolded_Page_Content {

	/**
	 * @param WP_Post $post Post object.
	 */
	private function register_scaffolded_page_meta_boxes( $post ) {
		if ( ! function_exists( 'anna_get_flexible_page_config' ) ) {
			return;
		}

		$config = anna_get_flexible_page_config( $post->ID );
		if ( ! $config ) {
			return;
		}

		$title = $config['title'] ?? get_the_title( $post->ID );

		add_meta_box(
			'anna_content_flexible_page_' . ( $config['code'] ?? 'page' ),
			sprintf(
				/* translators: %s: page title */
				__( 'Anna %s Page', 'anna-baylis' ),
				$title
			),
			array( $this, 'render_scaffolded_page_meta_box' ),
			'page',
			'normal',
			'high',
			array( 'page_config' => $config )
		);
	}

	/**
	 * @param WP_Post $post Post object.
	 * @param array   $box  Meta box args.
	 */
	public function render_scaffolded_page_meta_box( $post, $box ) {
		$config = $box['args']['page_config'] ?? array();
		$code   = $config['code'] ?? '';
		if ( ! $code ) {
			return;
		}

		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_scaffold_page_content_with_defaults( $post->ID, $config );
		$this->maybe_backfill_scaffolded_page_meta( $post->ID, $data, $config );

		$group = 'anna_content_' . $code . '_page';

		$this->render_page_section_layout_builder( $post->ID );

		echo '<p>' . esc_html__( 'Edit section copy and images below. Theme defaults apply when fields are empty.', 'anna-baylis' ) . '</p>';

		$sections = function_exists( 'anna_get_page_sections_for_post' )
			? anna_get_page_sections_for_post( $post->ID )
			: ( $config['sections'] ?? array() );

		foreach ( $sections as $section ) {
			echo '<h3>' . esc_html( (string) ( $section['label'] ?? '' ) ) . '</h3>';
			echo '<table class="form-table">';
			foreach ( $section['fields'] as $key => $field ) {
				$this->render_scaffolded_field_row( $group, $key, $field, $data[ $key ] ?? '' );
			}
			echo '</table>';
		}
	}

	/**
	 * Visual section order / add UI.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_page_section_layout_builder( $post_id ) {
		if ( ! function_exists( 'anna_get_page_section_layout' ) || ! function_exists( 'anna_get_page_section_types' ) ) {
			return;
		}

		$layout = anna_get_page_section_layout( $post_id );
		$types  = anna_get_page_section_types();
		?>
		<h3><?php esc_html_e( 'Page sections (layout)', 'anna-baylis' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Add, remove, and reorder sections. Save the page to apply layout changes on the front end.', 'anna-baylis' ); ?></p>

		<div class="anna-content-repeater anna-section-layout" data-anna-content-repeater="section-layout">
			<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
				<?php foreach ( $layout as $index => $row ) : ?>
					<?php $this->render_section_layout_row( $index, $row, $types ); ?>
				<?php endforeach; ?>
			</div>
			<p>
				<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Section', 'anna-baylis' ); ?></button>
			</p>
			<template data-anna-content-repeater-template="true">
				<?php $this->render_section_layout_row( '__INDEX__', array( 'type' => 'text-image', 'id' => '' ), $types ); ?>
			</template>
		</div>
		<hr>
		<?php
	}

	/**
	 * @param int|string              $index Row index.
	 * @param array<string, string>   $row   Layout row.
	 * @param array<string, mixed>    $types Section types.
	 */
	private function render_section_layout_row( $index, $row, $types ) {
		$type = $row['type'] ?? 'text-image';
		$id   = $row['id'] ?? '';
		?>
		<div class="anna-content-repeater__row anna-section-layout__row" data-anna-content-repeater-row="true" style="padding:12px 0;border-bottom:1px solid #dcdcde;">
			<p>
				<label><?php esc_html_e( 'Section type', 'anna-baylis' ); ?></label><br>
				<select name="anna_page_section_layout[<?php echo esc_attr( $index ); ?>][type]" class="anna-section-layout__type">
					<?php foreach ( $types as $type_key => $def ) : ?>
						<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $type, $type_key ); ?>><?php echo esc_html( $def['label'] ?? $type_key ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="anna-section-layout__id-wrap">
				<label><?php esc_html_e( 'Block ID (text+image only)', 'anna-baylis' ); ?></label><br>
				<input type="text" class="regular-text anna-section-layout__id" name="anna_page_section_layout[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $id ); ?>" placeholder="block1">
			</p>
			<p>
				<button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove section', 'anna-baylis' ); ?></button>
			</p>
		</div>
		<?php
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
	 * @param string               $group   Field group.
	 * @param string               $key     Field key.
	 * @param string               $label   Label.
	 * @param string               $value   Value.
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
		if ( ! function_exists( 'anna_get_flexible_page_config' ) ) {
			return;
		}

		$config = anna_get_flexible_page_config( $post_id );
		if ( ! $config ) {
			return;
		}

		$code = $config['code'] ?? '';

		if ( isset( $_POST['anna_page_section_layout'] ) && function_exists( 'anna_normalize_page_section_layout' ) ) {
			$layout = anna_normalize_page_section_layout( wp_unslash( $_POST['anna_page_section_layout'] ) );
			update_post_meta( $post_id, '_anna_page_section_layout', $layout );
		}

		$group = 'anna_content_' . $code . '_page';
		if ( isset( $_POST[ $group ] ) && is_array( $_POST[ $group ] ) ) {
			$input = wp_unslash( $_POST[ $group ] );
			update_post_meta( $post_id, '_anna_content_' . $code . '_page', $this->sanitize_scaffolded_page_content( $input, $post_id, $config ) );
		}
	}

	/**
	 * @param int    $post_id Post ID.
	 * @param string $code    Page code.
	 * @return array<string, mixed>
	 */
	public function get_scaffold_page_content( $post_id, $code ) {
		$config = function_exists( 'anna_get_scaffolded_page' ) ? anna_get_scaffolded_page( $code ) : null;
		if ( ! $config && function_exists( 'anna_get_flexible_page_config' ) ) {
			$config = anna_get_flexible_page_config( $post_id );
		}
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
	 * @param array<string, mixed> $input   Raw POST.
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $config  Page config (fallback).
	 * @return array<string, mixed>
	 */
	private function sanitize_scaffolded_page_content( $input, $post_id, $config ) {
		$code        = $config['code'] ?? '';
		$defaults_fn = 'anna_get_' . $code . '_default_content';
		$defaults    = function_exists( $defaults_fn ) ? $defaults_fn() : array();
		$out         = array();

		$sections = function_exists( 'anna_get_page_sections_for_post' )
			? anna_get_page_sections_for_post( $post_id )
			: ( $config['sections'] ?? array() );

		foreach ( $sections as $section ) {
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
