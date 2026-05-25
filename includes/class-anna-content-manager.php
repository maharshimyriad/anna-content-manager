<?php
/**
 * Anna Content Manager plugin bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Anna_Content_Manager {
	/**
	 * Singleton instance.
	 *
	 * @var Anna_Content_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Anna_Content_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Activate plugin and seed hero data from legacy theme options.
	 */
	public static function activate() {
		$front_page_id = (int) get_option( 'page_on_front' );
		if ( ! $front_page_id ) {
			return;
		}

		$existing = get_post_meta( $front_page_id, '_anna_content_hero', true );
		if ( is_array( $existing ) && ! empty( array_filter( $existing ) ) ) {
			return;
		}

		$theme_options = get_option( 'anna_theme_options', array() );
		if ( ! is_array( $theme_options ) ) {
			$theme_options = array();
		}

		$hero_data = array(
			'eyebrow'            => $theme_options['hero_eyebrow'] ?? '',
			'heading'            => isset( $theme_options['hero_heading'] ) ? wp_strip_all_tags( str_replace( '<br>', "\n", (string) $theme_options['hero_heading'] ) ) : '',
			'description'        => $theme_options['hero_description'] ?? '',
			'trust_text'         => $theme_options['hero_trust_text'] ?? '',
			'image_id'           => absint( $theme_options['hero_image_id'] ?? 0 ),
			'primary_button_text'=> $theme_options['cta_primary_text'] ?? '',
			'primary_button_url' => $theme_options['cta_primary_url'] ?? '',
			'secondary_button_text' => $theme_options['cta_secondary_text'] ?? '',
			'secondary_button_url'  => $theme_options['cta_secondary_url'] ?? '',
			'stat_1_value'       => $theme_options['stat_1_value'] ?? '',
			'stat_1_label'       => $theme_options['stat_1_label'] ?? '',
			'stat_2_value'       => $theme_options['stat_2_value'] ?? '',
			'stat_2_label'       => $theme_options['stat_2_label'] ?? '',
			'stat_3_value'       => $theme_options['stat_3_value'] ?? '',
			'stat_3_label'       => $theme_options['stat_3_label'] ?? '',
		);

		update_post_meta( $front_page_id, '_anna_content_hero', $hero_data );
		update_post_meta(
			$front_page_id,
			'_anna_content_intro',
			array(
				'intro_eyebrow'           => $theme_options['intro_eyebrow'] ?? '',
				'intro_heading'           => isset( $theme_options['intro_heading'] ) ? wp_strip_all_tags( (string) $theme_options['intro_heading'] ) : '',
				'intro_body'              => $theme_options['intro_body'] ?? '',
				'intro_quote'             => $theme_options['intro_quote'] ?? '',
				'intro_quote_cite'        => $theme_options['intro_quote_cite'] ?? '',
				'recognition_eyebrow'     => $theme_options['recognition_eyebrow'] ?? '',
				'recognition_heading'     => $theme_options['recognition_heading'] ?? '',
				'recognition_description' => $theme_options['recognition_description'] ?? '',
				'recognition_items_text'  => $theme_options['recognition_items_text'] ?? '',
			)
		);
		update_post_meta(
			$front_page_id,
			'_anna_content_services',
			array(
				'eyebrow'     => $theme_options['services_eyebrow'] ?? '',
				'heading'     => $theme_options['services_heading'] ?? '',
				'description' => $theme_options['services_description'] ?? '',
				'cta_text'    => $theme_options['services_cta_text'] ?? '',
				'cta_url'     => $theme_options['services_cta_url'] ?? '',
			)
		);
		update_post_meta(
			$front_page_id,
			'_anna_content_about',
			array(
				'eyebrow'        => $theme_options['about_eyebrow'] ?? '',
				'heading'        => isset( $theme_options['about_heading'] ) ? wp_strip_all_tags( (string) $theme_options['about_heading'] ) : '',
				'body'           => $theme_options['about_body'] ?? '',
				'quote'          => $theme_options['about_quote'] ?? '',
				'image_id'       => absint( $theme_options['about_image_id'] ?? 0 ),
				'badge_number'   => $theme_options['about_badge_number'] ?? '',
				'badge_text'     => $theme_options['about_badge_text'] ?? '',
				'expertise_text' => $theme_options['about_expertise_text'] ?? '',
				'cta_text'       => $theme_options['about_cta_text'] ?? '',
				'cta_url'        => $theme_options['about_cta_url'] ?? '',
			)
		);
		update_post_meta(
			$front_page_id,
			'_anna_content_testimonials',
			array(
				'eyebrow'  => $theme_options['testimonials_eyebrow'] ?? '',
				'heading'  => $theme_options['testimonials_heading'] ?? '',
				'summary'  => $theme_options['testimonials_summary'] ?? '',
				'cta_text' => $theme_options['testimonials_cta_text'] ?? '',
				'cta_url'  => $theme_options['testimonials_cta_url'] ?? '',
			)
		);
		update_post_meta(
			$front_page_id,
			'_anna_content_cta',
			array(
				'eyebrow'               => $theme_options['cta_eyebrow'] ?? '',
				'heading'               => isset( $theme_options['cta_heading'] ) ? wp_strip_all_tags( (string) $theme_options['cta_heading'] ) : '',
				'description'           => $theme_options['cta_description'] ?? '',
				'trust_text'            => $theme_options['cta_trust'] ?? '',
				'primary_button_text'   => $theme_options['cta_primary_text'] ?? '',
				'primary_button_url'    => $theme_options['cta_primary_url'] ?? '',
				'secondary_button_text' => $theme_options['cta_secondary_text'] ?? '',
				'secondary_button_url'  => $theme_options['cta_secondary_url'] ?? '',
			)
		);
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes_page', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_page', array( $this, 'save_page_content' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register page-level content meta boxes.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'anna_content_hero',
			__( 'Anna Hero Section Content', 'anna-baylis' ),
			array( $this, 'render_hero_meta_box' ),
			'page',
			'normal',
			'high'
		);
		add_meta_box( 'anna_content_intro', __( 'Anna Intro / Recognition Content', 'anna-baylis' ), array( $this, 'render_intro_meta_box' ), 'page', 'normal', 'default' );
		add_meta_box( 'anna_content_services', __( 'Anna Services Section Content', 'anna-baylis' ), array( $this, 'render_services_meta_box' ), 'page', 'normal', 'default' );
		add_meta_box( 'anna_content_about', __( 'Anna About Section Content', 'anna-baylis' ), array( $this, 'render_about_meta_box' ), 'page', 'normal', 'default' );
		add_meta_box( 'anna_content_testimonials', __( 'Anna Testimonials Section Content', 'anna-baylis' ), array( $this, 'render_testimonials_meta_box' ), 'page', 'normal', 'default' );
		add_meta_box( 'anna_content_cta', __( 'Anna Final CTA Section Content', 'anna-baylis' ), array( $this, 'render_cta_meta_box' ), 'page', 'normal', 'default' );
	}

	/**
	 * Enqueue admin assets for media selection.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'page' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'anna-content-manager-admin',
			ANNA_CONTENT_MANAGER_URL . 'assets/js/admin-page-content.js',
			array( 'jquery' ),
			ANNA_CONTENT_MANAGER_VERSION,
			true
		);
	}

	/**
	 * Render hero meta box fields.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_hero_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_section_with_legacy_defaults( $post->ID, 'hero' );

		$image_url = ! empty( $data['image_id'] ) ? wp_get_attachment_image_url( absint( $data['image_id'] ), 'medium' ) : '';
		?>
		<p><?php esc_html_e( 'Use this panel to manage hero content for this page without touching the theme layout. The homepage hero reads these values first.', 'anna-baylis' ); ?></p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="anna-content-hero-eyebrow"><?php esc_html_e( 'Eyebrow', 'anna-baylis' ); ?></label></th>
				<td><input type="text" id="anna-content-hero-eyebrow" name="anna_content_hero[eyebrow]" value="<?php echo esc_attr( $data['eyebrow'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="anna-content-hero-heading"><?php esc_html_e( 'Heading', 'anna-baylis' ); ?></label></th>
				<td>
					<textarea id="anna-content-hero-heading" name="anna_content_hero[heading]" rows="3" class="large-text"><?php echo esc_textarea( $data['heading'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use new lines where the design should break the headline.', 'anna-baylis' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="anna-content-hero-description"><?php esc_html_e( 'Description', 'anna-baylis' ); ?></label></th>
				<td><textarea id="anna-content-hero-description" name="anna_content_hero[description]" rows="4" class="large-text"><?php echo esc_textarea( $data['description'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="anna-content-hero-trust"><?php esc_html_e( 'Trust Text', 'anna-baylis' ); ?></label></th>
				<td><input type="text" id="anna-content-hero-trust" name="anna_content_hero[trust_text]" value="<?php echo esc_attr( $data['trust_text'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Background Image', 'anna-baylis' ); ?></th>
				<td>
					<input type="hidden" id="anna-content-hero-image-id" name="anna_content_hero[image_id]" value="<?php echo esc_attr( $data['image_id'] ); ?>">
					<div id="anna-content-hero-image-preview" style="margin-bottom:10px;">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:240px;height:auto;border-radius:10px;">
						<?php endif; ?>
					</div>
					<button type="button" class="button anna-content-media-select" data-target="anna-content-hero-image-id" data-preview="anna-content-hero-image-preview"><?php esc_html_e( 'Select Image', 'anna-baylis' ); ?></button>
					<button type="button" class="button anna-content-media-remove" data-target="anna-content-hero-image-id" data-preview="anna-content-hero-image-preview"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button>
				</td>
			</tr>
		</table>

		<h4><?php esc_html_e( 'Primary Button', 'anna-baylis' ); ?></h4>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="anna-content-hero-primary-text"><?php esc_html_e( 'Text', 'anna-baylis' ); ?></label></th>
				<td><input type="text" id="anna-content-hero-primary-text" name="anna_content_hero[primary_button_text]" value="<?php echo esc_attr( $data['primary_button_text'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="anna-content-hero-primary-url"><?php esc_html_e( 'URL', 'anna-baylis' ); ?></label></th>
				<td><input type="url" id="anna-content-hero-primary-url" name="anna_content_hero[primary_button_url]" value="<?php echo esc_attr( $data['primary_button_url'] ); ?>" class="regular-text"></td>
			</tr>
		</table>

		<h4><?php esc_html_e( 'Secondary Button', 'anna-baylis' ); ?></h4>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="anna-content-hero-secondary-text"><?php esc_html_e( 'Text', 'anna-baylis' ); ?></label></th>
				<td><input type="text" id="anna-content-hero-secondary-text" name="anna_content_hero[secondary_button_text]" value="<?php echo esc_attr( $data['secondary_button_text'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="anna-content-hero-secondary-url"><?php esc_html_e( 'URL', 'anna-baylis' ); ?></label></th>
				<td><input type="url" id="anna-content-hero-secondary-url" name="anna_content_hero[secondary_button_url]" value="<?php echo esc_attr( $data['secondary_button_url'] ); ?>" class="regular-text"></td>
			</tr>
		</table>

		<h4><?php esc_html_e( 'Stats', 'anna-baylis' ); ?></h4>
		<table class="form-table">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<tr>
					<th scope="row"><?php echo esc_html( sprintf( __( 'Stat %d', 'anna-baylis' ), $i ) ); ?></th>
					<td>
						<input type="text" name="anna_content_hero[stat_<?php echo esc_attr( $i ); ?>_value]" value="<?php echo esc_attr( $data[ 'stat_' . $i . '_value' ] ); ?>" class="small-text" placeholder="<?php esc_attr_e( 'Value', 'anna-baylis' ); ?>">
						<input type="text" name="anna_content_hero[stat_<?php echo esc_attr( $i ); ?>_label]" value="<?php echo esc_attr( $data[ 'stat_' . $i . '_label' ] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Label', 'anna-baylis' ); ?>">
					</td>
				</tr>
			<?php endfor; ?>
		</table>
		<?php
	}

	/**
	 * Render intro/recognition meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_intro_meta_box( $post ) {
		$data = $this->get_section_with_legacy_defaults( $post->ID, 'intro' );
		?>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-intro-eyebrow"><?php esc_html_e( 'Intro Eyebrow', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-intro-eyebrow" name="anna_content_intro[intro_eyebrow]" value="<?php echo esc_attr( $data['intro_eyebrow'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-intro-heading"><?php esc_html_e( 'Intro Heading', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-intro-heading" name="anna_content_intro[intro_heading]" rows="3" class="large-text"><?php echo esc_textarea( $data['intro_heading'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-intro-body"><?php esc_html_e( 'Intro Body', 'anna-baylis' ); ?></label></th><td><?php wp_editor( $data['intro_body'], 'anna_content_intro_body_editor', array( 'textarea_name' => 'anna_content_intro[intro_body]', 'textarea_rows' => 8, 'media_buttons' => false ) ); ?></td></tr>
			<tr><th scope="row"><label for="anna-content-intro-quote"><?php esc_html_e( 'Intro Quote', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-intro-quote" name="anna_content_intro[intro_quote]" rows="2" class="large-text"><?php echo esc_textarea( $data['intro_quote'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-intro-quote-cite"><?php esc_html_e( 'Intro Quote Citation', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-intro-quote-cite" name="anna_content_intro[intro_quote_cite]" value="<?php echo esc_attr( $data['intro_quote_cite'] ); ?>" class="regular-text"></td></tr>
		</table>
		<h4><?php esc_html_e( 'Recognition Card', 'anna-baylis' ); ?></h4>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-recognition-eyebrow"><?php esc_html_e( 'Eyebrow', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-recognition-eyebrow" name="anna_content_intro[recognition_eyebrow]" value="<?php echo esc_attr( $data['recognition_eyebrow'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-recognition-heading"><?php esc_html_e( 'Heading', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-recognition-heading" name="anna_content_intro[recognition_heading]" value="<?php echo esc_attr( $data['recognition_heading'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-recognition-description"><?php esc_html_e( 'Description', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-recognition-description" name="anna_content_intro[recognition_description]" rows="3" class="large-text"><?php echo esc_textarea( $data['recognition_description'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-recognition-items"><?php esc_html_e( 'Items', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-recognition-items" name="anna_content_intro[recognition_items_text]" rows="8" class="large-text"><?php echo esc_textarea( $data['recognition_items_text'] ); ?></textarea><p class="description"><?php esc_html_e( 'One item per line.', 'anna-baylis' ); ?></p></td></tr>
		</table>
		<?php
	}

	/**
	 * Render services meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_services_meta_box( $post ) {
		$data = $this->get_section_with_legacy_defaults( $post->ID, 'services' );
		?>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-services-eyebrow"><?php esc_html_e( 'Eyebrow', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-services-eyebrow" name="anna_content_services[eyebrow]" value="<?php echo esc_attr( $data['eyebrow'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-services-heading"><?php esc_html_e( 'Heading', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-services-heading" name="anna_content_services[heading]" value="<?php echo esc_attr( $data['heading'] ); ?>" class="large-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-services-description"><?php esc_html_e( 'Description', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-services-description" name="anna_content_services[description]" rows="3" class="large-text"><?php echo esc_textarea( $data['description'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-services-cta-text"><?php esc_html_e( 'CTA Text', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-services-cta-text" name="anna_content_services[cta_text]" value="<?php echo esc_attr( $data['cta_text'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-services-cta-url"><?php esc_html_e( 'CTA URL', 'anna-baylis' ); ?></label></th><td><input type="url" id="anna-content-services-cta-url" name="anna_content_services[cta_url]" value="<?php echo esc_attr( $data['cta_url'] ); ?>" class="regular-text"></td></tr>
		</table>
		<p class="description"><?php esc_html_e( 'Service cards still come from the Services content type. This box controls the section copy around them.', 'anna-baylis' ); ?></p>
		<?php
	}

	/**
	 * Render about meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_about_meta_box( $post ) {
		$data = $this->get_section_with_legacy_defaults( $post->ID, 'about' );
		$image_url = ! empty( $data['image_id'] ) ? wp_get_attachment_image_url( absint( $data['image_id'] ), 'medium' ) : '';
		?>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-about-eyebrow"><?php esc_html_e( 'Eyebrow', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-about-eyebrow" name="anna_content_about[eyebrow]" value="<?php echo esc_attr( $data['eyebrow'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-about-heading"><?php esc_html_e( 'Heading', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-about-heading" name="anna_content_about[heading]" rows="3" class="large-text"><?php echo esc_textarea( $data['heading'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-about-body"><?php esc_html_e( 'Body', 'anna-baylis' ); ?></label></th><td><?php wp_editor( $data['body'], 'anna_content_about_body_editor', array( 'textarea_name' => 'anna_content_about[body]', 'textarea_rows' => 8, 'media_buttons' => false ) ); ?></td></tr>
			<tr><th scope="row"><label for="anna-content-about-quote"><?php esc_html_e( 'Quote', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-about-quote" name="anna_content_about[quote]" rows="2" class="large-text"><?php echo esc_textarea( $data['quote'] ); ?></textarea></td></tr>
			<tr><th scope="row"><?php esc_html_e( 'Image', 'anna-baylis' ); ?></th><td><input type="hidden" id="anna-content-about-image-id" name="anna_content_about[image_id]" value="<?php echo esc_attr( $data['image_id'] ); ?>"><div id="anna-content-about-image-preview" style="margin-bottom:10px;"><?php if ( $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:240px;height:auto;border-radius:10px;"><?php endif; ?></div><button type="button" class="button anna-content-media-select" data-target="anna-content-about-image-id" data-preview="anna-content-about-image-preview"><?php esc_html_e( 'Select Image', 'anna-baylis' ); ?></button> <button type="button" class="button anna-content-media-remove" data-target="anna-content-about-image-id" data-preview="anna-content-about-image-preview"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></td></tr>
			<tr><th scope="row"><label for="anna-content-about-badge-number"><?php esc_html_e( 'Badge Number', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-about-badge-number" name="anna_content_about[badge_number]" value="<?php echo esc_attr( $data['badge_number'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-about-badge-text"><?php esc_html_e( 'Badge Text', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-about-badge-text" name="anna_content_about[badge_text]" value="<?php echo esc_attr( $data['badge_text'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-about-expertise"><?php esc_html_e( 'Expertise Tags', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-about-expertise" name="anna_content_about[expertise_text]" rows="8" class="large-text"><?php echo esc_textarea( $data['expertise_text'] ); ?></textarea><p class="description"><?php esc_html_e( 'One tag per line.', 'anna-baylis' ); ?></p></td></tr>
			<tr><th scope="row"><label for="anna-content-about-cta-text"><?php esc_html_e( 'CTA Text', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-about-cta-text" name="anna_content_about[cta_text]" value="<?php echo esc_attr( $data['cta_text'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-about-cta-url"><?php esc_html_e( 'CTA URL', 'anna-baylis' ); ?></label></th><td><input type="url" id="anna-content-about-cta-url" name="anna_content_about[cta_url]" value="<?php echo esc_attr( $data['cta_url'] ); ?>" class="regular-text"></td></tr>
		</table>
		<?php
	}

	/**
	 * Render testimonials meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_testimonials_meta_box( $post ) {
		$data = $this->get_section_with_legacy_defaults( $post->ID, 'testimonials' );
		?>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-testimonials-eyebrow"><?php esc_html_e( 'Eyebrow', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-testimonials-eyebrow" name="anna_content_testimonials[eyebrow]" value="<?php echo esc_attr( $data['eyebrow'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-testimonials-heading"><?php esc_html_e( 'Heading', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-testimonials-heading" name="anna_content_testimonials[heading]" value="<?php echo esc_attr( $data['heading'] ); ?>" class="large-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-testimonials-summary"><?php esc_html_e( 'Summary', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-testimonials-summary" name="anna_content_testimonials[summary]" rows="2" class="large-text"><?php echo esc_textarea( $data['summary'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-testimonials-cta-text"><?php esc_html_e( 'CTA Text', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-testimonials-cta-text" name="anna_content_testimonials[cta_text]" value="<?php echo esc_attr( $data['cta_text'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-testimonials-cta-url"><?php esc_html_e( 'CTA URL', 'anna-baylis' ); ?></label></th><td><input type="url" id="anna-content-testimonials-cta-url" name="anna_content_testimonials[cta_url]" value="<?php echo esc_attr( $data['cta_url'] ); ?>" class="regular-text"></td></tr>
		</table>
		<p class="description"><?php esc_html_e( 'Testimonial cards still come from the Testimonials content type. This box controls the section copy around them.', 'anna-baylis' ); ?></p>
		<?php
	}

	/**
	 * Render CTA meta box.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_cta_meta_box( $post ) {
		$data = $this->get_section_with_legacy_defaults( $post->ID, 'cta' );
		?>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-cta-eyebrow"><?php esc_html_e( 'Eyebrow', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-cta-eyebrow" name="anna_content_cta[eyebrow]" value="<?php echo esc_attr( $data['eyebrow'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-cta-heading"><?php esc_html_e( 'Heading', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-cta-heading" name="anna_content_cta[heading]" rows="3" class="large-text"><?php echo esc_textarea( $data['heading'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-cta-description"><?php esc_html_e( 'Description', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-cta-description" name="anna_content_cta[description]" rows="3" class="large-text"><?php echo esc_textarea( $data['description'] ); ?></textarea></td></tr>
			<tr><th scope="row"><label for="anna-content-cta-trust"><?php esc_html_e( 'Trust Text', 'anna-baylis' ); ?></label></th><td><textarea id="anna-content-cta-trust" name="anna_content_cta[trust_text]" rows="3" class="large-text"><?php echo esc_textarea( $data['trust_text'] ); ?></textarea></td></tr>
		</table>
		<h4><?php esc_html_e( 'Primary Button', 'anna-baylis' ); ?></h4>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-cta-primary-text"><?php esc_html_e( 'Text', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-cta-primary-text" name="anna_content_cta[primary_button_text]" value="<?php echo esc_attr( $data['primary_button_text'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-cta-primary-url"><?php esc_html_e( 'URL', 'anna-baylis' ); ?></label></th><td><input type="url" id="anna-content-cta-primary-url" name="anna_content_cta[primary_button_url]" value="<?php echo esc_attr( $data['primary_button_url'] ); ?>" class="regular-text"></td></tr>
		</table>
		<h4><?php esc_html_e( 'Secondary Button', 'anna-baylis' ); ?></h4>
		<table class="form-table">
			<tr><th scope="row"><label for="anna-content-cta-secondary-text"><?php esc_html_e( 'Text', 'anna-baylis' ); ?></label></th><td><input type="text" id="anna-content-cta-secondary-text" name="anna_content_cta[secondary_button_text]" value="<?php echo esc_attr( $data['secondary_button_text'] ); ?>" class="regular-text"></td></tr>
			<tr><th scope="row"><label for="anna-content-cta-secondary-url"><?php esc_html_e( 'URL', 'anna-baylis' ); ?></label></th><td><input type="url" id="anna-content-cta-secondary-url" name="anna_content_cta[secondary_button_url]" value="<?php echo esc_attr( $data['secondary_button_url'] ); ?>" class="regular-text"></td></tr>
		</table>
		<?php
	}

	/**
	 * Save page content meta.
	 *
	 * @param int $post_id Current post ID.
	 */
	public function save_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_page_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anna_content_page_nonce'] ) ), 'anna_content_save_page' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['anna_content_hero'] ) && is_array( $_POST['anna_content_hero'] ) ) {
			$hero = wp_unslash( $_POST['anna_content_hero'] );
			$data = array(
				'eyebrow'               => sanitize_text_field( $hero['eyebrow'] ?? '' ),
				'heading'               => sanitize_textarea_field( $hero['heading'] ?? '' ),
				'description'           => sanitize_textarea_field( $hero['description'] ?? '' ),
				'trust_text'            => sanitize_text_field( $hero['trust_text'] ?? '' ),
				'image_id'              => absint( $hero['image_id'] ?? 0 ),
				'primary_button_text'   => sanitize_text_field( $hero['primary_button_text'] ?? '' ),
				'primary_button_url'    => esc_url_raw( $hero['primary_button_url'] ?? '' ),
				'secondary_button_text' => sanitize_text_field( $hero['secondary_button_text'] ?? '' ),
				'secondary_button_url'  => esc_url_raw( $hero['secondary_button_url'] ?? '' ),
				'stat_1_value'          => sanitize_text_field( $hero['stat_1_value'] ?? '' ),
				'stat_1_label'          => sanitize_text_field( $hero['stat_1_label'] ?? '' ),
				'stat_2_value'          => sanitize_text_field( $hero['stat_2_value'] ?? '' ),
				'stat_2_label'          => sanitize_text_field( $hero['stat_2_label'] ?? '' ),
				'stat_3_value'          => sanitize_text_field( $hero['stat_3_value'] ?? '' ),
				'stat_3_label'          => sanitize_text_field( $hero['stat_3_label'] ?? '' ),
			);

			update_post_meta( $post_id, '_anna_content_hero', $data );
		}
		if ( isset( $_POST['anna_content_intro'] ) && is_array( $_POST['anna_content_intro'] ) ) {
			$input = wp_unslash( $_POST['anna_content_intro'] );
			update_post_meta(
				$post_id,
				'_anna_content_intro',
				array(
					'intro_eyebrow'           => sanitize_text_field( $input['intro_eyebrow'] ?? '' ),
					'intro_heading'           => sanitize_textarea_field( $input['intro_heading'] ?? '' ),
					'intro_body'              => wp_kses_post( $input['intro_body'] ?? '' ),
					'intro_quote'             => sanitize_textarea_field( $input['intro_quote'] ?? '' ),
					'intro_quote_cite'        => sanitize_text_field( $input['intro_quote_cite'] ?? '' ),
					'recognition_eyebrow'     => sanitize_text_field( $input['recognition_eyebrow'] ?? '' ),
					'recognition_heading'     => sanitize_text_field( $input['recognition_heading'] ?? '' ),
					'recognition_description' => sanitize_textarea_field( $input['recognition_description'] ?? '' ),
					'recognition_items_text'  => sanitize_textarea_field( $input['recognition_items_text'] ?? '' ),
				)
			);
		}
		if ( isset( $_POST['anna_content_services'] ) && is_array( $_POST['anna_content_services'] ) ) {
			$input = wp_unslash( $_POST['anna_content_services'] );
			update_post_meta(
				$post_id,
				'_anna_content_services',
				array(
					'eyebrow'     => sanitize_text_field( $input['eyebrow'] ?? '' ),
					'heading'     => sanitize_text_field( $input['heading'] ?? '' ),
					'description' => sanitize_textarea_field( $input['description'] ?? '' ),
					'cta_text'    => sanitize_text_field( $input['cta_text'] ?? '' ),
					'cta_url'     => esc_url_raw( $input['cta_url'] ?? '' ),
				)
			);
		}
		if ( isset( $_POST['anna_content_about'] ) && is_array( $_POST['anna_content_about'] ) ) {
			$input = wp_unslash( $_POST['anna_content_about'] );
			update_post_meta(
				$post_id,
				'_anna_content_about',
				array(
					'eyebrow'        => sanitize_text_field( $input['eyebrow'] ?? '' ),
					'heading'        => sanitize_textarea_field( $input['heading'] ?? '' ),
					'body'           => wp_kses_post( $input['body'] ?? '' ),
					'quote'          => sanitize_textarea_field( $input['quote'] ?? '' ),
					'image_id'       => absint( $input['image_id'] ?? 0 ),
					'badge_number'   => sanitize_text_field( $input['badge_number'] ?? '' ),
					'badge_text'     => sanitize_text_field( $input['badge_text'] ?? '' ),
					'expertise_text' => sanitize_textarea_field( $input['expertise_text'] ?? '' ),
					'cta_text'       => sanitize_text_field( $input['cta_text'] ?? '' ),
					'cta_url'        => esc_url_raw( $input['cta_url'] ?? '' ),
				)
			);
		}
		if ( isset( $_POST['anna_content_testimonials'] ) && is_array( $_POST['anna_content_testimonials'] ) ) {
			$input = wp_unslash( $_POST['anna_content_testimonials'] );
			update_post_meta(
				$post_id,
				'_anna_content_testimonials',
				array(
					'eyebrow'  => sanitize_text_field( $input['eyebrow'] ?? '' ),
					'heading'  => sanitize_text_field( $input['heading'] ?? '' ),
					'summary'  => sanitize_textarea_field( $input['summary'] ?? '' ),
					'cta_text' => sanitize_text_field( $input['cta_text'] ?? '' ),
					'cta_url'  => esc_url_raw( $input['cta_url'] ?? '' ),
				)
			);
		}
		if ( isset( $_POST['anna_content_cta'] ) && is_array( $_POST['anna_content_cta'] ) ) {
			$input = wp_unslash( $_POST['anna_content_cta'] );
			update_post_meta(
				$post_id,
				'_anna_content_cta',
				array(
					'eyebrow'               => sanitize_text_field( $input['eyebrow'] ?? '' ),
					'heading'               => sanitize_textarea_field( $input['heading'] ?? '' ),
					'description'           => sanitize_textarea_field( $input['description'] ?? '' ),
					'trust_text'            => sanitize_textarea_field( $input['trust_text'] ?? '' ),
					'primary_button_text'   => sanitize_text_field( $input['primary_button_text'] ?? '' ),
					'primary_button_url'    => esc_url_raw( $input['primary_button_url'] ?? '' ),
					'secondary_button_text' => sanitize_text_field( $input['secondary_button_text'] ?? '' ),
					'secondary_button_url'  => esc_url_raw( $input['secondary_button_url'] ?? '' ),
				)
			);
		}
	}

	/**
	 * Get page section meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $section Section key.
	 * @return array
	 */
	public function get_page_section( $post_id, $section ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return array();
		}

		$data = get_post_meta( $post_id, '_anna_content_' . sanitize_key( $section ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Get page section data with legacy theme option fallback.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $section Section key.
	 * @return array
	 */
	private function get_section_with_legacy_defaults( $post_id, $section ) {
		$stored = $this->get_page_section( $post_id, $section );
		$legacy = $this->get_legacy_section_defaults( $section );

		return wp_parse_args( $stored, $legacy );
	}

	/**
	 * Get legacy theme-option defaults for a section.
	 *
	 * @param string $section Section key.
	 * @return array
	 */
	private function get_legacy_section_defaults( $section ) {
		$options = get_option( 'anna_theme_options', array() );
		$options = is_array( $options ) ? $options : array();

		switch ( $section ) {
			case 'hero':
				return array(
					'eyebrow'               => $options['hero_eyebrow'] ?? '',
					'heading'               => isset( $options['hero_heading'] ) ? wp_strip_all_tags( str_replace( '<br>', "\n", (string) $options['hero_heading'] ) ) : '',
					'description'           => $options['hero_description'] ?? '',
					'trust_text'            => $options['hero_trust_text'] ?? '',
					'image_id'              => absint( $options['hero_image_id'] ?? 0 ),
					'primary_button_text'   => $options['cta_primary_text'] ?? '',
					'primary_button_url'    => $options['cta_primary_url'] ?? '',
					'secondary_button_text' => $options['cta_secondary_text'] ?? '',
					'secondary_button_url'  => $options['cta_secondary_url'] ?? '',
					'stat_1_value'          => $options['stat_1_value'] ?? '',
					'stat_1_label'          => $options['stat_1_label'] ?? '',
					'stat_2_value'          => $options['stat_2_value'] ?? '',
					'stat_2_label'          => $options['stat_2_label'] ?? '',
					'stat_3_value'          => $options['stat_3_value'] ?? '',
					'stat_3_label'          => $options['stat_3_label'] ?? '',
				);
			case 'intro':
				return array(
					'intro_eyebrow'           => $options['intro_eyebrow'] ?? '',
					'intro_heading'           => isset( $options['intro_heading'] ) ? wp_strip_all_tags( (string) $options['intro_heading'] ) : '',
					'intro_body'              => $options['intro_body'] ?? '',
					'intro_quote'             => $options['intro_quote'] ?? '',
					'intro_quote_cite'        => $options['intro_quote_cite'] ?? '',
					'recognition_eyebrow'     => $options['recognition_eyebrow'] ?? '',
					'recognition_heading'     => $options['recognition_heading'] ?? '',
					'recognition_description' => $options['recognition_description'] ?? '',
					'recognition_items_text'  => $options['recognition_items_text'] ?? '',
				);
			case 'services':
				return array(
					'eyebrow'     => $options['services_eyebrow'] ?? '',
					'heading'     => $options['services_heading'] ?? '',
					'description' => $options['services_description'] ?? '',
					'cta_text'    => $options['services_cta_text'] ?? '',
					'cta_url'     => $options['services_cta_url'] ?? '',
				);
			case 'about':
				return array(
					'eyebrow'        => $options['about_eyebrow'] ?? '',
					'heading'        => isset( $options['about_heading'] ) ? wp_strip_all_tags( (string) $options['about_heading'] ) : '',
					'body'           => $options['about_body'] ?? '',
					'quote'          => $options['about_quote'] ?? '',
					'image_id'       => absint( $options['about_image_id'] ?? 0 ),
					'badge_number'   => $options['about_badge_number'] ?? '',
					'badge_text'     => $options['about_badge_text'] ?? '',
					'expertise_text' => $options['about_expertise_text'] ?? '',
					'cta_text'       => $options['about_cta_text'] ?? '',
					'cta_url'        => $options['about_cta_url'] ?? '',
				);
			case 'testimonials':
				return array(
					'eyebrow'  => $options['testimonials_eyebrow'] ?? '',
					'heading'  => $options['testimonials_heading'] ?? '',
					'summary'  => $options['testimonials_summary'] ?? '',
					'cta_text' => $options['testimonials_cta_text'] ?? '',
					'cta_url'  => $options['testimonials_cta_url'] ?? '',
				);
			case 'cta':
				return array(
					'eyebrow'               => $options['cta_eyebrow'] ?? '',
					'heading'               => isset( $options['cta_heading'] ) ? wp_strip_all_tags( (string) $options['cta_heading'] ) : '',
					'description'           => $options['cta_description'] ?? '',
					'trust_text'            => $options['cta_trust'] ?? '',
					'primary_button_text'   => $options['cta_primary_text'] ?? '',
					'primary_button_url'    => $options['cta_primary_url'] ?? '',
					'secondary_button_text' => $options['cta_secondary_text'] ?? '',
					'secondary_button_url'  => $options['cta_secondary_url'] ?? '',
				);
			default:
				return array();
		}
	}
}

/**
 * Public helper for theme templates.
 *
 * @param int    $post_id Post ID.
 * @param string $section Section key.
 * @return array
 */
function anna_content_get_page_section( $post_id, $section ) {
	return Anna_Content_Manager::instance()->get_page_section( $post_id, $section );
}
