<?php
/**
 * Blog page content meta box, save, and defaults.
 *
 * Meta key: _anna_content_blog_page
 * Detected: page-blog.php template or slug 'blog'
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Blog_Page_Content {

	/**
	 * Register the Blog page meta box when the correct template/slug is active.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function register_blog_page_meta_box( $post ) {
		$is_blog = ( 'blog' === $post->post_name || 'page-blog.php' === get_page_template_slug( $post->ID ) );
		if ( ! $is_blog ) {
			return;
		}

		add_meta_box(
			'anna_content_blog_page',
			__( 'Anna Blog Page Content', 'anna-baylis' ),
			array( $this, 'render_blog_page_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Render the Blog page editor fields.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_blog_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_blog_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_blog_page_meta( $post->ID, $data );

		$prefix = 'anna_content_blog_page';
		?>
		<p><?php esc_html_e( 'These fields control the Blog page header and section labels. Category filter tabs are managed in WordPress → Categories.', 'anna-baylis' ); ?></p>
		<p class="description" style="padding:0.6rem 0.9rem;background:#f0f6fc;border-left:3px solid #72aee6;border-radius:2px;font-size:12px;">
			<?php esc_html_e( 'Tip: type', 'anna-baylis' ); ?> <code>empty--</code> <?php esc_html_e( 'in any field to hide it on the frontend.', 'anna-baylis' ); ?>
		</p>

		<h3><?php esc_html_e( 'Page Header', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'hero_description', __( 'Description', 'anna-baylis' ), $data['hero_description'], 3 ); ?>
		</table>

		<h3><?php esc_html_e( 'Articles Section', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'section_heading', __( 'Section Heading', 'anna-baylis' ), $data['section_heading'] ); ?>
			<?php $this->render_text_field( $prefix, 'section_subtext', __( 'Section Subtext', 'anna-baylis' ), $data['section_subtext'] ); ?>
		</table>
		<?php
	}

	/**
	 * Save Blog page meta from POST.
	 *
	 * @param int $post_id Post ID.
	 */
	private function save_blog_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_blog_page'] ) || ! is_array( $_POST['anna_content_blog_page'] ) ) {
			return;
		}

		$input = wp_unslash( $_POST['anna_content_blog_page'] );
		update_post_meta( $post_id, '_anna_content_blog_page', $this->sanitize_blog_page_content( $input ) );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_blog_page_content( $post_id ) {
		return $this->get_blog_page_content_with_defaults( $post_id );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_blog_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_blog_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_blog_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		// Scalar fields only — categories always come from defaults.
		$scalar_keys = array( 'hero_heading', 'hero_description', 'section_heading', 'section_subtext' );
		foreach ( $scalar_keys as $key ) {
			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( isset( $defaults[ $key ] ) && ! $this->is_blank_section_value( $defaults[ $key ], $key ) ) {
					$merged[ $key ] = $defaults[ $key ];
				}
			}
		}

		// Categories always use the coded defaults.
		$merged['categories']    = $defaults['categories'];
		$merged['posts_per_page'] = $defaults['posts_per_page'];

		return $merged;
	}

	/**
	 * @param int   $post_id Post ID.
	 * @param array $data    Resolved content.
	 */
	private function maybe_backfill_blog_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || get_post_meta( $post_id, '_anna_blog_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_blog_page', true );
		$stored  = is_array( $stored ) ? $stored : array();
		$changed = false;

		$scalar_keys = array( 'hero_heading', 'hero_description', 'section_heading', 'section_subtext' );
		foreach ( $scalar_keys as $key ) {
			if ( ! array_key_exists( $key, $stored ) || $this->is_blank_section_value( $stored[ $key ], $key ) ) {
				if ( isset( $data[ $key ] ) && ! $this->is_blank_section_value( $data[ $key ], $key ) ) {
					$stored[ $key ] = $data[ $key ];
					$changed        = true;
				}
			}
		}

		if ( $changed ) {
			update_post_meta( $post_id, '_anna_content_blog_page', $stored );
		}
		update_post_meta( $post_id, '_anna_blog_meta_backfilled_v1', 1 );
	}

	/**
	 * @param array $input Raw POST array.
	 * @return array
	 */
	private function sanitize_blog_page_content( $input ) {
		return array(
			'hero_heading'     => sanitize_text_field( $input['hero_heading'] ?? '' ),
			'hero_description' => sanitize_textarea_field( $input['hero_description'] ?? '' ),
			'section_heading'  => sanitize_text_field( $input['section_heading'] ?? '' ),
			'section_subtext'  => sanitize_text_field( $input['section_subtext'] ?? '' ),
		);
	}

	/**
	 * @return array
	 */
	private function get_blog_page_defaults() {
		return function_exists( 'anna_get_blog_default_content' )
			? anna_get_blog_default_content()
			: array();
	}
}
