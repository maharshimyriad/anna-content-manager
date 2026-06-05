<?php
/**
 * Contact page content meta box, save, and defaults.
 *
 * Meta key: _anna_content_contact_page
 * Detected: page-contact.php template or slug 'contact'
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Contact_Page_Content {

	/**
	 * Register the Contact page meta box when the correct template/slug is active.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function register_contact_page_meta_box( $post ) {
		$is_contact = ( 'contact' === $post->post_name || 'page-contact.php' === get_page_template_slug( $post->ID ) );
		if ( ! $is_contact ) {
			return;
		}

		add_meta_box(
			'anna_content_contact_page',
			__( 'Anna Contact Page Content', 'anna-baylis' ),
			array( $this, 'render_contact_page_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Render the Contact page editor fields.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_contact_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_contact_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_contact_page_meta( $post->ID, $data );

		$prefix = 'anna_content_contact_page';
		?>
		<p><?php esc_html_e( 'These fields control the Contact page. Edit copy, the hero image, and form settings. Contact details (email, phone, address, hours) come from Anna Theme → Footer.', 'anna-baylis' ); ?></p>
		<p class="description" style="padding:0.6rem 0.9rem;background:#f0f6fc;border-left:3px solid #72aee6;border-radius:2px;font-size:12px;">
			<?php esc_html_e( 'Tip: type', 'anna-baylis' ); ?> <code>empty--</code> <?php esc_html_e( 'in any field to hide it on the frontend.', 'anna-baylis' ); ?>
		</p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'hero_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['hero_eyebrow'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'], 2, __( 'Use a new line where the heading should break.', 'anna-baylis' ) ); ?>
			<?php $this->render_media_field( $prefix, 'hero_image_id', __( 'Background Image', 'anna-baylis' ), $data['hero_image_id'] ); ?>
		</table>

		<h3><?php esc_html_e( 'Contact Info Panel', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'info_heading', __( 'Section Heading', 'anna-baylis' ), $data['info_heading'] ); ?>
		</table>

		<h3><?php esc_html_e( 'CTA Card', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'cta_card_heading', __( 'Heading', 'anna-baylis' ), $data['cta_card_heading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'cta_card_body', __( 'Body', 'anna-baylis' ), $data['cta_card_body'], 3 ); ?>
			<?php $this->render_text_field( $prefix, 'cta_card_button_text', __( 'Button Text', 'anna-baylis' ), $data['cta_card_button_text'] ); ?>
			<?php $this->render_text_field( $prefix, 'cta_card_button_url', __( 'Button URL', 'anna-baylis' ), $data['cta_card_button_url'] ); ?>
		</table>

		<h3><?php esc_html_e( 'Contact Form', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'form_heading', __( 'Heading', 'anna-baylis' ), $data['form_heading'] ); ?>
			<?php $this->render_text_field( $prefix, 'form_button_text', __( 'Submit Button Text', 'anna-baylis' ), $data['form_button_text'] ); ?>
			<?php $this->render_text_field( $prefix, 'form_response_note', __( 'Response Time Note', 'anna-baylis' ), $data['form_response_note'] ); ?>
			<?php $this->render_text_field( $prefix, 'form_action_url', __( 'Form Action URL', 'anna-baylis' ), $data['form_action_url'] ); ?>
			<tr>
				<td colspan="2">
					<p class="description"><?php esc_html_e( 'Leave Form Action URL blank to use the built-in email handler. Set it to a third-party URL (e.g. Formspree) to redirect submissions there instead.', 'anna-baylis' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save Contact page meta from POST.
	 *
	 * @param int $post_id Post ID.
	 */
	private function save_contact_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_contact_page'] ) || ! is_array( $_POST['anna_content_contact_page'] ) ) {
			return;
		}

		$input = wp_unslash( $_POST['anna_content_contact_page'] );
		update_post_meta( $post_id, '_anna_content_contact_page', $this->sanitize_contact_page_content( $input ) );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_contact_page_content( $post_id ) {
		return $this->get_contact_page_content_with_defaults( $post_id );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_contact_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_contact_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_contact_page_defaults();
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
	 * One-time backfill of defaults into saved meta so fields show values on first open.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Resolved content.
	 */
	private function maybe_backfill_contact_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || get_post_meta( $post_id, '_anna_contact_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_contact_page', true );
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
			update_post_meta( $post_id, '_anna_content_contact_page', $stored );
		}
		update_post_meta( $post_id, '_anna_contact_meta_backfilled_v1', 1 );
	}

	/**
	 * Sanitize Contact page POST input.
	 *
	 * @param array $input Raw POST array.
	 * @return array
	 */
	private function sanitize_contact_page_content( $input ) {
		return array(
			'hero_eyebrow'         => sanitize_text_field( $input['hero_eyebrow'] ?? '' ),
			'hero_heading'         => sanitize_textarea_field( $input['hero_heading'] ?? '' ),
			'hero_image_id'        => absint( $input['hero_image_id'] ?? 0 ),
			'info_heading'         => sanitize_text_field( $input['info_heading'] ?? '' ),
			'cta_card_heading'     => sanitize_text_field( $input['cta_card_heading'] ?? '' ),
			'cta_card_body'        => sanitize_textarea_field( $input['cta_card_body'] ?? '' ),
			'cta_card_button_text' => sanitize_text_field( $input['cta_card_button_text'] ?? '' ),
			'cta_card_button_url'  => esc_url_raw( $input['cta_card_button_url'] ?? '' ),
			'form_heading'         => sanitize_text_field( $input['form_heading'] ?? '' ),
			'form_button_text'     => sanitize_text_field( $input['form_button_text'] ?? '' ),
			'form_response_note'   => sanitize_text_field( $input['form_response_note'] ?? '' ),
			'form_action_url'      => esc_url_raw( $input['form_action_url'] ?? '' ),
		);
	}

	/**
	 * @return array
	 */
	private function get_contact_page_defaults() {
		return function_exists( 'anna_get_contact_default_content' )
			? anna_get_contact_default_content()
			: array();
	}
}
