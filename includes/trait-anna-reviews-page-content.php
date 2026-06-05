<?php
/**
 * Reviews page content meta box, save, and defaults.
 *
 * Meta key: _anna_content_reviews_page
 * Detected: page-reviews.php template or slug 'reviews'
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Reviews_Page_Content {

	/**
	 * Register the Reviews page meta box when the correct template/slug is active.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function register_reviews_page_meta_box( $post ) {
		$is_reviews = ( 'reviews' === $post->post_name || 'page-reviews.php' === get_page_template_slug( $post->ID ) );
		if ( ! $is_reviews ) {
			return;
		}

		add_meta_box(
			'anna_content_reviews_page',
			__( 'Anna Reviews Page Content', 'anna-baylis' ),
			array( $this, 'render_reviews_page_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Render the Reviews page editor fields.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_reviews_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_reviews_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_reviews_page_meta( $post->ID, $data );

		$prefix = 'anna_content_reviews_page';
		?>
		<p><?php esc_html_e( 'These fields control the Reviews page. Individual review cards are managed under Reviews in the main WordPress menu.', 'anna-baylis' ); ?></p>
		<p class="description" style="padding:0.6rem 0.9rem;background:#f0f6fc;border-left:3px solid #72aee6;border-radius:2px;font-size:12px;">
			<?php esc_html_e( 'Tip: type', 'anna-baylis' ); ?> <code>empty--</code> <?php esc_html_e( 'in any field to hide it on the frontend.', 'anna-baylis' ); ?>
		</p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'hero_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['hero_eyebrow'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'], 2, __( 'Use a new line where the heading should break.', 'anna-baylis' ) ); ?>
			<?php $this->render_media_field( $prefix, 'hero_image_id', __( 'Background Image', 'anna-baylis' ), $data['hero_image_id'] ); ?>
			<?php $this->render_text_field( $prefix, 'hero_rating_text', __( 'Rating Summary Text', 'anna-baylis' ), $data['hero_rating_text'] ); ?>
		</table>

		<h3><?php esc_html_e( 'Reviews Grid', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'google_reviews_url', __( 'Google Reviews URL', 'anna-baylis' ), $data['google_reviews_url'] ); ?>
			<?php $this->render_text_field( $prefix, 'google_reviews_text', __( 'Google Reviews Link Text', 'anna-baylis' ), $data['google_reviews_text'] ); ?>
		</table>

		<h3><?php esc_html_e( 'CTA Section', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'cta_heading', __( 'Heading', 'anna-baylis' ), $data['cta_heading'] ); ?>
			<?php $this->render_text_field( $prefix, 'cta_body', __( 'Body Text', 'anna-baylis' ), $data['cta_body'] ); ?>
			<?php $this->render_text_field( $prefix, 'cta_button_text', __( 'Button Text', 'anna-baylis' ), $data['cta_button_text'] ); ?>
			<?php $this->render_text_field( $prefix, 'cta_button_url', __( 'Button URL', 'anna-baylis' ), $data['cta_button_url'] ); ?>
		</table>
		<?php
	}

	/**
	 * Save Reviews page meta from POST.
	 *
	 * @param int $post_id Post ID.
	 */
	private function save_reviews_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_reviews_page'] ) || ! is_array( $_POST['anna_content_reviews_page'] ) ) {
			return;
		}

		$input = wp_unslash( $_POST['anna_content_reviews_page'] );
		update_post_meta( $post_id, '_anna_content_reviews_page', $this->sanitize_reviews_page_content( $input ) );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_reviews_page_content( $post_id ) {
		return $this->get_reviews_page_content_with_defaults( $post_id );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_reviews_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_reviews_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_reviews_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		foreach ( $defaults as $key => $default_value ) {
			if ( 'hero_image_id' === $key ) {
				continue;
			}
			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $default_value, $key ) ) {
					$merged[ $key ] = $default_value;
				}
			}
		}

		return $merged;
	}

	/**
	 * @param int   $post_id Post ID.
	 * @param array $data    Resolved content.
	 */
	private function maybe_backfill_reviews_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || get_post_meta( $post_id, '_anna_reviews_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_reviews_page', true );
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
			update_post_meta( $post_id, '_anna_content_reviews_page', $stored );
		}
		update_post_meta( $post_id, '_anna_reviews_meta_backfilled_v1', 1 );
	}

	/**
	 * @param array $input Raw POST array.
	 * @return array
	 */
	private function sanitize_reviews_page_content( $input ) {
		return array(
			'hero_eyebrow'        => sanitize_text_field( $input['hero_eyebrow'] ?? '' ),
			'hero_heading'        => sanitize_textarea_field( $input['hero_heading'] ?? '' ),
			'hero_image_id'       => absint( $input['hero_image_id'] ?? 0 ),
			'hero_rating_text'    => sanitize_text_field( $input['hero_rating_text'] ?? '' ),
			'google_reviews_url'  => esc_url_raw( $input['google_reviews_url'] ?? '' ),
			'google_reviews_text' => sanitize_text_field( $input['google_reviews_text'] ?? '' ),
			'cta_heading'         => sanitize_text_field( $input['cta_heading'] ?? '' ),
			'cta_body'            => sanitize_text_field( $input['cta_body'] ?? '' ),
			'cta_button_text'     => sanitize_text_field( $input['cta_button_text'] ?? '' ),
			'cta_button_url'      => esc_url_raw( $input['cta_button_url'] ?? '' ),
		);
	}

	/**
	 * @return array
	 */
	private function get_reviews_page_defaults() {
		return function_exists( 'anna_get_reviews_default_content' )
			? anna_get_reviews_default_content()
			: array();
	}
}
