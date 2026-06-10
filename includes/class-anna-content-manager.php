<?php
/**
 * Anna Content Manager plugin bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Anna_Content_Manager {
	use Anna_Oasis_Page_Content;
	use Anna_Speaking_Page_Content;
	use Anna_Mhs_Page_Content;
	use Anna_Move_Page_Content;
	use Anna_Scaffolded_Page_Content;
	use Anna_Contact_Page_Content;
	use Anna_Reviews_Page_Content;
	use Anna_Blog_Page_Content;
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

		$theme_options = self::get_theme_options_with_defaults();

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
		add_action( 'admin_init', array( $this, 'hide_editor_for_managed_templates' ) );
	}

	/**
	 * Hide the classic editor content area for pages using our custom templates.
	 * Clients only need to use the meta boxes below — the editor is irrelevant
	 * and confusing when a custom template is active.
	 */
	public function hide_editor_for_managed_templates() {
		$managed_templates = array(
			'page-contact.php',
			'page-reviews.php',
			'page-oasis.php',
			'page-coaching.php',
			'page-speaking.php',
			'page-mental-health-support.php',
			'page-move.php',
			'page-about.php',
		);

		// Also hide for scaffolded page templates.
		if ( function_exists( 'anna_get_scaffolded_pages' ) ) {
			foreach ( anna_get_scaffolded_pages() as $page ) {
				if ( ! empty( $page['template'] ) ) {
					$managed_templates[] = $page['template'];
				}
			}
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'page' !== $screen->post_type ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id ) {
			return;
		}

		$template = get_page_template_slug( $post_id );
		if ( in_array( $template, $managed_templates, true ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}

	/**
	 * Register page-level content meta boxes.
	 */
	public function register_meta_boxes( $post = null ) {
		$is_page          = $post instanceof WP_Post;
		$is_about_page    = $is_page && ( 'about' === $post->post_name || 'page-about.php' === get_page_template_slug( $post->ID ) );
		$is_coaching_page = $is_page && ( 'coaching' === $post->post_name || 'page-coaching.php' === get_page_template_slug( $post->ID ) );

		if ( $is_about_page ) {
			add_meta_box(
				'anna_content_about_page',
				__( 'Anna About Page Content', 'anna-baylis' ),
				array( $this, 'render_about_page_meta_box' ),
				'page',
				'normal',
				'high'
			);
		}

		if ( $is_coaching_page ) {
			add_meta_box(
				'anna_content_coaching_page',
				__( 'Anna Coaching Page Content', 'anna-baylis' ),
				array( $this, 'render_coaching_page_meta_box' ),
				'page',
				'normal',
				'high'
			);
		}

		if ( $is_page ) {
			$this->register_oasis_page_meta_box( $post );
			$this->register_speaking_page_meta_box( $post );
			$this->register_mhs_page_meta_box( $post );
			$this->register_move_page_meta_box( $post );
			$this->register_scaffolded_page_meta_boxes( $post );
			$this->register_contact_page_meta_box( $post );
			$this->register_reviews_page_meta_box( $post );
			$this->register_blog_page_meta_box( $post );
		}

		// Home page now uses a single metabox registered by the theme (inc/home-helpers.php).
		// The individual per-section metaboxes (hero, intro, services, about, testimonials, cta)
		// have been removed; all home content is stored in one meta row: _anna_content_home_page.
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
		$css_path = ANNA_CONTENT_MANAGER_DIR . 'assets/css/admin-page-content.css';
		$js_path  = ANNA_CONTENT_MANAGER_DIR . 'assets/js/admin-page-content.js';

		wp_enqueue_style(
			'anna-content-manager-admin',
			ANNA_CONTENT_MANAGER_URL . 'assets/css/admin-page-content.css',
			array(),
			file_exists( $css_path ) ? (string) filemtime( $css_path ) : ANNA_CONTENT_MANAGER_VERSION
		);
		wp_enqueue_script(
			'anna-content-manager-admin',
			ANNA_CONTENT_MANAGER_URL . 'assets/js/admin-page-content.js',
			array( 'jquery' ),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : ANNA_CONTENT_MANAGER_VERSION,
			true
		);

		$layout_js = ANNA_CONTENT_MANAGER_DIR . 'assets/js/section-layout.js';
		if ( file_exists( $layout_js ) ) {
			wp_enqueue_script(
				'anna-content-section-layout',
				ANNA_CONTENT_MANAGER_URL . 'assets/js/section-layout.js',
				array( 'jquery', 'anna-content-manager-admin' ),
				(string) filemtime( $layout_js ),
				true
			);
		}
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
		<p class="description" style="padding:0.6rem 0.9rem;background:#f0f6fc;border-left:3px solid #72aee6;border-radius:2px;font-size:12px;">
			<?php esc_html_e( 'Tip: type', 'anna-baylis' ); ?> <code>empty--</code> <?php esc_html_e( 'in any field to hide it on the frontend and suppress the default content.', 'anna-baylis' ); ?>
		</p>
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
	 * Render fixed About page content fields.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_about_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_about_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_about_page_meta( $post->ID, $data );
		?>
		<p><?php esc_html_e( 'These fields feed the fixed About page design. Admins can edit copy and images only; the section layout stays in the theme.', 'anna-baylis' ); ?></p>
		<p class="description" style="padding:0.6rem 0.9rem;background:#f0f6fc;border-left:3px solid #72aee6;border-radius:2px;font-size:12px;">
			<?php esc_html_e( 'Tip: type', 'anna-baylis' ); ?> <code>empty--</code> <?php esc_html_e( 'in any field to hide it on the frontend and suppress the default content.', 'anna-baylis' ); ?>
		</p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_about_page', 'hero_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['hero_eyebrow'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'], 2 ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'hero_subheading', __( 'Subheading (optional)', 'anna-baylis' ), $data['hero_subheading'], 2 ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'hero_description', __( 'Description (optional)', 'anna-baylis' ), $data['hero_description'], 3 ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'hero_tags', __( 'Hero Tags', 'anna-baylis' ), is_array( $data['hero_tags'] ) ? implode( "\n", $data['hero_tags'] ) : $data['hero_tags'], 6, __( 'One tag per line.', 'anna-baylis' ) ); ?>
			<?php $this->render_media_field( 'anna_content_about_page', 'hero_image_id', __( 'Hero Background Image', 'anna-baylis' ), $data['hero_image_id'] ); ?>
		</table>

		<h3><?php esc_html_e( 'Story Beginning', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_media_field( 'anna_content_about_page', 'story_image_id', __( 'Portrait Image', 'anna-baylis' ), $data['story_image_id'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'story_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['story_eyebrow'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'story_heading', __( 'Heading', 'anna-baylis' ), $data['story_heading'], 2 ); ?>
			<?php $this->render_editor_field( 'anna_content_about_page', 'story_body', __( 'Body', 'anna-baylis' ), $data['story_body'], $post->ID ); ?>
		</table>

		<h3><?php esc_html_e( 'My Rock Bottom', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_about_page', 'rock_heading', __( 'Heading', 'anna-baylis' ), $data['rock_heading'] ); ?>
			<?php $this->render_editor_field( 'anna_content_about_page', 'rock_left_body', __( 'Left Column', 'anna-baylis' ), $data['rock_left_body'], $post->ID ); ?>
			<?php $this->render_editor_field( 'anna_content_about_page', 'rock_right_body', __( 'Right Column', 'anna-baylis' ), $data['rock_right_body'], $post->ID ); ?>
		</table>

		<h3><?php esc_html_e( 'How I Became a Coach', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_about_page', 'coach_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['coach_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'coach_title', __( 'Heading', 'anna-baylis' ), $data['coach_title'] ); ?>
			<?php $this->render_editor_field( 'anna_content_about_page', 'coach_body', __( 'Body', 'anna-baylis' ), $data['coach_body'], $post->ID ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'coach_button_text', __( 'Button Text', 'anna-baylis' ), $data['coach_button_text'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'coach_button_url', __( 'Button URL', 'anna-baylis' ), $data['coach_button_url'] ); ?>
			<?php $this->render_media_field( 'anna_content_about_page', 'coach_image_id', __( 'Right Image', 'anna-baylis' ), $data['coach_image_id'] ); ?>
		</table>

		<h3><?php esc_html_e( 'How I Work', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_about_page', 'work_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['work_eyebrow'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'work_heading', __( 'Heading', 'anna-baylis' ), $data['work_heading'], 2 ); ?>
			<?php $this->render_editor_field( 'anna_content_about_page', 'work_body', __( 'Left Column Copy', 'anna-baylis' ), $data['work_body'], $post->ID ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'work_card_1_title', __( 'Card 1 Title', 'anna-baylis' ), $data['work_card_1_title'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'work_card_1_body', __( 'Card 1 Body', 'anna-baylis' ), $data['work_card_1_body'], 3 ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'work_card_2_title', __( 'Card 2 Title', 'anna-baylis' ), $data['work_card_2_title'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'work_card_2_body', __( 'Card 2 Body', 'anna-baylis' ), $data['work_card_2_body'], 3 ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'work_card_3_title', __( 'Card 3 Title', 'anna-baylis' ), $data['work_card_3_title'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'work_card_3_body', __( 'Card 3 Body', 'anna-baylis' ), $data['work_card_3_body'], 3 ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'work_card_4_title', __( 'Card 4 Title', 'anna-baylis' ), $data['work_card_4_title'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'work_card_4_body', __( 'Card 4 Body', 'anna-baylis' ), $data['work_card_4_body'], 3 ); ?>
		</table>

		<h3><?php esc_html_e( 'What People Say', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_about_page', 'people_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['people_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'people_heading', __( 'Heading', 'anna-baylis' ), $data['people_heading'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_about_page', 'people_body', __( 'Intro', 'anna-baylis' ), $data['people_body'], 3 ); ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Cards', 'anna-baylis' ); ?></th>
				<td>
					<p class="description"><?php esc_html_e( 'Optional logo, or initials in the green circle when no logo is set.', 'anna-baylis' ); ?></p>
					<?php
					$people_items = isset( $data['people_items'] ) && is_array( $data['people_items'] ) ? $data['people_items'] : array();
					$people_count = count( $people_items );
					?>
					<div class="anna-repeater-collapse">
						<button type="button" class="anna-repeater-collapse__toggle" aria-expanded="false">
							<span class="anna-repeater-collapse__arrow" aria-hidden="true">▶</span>
							<span class="anna-repeater-collapse__label">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %d: number of qualification cards */
										__( 'Show all cards (%d)', 'anna-baylis' ),
										$people_count
									)
								);
								?>
							</span>
						</button>
						<div class="anna-repeater-collapse__panel is-collapsed" data-anna-repeater-collapse-panel="true">
					<div class="anna-content-repeater" data-anna-content-repeater="people-items">
						<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
							<?php foreach ( $people_items as $index => $item ) : ?>
								<?php
								if ( ! is_array( $item ) ) {
									continue;
								}
								$logo_id     = absint( $item['logo_id'] ?? 0 );
								$initials    = (string) ( $item['initials'] ?? '' );
								$title       = (string) ( $item['title'] ?? '' );
								$description = (string) ( $item['org'] ?? $item['description'] ?? '' );
								$input_id    = 'anna-content-about-people-' . (int) $index . '-logo-id';
								$preview_id  = 'anna-content-about-people-' . (int) $index . '-logo-preview';
								$img_url     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'thumbnail' ) : '';
								?>
								<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
									<p><strong><?php esc_html_e( 'Card', 'anna-baylis' ); ?></strong></p>
									<p>
										<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="anna_content_about_page[people_items][<?php echo esc_attr( $index ); ?>][logo_id]" value="<?php echo esc_attr( $logo_id ); ?>">
										<span id="<?php echo esc_attr( $preview_id ); ?>" style="display:block;margin:8px 0;">
											<?php if ( $img_url ) : ?>
												<img src="<?php echo esc_url( $img_url ); ?>" alt="" style="max-width:120px;height:auto;border-radius:10px;">
											<?php endif; ?>
										</span>
										<button type="button" class="button anna-content-media-select" data-target="<?php echo esc_attr( $input_id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>"><?php esc_html_e( 'Select Logo', 'anna-baylis' ); ?></button>
										<button type="button" class="button anna-content-media-remove" data-target="<?php echo esc_attr( $input_id ); ?>" data-preview="<?php echo esc_attr( $preview_id ); ?>"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button>
									</p>
									<p>
										<label><?php esc_html_e( 'Initials', 'anna-baylis' ); ?><br>
											<input type="text" class="small-text" name="anna_content_about_page[people_items][<?php echo esc_attr( $index ); ?>][initials]" value="<?php echo esc_attr( $initials ); ?>">
										</label>
									</p>
									<p>
										<label><?php esc_html_e( 'Title', 'anna-baylis' ); ?><br>
											<input type="text" class="large-text" name="anna_content_about_page[people_items][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>">
										</label>
									</p>
									<p>
										<label><?php esc_html_e( 'Description', 'anna-baylis' ); ?><br>
											<textarea class="large-text" rows="2" name="anna_content_about_page[people_items][<?php echo esc_attr( $index ); ?>][description]"><?php echo esc_textarea( $description ); ?></textarea>
										</label>
									</p>
									<p>
										<button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove card', 'anna-baylis' ); ?></button>
									</p>
									<hr>
								</div>
							<?php endforeach; ?>
						</div>

						<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Card', 'anna-baylis' ); ?></button>

						<template data-anna-content-repeater-template="true">
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><strong><?php esc_html_e( 'Card', 'anna-baylis' ); ?></strong></p>
								<p>
									<input type="hidden" id="anna-content-about-people-__INDEX__-logo-id" name="anna_content_about_page[people_items][__INDEX__][logo_id]" value="">
									<span id="anna-content-about-people-__INDEX__-logo-preview" style="display:block;margin:8px 0;"></span>
									<button type="button" class="button anna-content-media-select" data-target="anna-content-about-people-__INDEX__-logo-id" data-preview="anna-content-about-people-__INDEX__-logo-preview"><?php esc_html_e( 'Select Logo', 'anna-baylis' ); ?></button>
									<button type="button" class="button anna-content-media-remove" data-target="anna-content-about-people-__INDEX__-logo-id" data-preview="anna-content-about-people-__INDEX__-logo-preview"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button>
								</p>
								<p>
									<label><?php esc_html_e( 'Initials', 'anna-baylis' ); ?><br>
										<input type="text" class="small-text" name="anna_content_about_page[people_items][__INDEX__][initials]" value="">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Title', 'anna-baylis' ); ?><br>
										<input type="text" class="large-text" name="anna_content_about_page[people_items][__INDEX__][title]" value="">
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Description', 'anna-baylis' ); ?><br>
										<textarea class="large-text" rows="2" name="anna_content_about_page[people_items][__INDEX__][description]"></textarea>
									</label>
								</p>
								<p>
									<button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove card', 'anna-baylis' ); ?></button>
								</p>
								<hr>
							</div>
						</template>
					</div>
						</div>
					</div>
				</td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'I would love to connect', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_about_page', 'connect_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['connect_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'connect_heading', __( 'Heading', 'anna-baylis' ), $data['connect_heading'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'connect_button_text', __( 'Button Text', 'anna-baylis' ), $data['connect_button_text'] ); ?>
			<?php $this->render_text_field( 'anna_content_about_page', 'connect_button_url', __( 'Button URL', 'anna-baylis' ), $data['connect_button_url'] ); ?>
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
	 * Render a text input row.
	 *
	 * @param string $group Field group.
	 * @param string $key Field key.
	 * @param string $label Field label.
	 * @param string $value Field value.
	 */
	private function render_text_field( $group, $key, $label, $value ) {
		$id = sanitize_key( $group . '_' . $key );
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $group ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="large-text"></td>
		</tr>
		<?php
	}

	/**
	 * Render a textarea row.
	 *
	 * @param string $group Field group.
	 * @param string $key Field key.
	 * @param string $label Field label.
	 * @param string $value Field value.
	 * @param int    $rows Textarea rows.
	 * @param string $description Optional description.
	 */
	private function render_textarea_field( $group, $key, $label, $value, $rows = 4, $description = '' ) {
		$id = sanitize_key( $group . '_' . $key );
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $group ); ?>[<?php echo esc_attr( $key ); ?>]" rows="<?php echo esc_attr( $rows ); ?>" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
				<?php if ( $description ) : ?>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render an editor row.
	 *
	 * @param string $group Field group.
	 * @param string $key Field key.
	 * @param string $label Field label.
	 * @param string $value Field value.
	 * @param int    $post_id Post ID for unique editor IDs.
	 */
	private function render_editor_field( $group, $key, $label, $value, $post_id ) {
		$id = sanitize_key( $group . '_' . $key . '_' . $post_id );
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<?php
				wp_editor(
					$value,
					$id,
					array(
						'textarea_name' => $group . '[' . $key . ']',
						'textarea_rows' => 6,
						'media_buttons' => false,
					)
				);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render a media selector row.
	 *
	 * @param string $group Field group.
	 * @param string $key Field key.
	 * @param string $label Field label.
	 * @param int    $value Attachment ID.
	 */
	private function render_media_field( $group, $key, $label, $value ) {
		$id        = sanitize_key( $group . '_' . $key );
		$preview   = $id . '_preview';
		$image_url = $value ? wp_get_attachment_image_url( absint( $value ), 'medium' ) : '';
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $group ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
				<div id="<?php echo esc_attr( $preview ); ?>" style="margin-bottom:10px;">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:220px;height:auto;border-radius:10px;">
					<?php endif; ?>
				</div>
				<button type="button" class="button anna-content-media-select" data-target="<?php echo esc_attr( $id ); ?>" data-preview="<?php echo esc_attr( $preview ); ?>"><?php esc_html_e( 'Select Image', 'anna-baylis' ); ?></button>
				<button type="button" class="button anna-content-media-remove" data-target="<?php echo esc_attr( $id ); ?>" data-preview="<?php echo esc_attr( $preview ); ?>"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button>
			</td>
		</tr>
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

		if ( isset( $_POST['anna_content_about_page'] ) && is_array( $_POST['anna_content_about_page'] ) ) {
			$input = wp_unslash( $_POST['anna_content_about_page'] );
			update_post_meta( $post_id, '_anna_content_about_page', $this->sanitize_about_page_content( $input ) );
		}

		if ( isset( $_POST['anna_content_coaching_page'] ) && is_array( $_POST['anna_content_coaching_page'] ) ) {
			$input = wp_unslash( $_POST['anna_content_coaching_page'] );
			update_post_meta( $post_id, '_anna_content_coaching_page', $this->sanitize_coaching_page_content( $input ) );
		}

		$this->save_oasis_page_content( $post_id );
		$this->save_speaking_page_content( $post_id );
		$this->save_mhs_page_content( $post_id );
		$this->save_move_page_content( $post_id );
		$this->save_scaffolded_page_content( $post_id );
		$this->save_contact_page_content( $post_id );
		$this->save_reviews_page_content( $post_id );
		$this->save_blog_page_content( $post_id );

		// Home page sections (hero, intro, services, about, testimonials, cta) are now
		// saved by the theme's anna_save_home_page_content_meta_box() in inc/home-helpers.php.
		// They are no longer saved here — all home content lives in one meta row: _anna_content_home_page.
	}

	/**
	 * Get fixed About page content.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_about_page_content( $post_id ) {
		return $this->get_about_page_content_with_defaults( $post_id );
	}

	/**
	 * Get fixed About page content with defaults.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_about_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_about_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_about_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		foreach ( $defaults as $key => $default_value ) {
			if ( 'people_items' === $key ) {
				continue;
			}

			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $default_value, $key ) ) {
					$merged[ $key ] = $default_value;
				}
			}
		}

		// wp_parse_args keeps an empty people_items array from saved meta; fill from defaults/theme.
		$merged['people_items'] = $this->resolve_about_people_items( $stored, $defaults );

		return $merged;
	}

	/**
	 * Resolve repeater rows for "What people say" (saved meta + legacy + theme defaults).
	 *
	 * @param array $stored  Saved post meta (partial).
	 * @param array $defaults Plugin defaults for the About page.
	 * @return array<int, array{logo_id:int,initials:string,title:string,org:string}>
	 */
	private function resolve_about_people_items( $stored, $defaults ) {
		if ( isset( $stored['people_items'] ) && is_array( $stored['people_items'] ) && ! empty( $stored['people_items'] ) ) {
			if ( is_string( reset( $stored['people_items'] ) ) && function_exists( 'anna_parse_about_people_items' ) && function_exists( 'anna_normalize_about_people_items' ) ) {
				$lines = implode( "\n", array_map( 'strval', $stored['people_items'] ) );
				$items = anna_normalize_about_people_items( anna_parse_about_people_items( $lines ) );
				if ( ! empty( $items ) ) {
					return $items;
				}
			}

			$items = $this->normalize_about_people_items( $stored['people_items'] );
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		if ( isset( $stored['qualifications'] ) && is_array( $stored['qualifications'] ) && ! empty( $stored['qualifications'] ) ) {
			if ( function_exists( 'anna_convert_qualifications_to_people_items' ) ) {
				$items = anna_convert_qualifications_to_people_items( $stored['qualifications'] );
				if ( ! empty( $items ) ) {
					return $items;
				}
			}

			$items = $this->normalize_about_people_items( $stored['qualifications'] );
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		if ( function_exists( 'anna_get_about_people_items_from_options' ) ) {
			$items = anna_get_about_people_items_from_options();
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['people_items'] ?? array();
		return $this->normalize_about_people_items( $default_items );
	}

	/**
	 * Normalize people repeater rows for admin + frontend.
	 *
	 * @param array $rows Raw rows.
	 * @return array<int, array{logo_id:int,initials:string,title:string,org:string}>
	 */
	private function normalize_about_people_items( $rows ) {
		if ( function_exists( 'anna_normalize_about_people_items' ) ) {
			return anna_normalize_about_people_items( $rows );
		}

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$items = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$logo_id  = absint( $row['logo_id'] ?? 0 );
			$initials = sanitize_text_field( $row['initials'] ?? '' );
			$title    = sanitize_text_field( $row['title'] ?? '' );
			$org      = sanitize_textarea_field( $row['org'] ?? $row['description'] ?? '' );

			if ( 0 === $logo_id && '' === trim( $initials ) && '' === trim( $title ) && '' === trim( $org ) ) {
				continue;
			}

			$items[] = array(
				'logo_id'  => $logo_id,
				'initials' => $initials,
				'title'    => $title,
				'org'      => $org,
			);
		}

		return $items;
	}

	/**
	 * Backfill blank About page meta from resolved defaults (one-time per page).
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Resolved About page content.
	 */
	private function maybe_backfill_about_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! is_array( $data ) ) {
			return;
		}

		if ( get_post_meta( $post_id, '_anna_about_meta_backfilled_v2', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_about_page', true );
		$stored  = is_array( $stored ) ? $stored : array();
		$changed = false;

		foreach ( $data as $key => $value ) {
			if ( 'people_items' === $key ) {
				continue;
			}

			if ( ! array_key_exists( $key, $stored ) || $this->is_blank_section_value( $stored[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $value, $key ) ) {
					$stored[ $key ] = $value;
					$changed        = true;
				}
			}
		}

		$has_items = false;
		if ( isset( $stored['people_items'] ) && is_array( $stored['people_items'] ) ) {
			$has_items = ! empty( $this->normalize_about_people_items( $stored['people_items'] ) );
		}

		if ( ! $has_items && ! empty( $data['people_items'] ) ) {
			$stored['people_items'] = $data['people_items'];
			$changed                = true;
		}

		if ( $changed ) {
			update_post_meta( $post_id, '_anna_content_about_page', $stored );
		}

		update_post_meta( $post_id, '_anna_about_meta_backfilled_v2', 1 );
	}

	/**
	 * Sanitize fixed About page content.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	private function sanitize_about_page_content( $input ) {
		$text_fields = array(
			'hero_eyebrow',
			'hero_description',
			'story_eyebrow',
			'rock_heading',
			'coach_eyebrow',
			'coach_title',
			'coach_button_text',
			'coach_button_url',
			'work_eyebrow',
			'work_heading',
			'work_card_1_title',
			'work_card_2_title',
			'work_card_3_title',
			'work_card_4_title',
			'people_eyebrow',
			'people_heading',
			'people_body',
			'connect_eyebrow',
			'connect_heading',
			'connect_button_text',
			'connect_button_url',
		);

		$textarea_fields = array(
			'hero_heading',
			'hero_subheading',
			'story_heading',
			'hero_tags',
			'work_card_1_body',
			'work_card_2_body',
			'work_card_3_body',
			'work_card_4_body',
		);

		$html_fields = array(
			'story_body',
			'rock_left_body',
			'rock_right_body',
			'coach_body',
			'work_body',
		);

		$image_fields = array(
			'hero_image_id',
			'story_image_id',
			'coach_image_id',
		);

		$data = array();

		foreach ( $text_fields as $field ) {
			$data[ $field ] = sanitize_text_field( $input[ $field ] ?? '' );
		}

		foreach ( array( 'coach_button_url', 'connect_button_url' ) as $url_field ) {
			$data[ $url_field ] = esc_url_raw( $input[ $url_field ] ?? '' );
		}

		foreach ( $textarea_fields as $field ) {
			$data[ $field ] = sanitize_textarea_field( $input[ $field ] ?? '' );
		}

		foreach ( $html_fields as $field ) {
			$data[ $field ] = wp_kses_post( $input[ $field ] ?? '' );
		}

		foreach ( $image_fields as $field ) {
			$data[ $field ] = absint( $input[ $field ] ?? 0 );
		}

		if ( ! empty( $data['hero_tags'] ) ) {
			$tags = preg_split( '/\r\n|\r|\n/', $data['hero_tags'] );
			$data['hero_tags'] = array_values( array_filter( array_map( 'trim', $tags ) ) );
		} else {
			$data['hero_tags'] = array();
		}

		$data['people_items'] = array();
		if ( isset( $input['people_items'] ) && is_array( $input['people_items'] ) ) {
			foreach ( $input['people_items'] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$logo_id  = absint( $row['logo_id'] ?? 0 );
				$initials = sanitize_text_field( $row['initials'] ?? '' );
				$title    = sanitize_text_field( $row['title'] ?? '' );
				$org      = sanitize_textarea_field( $row['description'] ?? $row['org'] ?? '' );

				if ( 0 === $logo_id && '' === trim( $initials ) && '' === trim( $title ) && '' === trim( $org ) ) {
					continue;
				}

				$data['people_items'][] = array(
					'logo_id'  => $logo_id,
					'initials' => $initials,
					'title'    => $title,
					'org'      => $org,
				);
			}
		}

		$defaults = $this->get_about_page_defaults();
		$merged   = wp_parse_args( $data, $defaults );

		if ( empty( $merged['people_items'] ) ) {
			$merged['people_items'] = $defaults['people_items'] ?? array();
		}

		return $merged;
	}

	/**
	 * Map theme option keys to About page meta box keys.
	 *
	 * @return array<string, string>
	 */
	private function get_about_page_theme_option_map() {
		return array(
			'hero_eyebrow'       => 'about_pg_hero_eyebrow',
			'hero_heading'       => 'about_pg_hero_heading',
			'hero_subheading'    => 'about_pg_hero_subheading',
			'hero_description'   => 'about_pg_hero_description',
			'hero_image_id'      => 'about_pg_hero_image_id',
			'story_eyebrow'      => 'about_pg_story_eyebrow',
			'story_heading'      => 'about_pg_story_heading',
			'story_body'         => 'about_pg_story_body',
			'story_image_id'     => 'about_pg_story_image_id',
			'rock_heading'       => 'about_pg_rock_heading',
			'rock_left_body'     => 'about_pg_rock_left_body',
			'rock_right_body'    => 'about_pg_rock_right_body',
			'coach_eyebrow'      => 'about_pg_coach_eyebrow',
			'coach_title'        => 'about_pg_coach_title',
			'coach_body'         => 'about_pg_coach_body',
			'coach_button_text'  => 'about_pg_coach_button_text',
			'coach_button_url'   => 'about_pg_coach_button_url',
			'coach_image_id'     => 'about_pg_coach_image_id',
			'work_eyebrow'       => 'about_pg_work_eyebrow',
			'work_heading'       => 'about_pg_work_heading',
			'work_body'          => 'about_pg_work_body',
			'work_card_1_title'  => 'about_pg_work_card_1_title',
			'work_card_1_body'   => 'about_pg_work_card_1_body',
			'work_card_2_title'  => 'about_pg_work_card_2_title',
			'work_card_2_body'   => 'about_pg_work_card_2_body',
			'work_card_3_title'  => 'about_pg_work_card_3_title',
			'work_card_3_body'   => 'about_pg_work_card_3_body',
			'work_card_4_title'  => 'about_pg_work_card_4_title',
			'work_card_4_body'   => 'about_pg_work_card_4_body',
			'people_eyebrow'     => 'about_pg_people_eyebrow',
			'people_heading'     => 'about_pg_people_heading',
			'people_body'        => 'about_pg_people_body',
			'connect_eyebrow'    => 'about_pg_connect_eyebrow',
			'connect_heading'    => 'about_pg_connect_heading',
			'connect_button_text'=> 'about_pg_connect_button_text',
			'connect_button_url' => 'about_pg_connect_button_url',
		);
	}

	/**
	 * Pull About page defaults from theme options when available.
	 *
	 * @return array
	 */
	private function get_theme_mapped_about_defaults() {
		$theme = self::get_theme_options_with_defaults();
		$map   = $this->get_about_page_theme_option_map();
		$out   = array();

		foreach ( $map as $plugin_key => $theme_key ) {
			if ( ! isset( $theme[ $theme_key ] ) ) {
				continue;
			}

			$value = $theme[ $theme_key ];
			if ( str_ends_with( $plugin_key, '_image_id' ) ) {
				$out[ $plugin_key ] = absint( $value );
			} else {
				$out[ $plugin_key ] = $value;
			}
		}

		if ( ! empty( $theme['about_pg_hero_tags_text'] ) ) {
			$tags = preg_split( '/\r\n|\r|\n/', (string) $theme['about_pg_hero_tags_text'] );
			$out['hero_tags'] = array_values( array_filter( array_map( 'trim', (array) $tags ) ) );
		}

		if ( ! empty( $theme['about_pg_people_items'] ) && is_array( $theme['about_pg_people_items'] ) ) {
			$out['people_items'] = $this->normalize_about_people_items( $theme['about_pg_people_items'] );
		} elseif ( function_exists( 'anna_get_about_people_items_from_options' ) ) {
			$items = anna_get_about_people_items_from_options();
			if ( ! empty( $items ) ) {
				$out['people_items'] = $items;
			}
		}

		return $out;
	}

	/**
	 * Default fixed About page content.
	 *
	 * @return array
	 */
	private function get_about_page_defaults() {
		$defaults = array(
			'hero_eyebrow'       => __( 'About Anna', 'anna-baylis' ),
			'hero_heading'       => __( "Hi, I'm Anna.\nI became the coach\nI am because of\nwhat I've lived through.", 'anna-baylis' ),
			'hero_subheading'    => '',
			'hero_description'   => '',
			'hero_tags'          => array( 'Olympian', 'Hawaii Ironman', 'IFS Trained', 'Somatic Psychology', 'Trauma-Informed' ),
			'hero_image_id'      => 0,
			'story_eyebrow'      => __( 'About Anna', 'anna-baylis' ),
			'story_heading'      => __( 'My story the beginning', 'anna-baylis' ),
			'story_body'         => '',
			'story_image_id'     => 0,
			'rock_heading'       => __( 'My rock bottom', 'anna-baylis' ),
			'rock_left_body'     => '',
			'rock_right_body'    => '',
			'coach_eyebrow'      => __( 'How I Became a Coach', 'anna-baylis' ),
			'coach_title'        => __( 'A defining moment that changed everything.', 'anna-baylis' ),
			'coach_body'         => '',
			'coach_button_text'  => __( 'Book a Discovery Call', 'anna-baylis' ),
			'coach_button_url'   => function_exists( 'anna_get_discovery_call_url' ) ? anna_get_discovery_call_url() : ANNA_DISCOVERY_CALL_URL,
			'coach_image_id'     => 0,
			'work_eyebrow'       => __( 'How I work', 'anna-baylis' ),
			'work_heading'       => __( 'Different to most talk therapies.', 'anna-baylis' ),
			'work_body'          => '',
			'work_card_1_title'  => __( 'Bottom-up approach', 'anna-baylis' ),
			'work_card_1_body'   => '',
			'work_card_2_title'  => __( 'Trauma-informed and safe', 'anna-baylis' ),
			'work_card_2_body'   => '',
			'work_card_3_title'  => __( 'Whole person body, mind and emotions', 'anna-baylis' ),
			'work_card_3_body'   => '',
			'work_card_4_title'  => __( 'Lived experience', 'anna-baylis' ),
			'work_card_4_body'   => '',
			'people_eyebrow'     => __( 'What people say', 'anna-baylis' ),
			'people_heading'     => __( 'Committed to continual learning.', 'anna-baylis' ),
			'people_body'        => __( 'Over a decade of rigorous study across human movement, nutrition, coaching, somatic psychology, trauma-informed practice and inner world work. This is what I bring to every session.', 'anna-baylis' ),
			'people_items'       => array(
				array( 'logo_id' => 0, 'initials' => 'HM', 'title' => __( 'Bachelor of Applied Science — Human Movement', 'anna-baylis' ), 'org' => __( 'Deakin University', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'CP', 'title' => __( 'Credentialled Practitioner of Coaching', 'anna-baylis' ), 'org' => __( 'The Coaching Institute', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'NLP', 'title' => __( 'NLP Practitioner and Master Practitioner', 'anna-baylis' ), 'org' => __( 'Institute of Empowered Psychology', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'HY', 'title' => __( 'Hypnotherapy', 'anna-baylis' ), 'org' => __( 'Institute of Empowered Psychology', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'IFS', 'title' => __( 'Parts work — Internal Family Systems informed', 'anna-baylis' ), 'org' => __( 'Embodied Philosophy Western School', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'CI', 'title' => __( 'Masters — currently completing', 'anna-baylis' ), 'org' => __( 'Gabor Maté', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'CT', 'title' => __( '102 five-star Google reviews', 'anna-baylis' ), 'org' => __( 'Anodea Judith', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'NR', 'title' => __( 'Honours — Food Science and Nutrition', 'anna-baylis' ), 'org' => __( 'Deakin University', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'EI', 'title' => __( 'Emotional Intimacy Coach', 'anna-baylis' ), 'org' => __( 'The Coaching Institute', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'TL', 'title' => __( 'Timeline Therapy', 'anna-baylis' ), 'org' => __( 'Institute of Empowered Psychology', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'TC', 'title' => __( 'Trauma-informed coaching', 'anna-baylis' ), 'org' => __( 'The Centre for Healing', 'anna-baylis' ) ),
				array( 'logo_id' => 0, 'initials' => 'SP', 'title' => __( 'Personal trainer — 7+ years', 'anna-baylis' ), 'org' => __( 'NeuroAffective Touch Institute', 'anna-baylis' ) ),
			),
			'connect_eyebrow'     => __( 'I would love to connect', 'anna-baylis' ),
			'connect_heading'     => __( 'Book a discovery call and let’s see if this is the right fit.', 'anna-baylis' ),
			'connect_button_text' => __( 'Book a Discovery Call', 'anna-baylis' ),
			'connect_button_url'  => function_exists( 'anna_get_discovery_call_url' ) ? anna_get_discovery_call_url() : ANNA_DISCOVERY_CALL_URL,
		);

		$theme_defaults = $this->get_theme_mapped_about_defaults();
		if ( ! empty( $theme_defaults ) ) {
			$defaults = wp_parse_args( $theme_defaults, $defaults );
		}

		return $defaults;
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

		// Home page now stores all sections in one meta row (_anna_content_home_page).
		// Check that key first before falling back to the old per-section keys.
		if ( function_exists( 'anna_is_home_content_page' ) && anna_is_home_content_page( $post_id ) ) {
			$home_data = get_post_meta( $post_id, '_anna_content_home_page', true );
			if ( is_array( $home_data ) && isset( $home_data[ $section ] ) && is_array( $home_data[ $section ] ) ) {
				return $home_data[ $section ];
			}
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

		if ( empty( $stored ) ) {
			return $legacy;
		}

		foreach ( $legacy as $key => $default_value ) {
			if ( ! array_key_exists( $key, $stored ) ) {
				$stored[ $key ] = $default_value;
				continue;
			}

			if ( $this->is_blank_section_value( $stored[ $key ], $key ) ) {
				$stored[ $key ] = $default_value;
			}
		}

		return $stored;
	}

	/**
	 * Get legacy theme-option defaults for a section.
	 *
	 * @param string $section Section key.
	 * @return array
	 */
	private function get_legacy_section_defaults( $section ) {
		$options = self::get_theme_options_with_defaults();

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
					'eyebrow'        => $options['services_eyebrow'] ?? '',
					'heading'        => $options['services_heading'] ?? '',
					'description'    => $options['services_description'] ?? '',
					'cta_text'       => $options['services_cta_text'] ?? '',
					'cta_url'        => $options['services_cta_url'] ?? '',
					'bg_image_id'    => 0,
					'card_1_title'   => '1-1 Life Coaching',
					'card_1_excerpt' => 'Deep, personalised work using a bottom-up approach that accesses the subconscious through the body and the nervous system. We get to the root of what is actually running underneath and change it.',
					'card_1_link'    => 'Find out more',
					'card_1_url'     => '',
					'card_1_image_id'=> 0,
					'card_2_title'   => 'Oasis Community',
					'card_2_excerpt' => 'A womens wellness community for sustainable health and wellbeing. Ongoing live guidance, daily practices, guided movement, nutrition, meditation, breathwork and community connection. A space to come back to yourself week after week.',
					'card_2_link'    => 'Find out more',
					'card_2_url'     => '',
					'card_2_image_id'=> 0,
					'card_3_title'   => 'Speaking and Workshops',
					'card_3_excerpt' => 'Keynotes and interactive sessions for conferences, corporate events and womens gatherings. Drawing on Olympic experience, deep coaching expertise and lived transformation. Topics include stress and the nervous system, building resilience, the mind-body connection and more.',
					'card_3_link'    => 'Enquire about speaking',
					'card_3_url'     => '',
					'card_3_image_id'=> 0,
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

	/**
	 * Get theme options merged with registered defaults.
	 *
	 * This matches how the front end can still show content even when the
	 * options row is incomplete or has never been explicitly saved.
	 *
	 * @return array
	 */
	private static function get_theme_options_with_defaults() {
		$options = get_option( 'anna_theme_options', array() );
		$options = is_array( $options ) ? $options : array();

		if ( function_exists( 'anna_get_default_options' ) ) {
			$defaults = anna_get_default_options();
			if ( is_array( $defaults ) ) {
				return wp_parse_args( $options, $defaults );
			}
		}

		return $options;
	}

	/**
	 * Determine whether a saved section value should fall back to legacy data.
	 *
	 * @param mixed  $value Stored value.
	 * @param string $key   Section field key.
	 * @return bool
	 */
	private function is_blank_section_value( $value, $key ) {
		if ( is_string( $value ) ) {
			return '' === trim( $value );
		}

		if ( is_array( $value ) ) {
			return empty( $value );
		}

		if ( false === $value || null === $value ) {
			return true;
		}

		if ( '_id' === substr( (string) $key, -3 ) ) {
			return 0 === absint( $value );
		}

		return false;
	}

	/**
	 * Render fixed Coaching page content fields.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_coaching_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_coaching_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_coaching_page_meta( $post->ID, $data );
		?>
		<p><?php esc_html_e( 'These fields feed the fixed Coaching page design. Admins can edit copy and images only.', 'anna-baylis' ); ?></p>
		<p class="description" style="padding:0.6rem 0.9rem;background:#f0f6fc;border-left:3px solid #72aee6;border-radius:2px;font-size:12px;">
			<?php esc_html_e( 'Tip: type', 'anna-baylis' ); ?> <code>empty--</code> <?php esc_html_e( 'in any field to hide it on the frontend and suppress the default content.', 'anna-baylis' ); ?>
		</p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_coaching_page', 'hero_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['hero_eyebrow'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_coaching_page', 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'], 3 ); ?>
			<?php $this->render_textarea_field( 'anna_content_coaching_page', 'hero_description', __( 'Description', 'anna-baylis' ), $data['hero_description'], 3 ); ?>
			<?php $this->render_media_field( 'anna_content_coaching_page', 'hero_image_id', __( 'Hero Background Image', 'anna-baylis' ), $data['hero_image_id'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'hero_button_text', __( 'Button Text', 'anna-baylis' ), $data['hero_button_text'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'hero_button_url', __( 'Button URL', 'anna-baylis' ), $data['hero_button_url'] ); ?>
		</table>

		<h3><?php esc_html_e( 'What This Is', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_coaching_page', 'what_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['what_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'what_heading', __( 'Heading', 'anna-baylis' ), $data['what_heading'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_coaching_page', 'what_body', __( 'Body', 'anna-baylis' ), $data['what_body'], 8 ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'what_button_text', __( 'Button Text', 'anna-baylis' ), $data['what_button_text'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'what_button_url', __( 'Button URL', 'anna-baylis' ), $data['what_button_url'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'what_card_heading', __( 'Card Heading', 'anna-baylis' ), $data['what_card_heading'] ); ?>
			<?php $this->render_coaching_text_repeater_field( 'what_card_items', $data['what_card_items'] ?? array(), __( 'Card List', 'anna-baylis' ) ); ?>
		</table>

		<h3><?php esc_html_e( 'How I Work — Pillars', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_coaching_page', 'pillars_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['pillars_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'pillars_heading', __( 'Heading', 'anna-baylis' ), $data['pillars_heading'] ); ?>
			<?php $this->render_coaching_pillar_repeater_field( $data['pillar_items'] ?? array() ); ?>
		</table>

		<h3><?php esc_html_e( 'What We Work On', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_coaching_page', 'work_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['work_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'work_heading', __( 'Heading', 'anna-baylis' ), $data['work_heading'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'work_gains_heading', __( 'Gains Heading', 'anna-baylis' ), $data['work_gains_heading'] ); ?>
			<?php $this->render_coaching_text_repeater_field( 'work_topics_items', $data['work_topics_items'] ?? array(), __( 'Session Topics', 'anna-baylis' ) ); ?>
			<?php $this->render_coaching_text_repeater_field( 'work_gains_items', $data['work_gains_items'] ?? array(), __( 'What Clients Gain', 'anna-baylis' ), __( 'Use **word** to bold key terms.', 'anna-baylis' ) ); ?>
		</table>

		<h3><?php esc_html_e( 'What to Expect', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_coaching_page', 'expect_eyebrow', __( 'Eyebrow (optional)', 'anna-baylis' ), $data['expect_eyebrow'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'expect_heading_line1', __( 'Heading Line 1', 'anna-baylis' ), $data['expect_heading_line1'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'expect_heading_line2', __( 'Heading Line 2', 'anna-baylis' ), $data['expect_heading_line2'] ); ?>
			<?php $this->render_textarea_field( 'anna_content_coaching_page', 'expect_body', __( 'Body', 'anna-baylis' ), $data['expect_body'], 6 ); ?>
			<?php $this->render_textarea_field( 'anna_content_coaching_page', 'expect_quote', __( 'Quote', 'anna-baylis' ), $data['expect_quote'], 3 ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'expect_button_text', __( 'Button Text', 'anna-baylis' ), $data['expect_button_text'] ); ?>
			<?php $this->render_text_field( 'anna_content_coaching_page', 'expect_button_url', __( 'Button URL', 'anna-baylis' ), $data['expect_button_url'] ); ?>
			<?php $this->render_coaching_info_cards_repeater_field( $data['expect_info_cards'] ?? array() ); ?>
		</table>

		<h3><?php esc_html_e( 'FAQ', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( 'anna_content_coaching_page', 'faq_heading', __( 'Section Heading', 'anna-baylis' ), $data['faq_heading'] ); ?>
			<?php $this->render_coaching_faq_repeater_field( $data['faq_items'] ?? array() ); ?>
		</table>
		<?php
	}

	/**
	 * Render coaching text repeater in page editor.
	 *
	 * @param string $key   Field key.
	 * @param array  $items Items.
	 * @param string $label Label.
	 * @param string $desc  Description.
	 */
	private function render_coaching_text_repeater_field( $key, $items, $label, $desc = '' ) {
		$items = function_exists( 'anna_normalize_coaching_text_items' ) ? anna_normalize_coaching_text_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<?php if ( $desc ) : ?><p class="description"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
				<div class="anna-content-repeater" data-anna-content-repeater="<?php echo esc_attr( $key ); ?>">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="large-text" name="anna_content_coaching_page[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $index ); ?>][text]" value="<?php echo esc_attr( $item['text'] ?? '' ); ?>"></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Item', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="large-text" name="anna_content_coaching_page[<?php echo esc_attr( $key ); ?>][__INDEX__][text]" value=""></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render coaching pillar repeater.
	 *
	 * @param array $items Pillar rows.
	 */
	private function render_coaching_pillar_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_coaching_pillar_items' ) ? anna_normalize_coaching_pillar_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Pillar Cards', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="pillar-items">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><label><?php esc_html_e( 'Number', 'anna-baylis' ); ?> <input type="text" class="small-text" name="anna_content_coaching_page[pillar_items][<?php echo esc_attr( $index ); ?>][number]" value="<?php echo esc_attr( $item['number'] ?? '' ); ?>"></label></p>
								<p><label><?php esc_html_e( 'Title', 'anna-baylis' ); ?> <input type="text" class="large-text" name="anna_content_coaching_page[pillar_items][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>"></label></p>
								<p><label><?php esc_html_e( 'Body', 'anna-baylis' ); ?><br><textarea class="large-text" rows="3" name="anna_content_coaching_page[pillar_items][<?php echo esc_attr( $index ); ?>][body]"><?php echo esc_textarea( $item['body'] ?? '' ); ?></textarea></label></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Pillar', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><label><?php esc_html_e( 'Number', 'anna-baylis' ); ?> <input type="text" class="small-text" name="anna_content_coaching_page[pillar_items][__INDEX__][number]" value=""></label></p>
							<p><label><?php esc_html_e( 'Title', 'anna-baylis' ); ?> <input type="text" class="large-text" name="anna_content_coaching_page[pillar_items][__INDEX__][title]" value=""></label></p>
							<p><label><?php esc_html_e( 'Body', 'anna-baylis' ); ?><br><textarea class="large-text" rows="3" name="anna_content_coaching_page[pillar_items][__INDEX__][body]"></textarea></label></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render coaching info cards repeater.
	 *
	 * @param array $items Cards.
	 */
	private function render_coaching_info_cards_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_coaching_info_cards' ) ? anna_normalize_coaching_info_cards( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Info Cards', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="expect-info-cards">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><label><?php esc_html_e( 'Label', 'anna-baylis' ); ?><br><input type="text" class="regular-text" name="anna_content_coaching_page[expect_info_cards][<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>"></label></p>
								<p><label><?php esc_html_e( 'Body', 'anna-baylis' ); ?><br><textarea class="large-text" rows="3" name="anna_content_coaching_page[expect_info_cards][<?php echo esc_attr( $index ); ?>][body]"><?php echo esc_textarea( $item['body'] ?? '' ); ?></textarea></label></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Card', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><label><?php esc_html_e( 'Label', 'anna-baylis' ); ?><br><input type="text" class="regular-text" name="anna_content_coaching_page[expect_info_cards][__INDEX__][label]" value=""></label></p>
							<p><label><?php esc_html_e( 'Body', 'anna-baylis' ); ?><br><textarea class="large-text" rows="3" name="anna_content_coaching_page[expect_info_cards][__INDEX__][body]"></textarea></label></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render coaching FAQ repeater.
	 *
	 * @param array $items FAQ rows.
	 */
	private function render_coaching_faq_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'FAQ Items', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="coaching-faq">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><label><?php esc_html_e( 'Question', 'anna-baylis' ); ?><br><input type="text" class="large-text" name="anna_content_coaching_page[faq_items][<?php echo esc_attr( $index ); ?>][question]" value="<?php echo esc_attr( $item['question'] ?? '' ); ?>"></label></p>
								<p><label><?php esc_html_e( 'Answer', 'anna-baylis' ); ?><br><textarea class="large-text" rows="4" name="anna_content_coaching_page[faq_items][<?php echo esc_attr( $index ); ?>][answer]"><?php echo esc_textarea( $item['answer'] ?? '' ); ?></textarea></label></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Question', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><label><?php esc_html_e( 'Question', 'anna-baylis' ); ?><br><input type="text" class="large-text" name="anna_content_coaching_page[faq_items][__INDEX__][question]" value=""></label></p>
							<p><label><?php esc_html_e( 'Answer', 'anna-baylis' ); ?><br><textarea class="large-text" rows="4" name="anna_content_coaching_page[faq_items][__INDEX__][answer]"></textarea></label></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get fixed Coaching page content.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_coaching_page_content( $post_id ) {
		return $this->get_coaching_page_content_with_defaults( $post_id );
	}

	/**
	 * Get Coaching page content with defaults.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_coaching_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_coaching_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_coaching_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		foreach ( $defaults as $key => $default_value ) {
			if ( in_array( $key, array( 'what_card_items', 'pillar_items', 'work_topics_items', 'work_gains_items', 'expect_info_cards', 'faq_items' ), true ) ) {
				continue;
			}

			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $default_value, $key ) ) {
					$merged[ $key ] = $default_value;
				}
			}
		}

		$merged['what_card_items']   = $this->resolve_coaching_text_items( $stored, $defaults, 'what_card_items' );
		$merged['pillar_items']      = $this->resolve_coaching_pillar_items( $stored, $defaults );
		$merged['work_topics_items'] = $this->resolve_coaching_text_items( $stored, $defaults, 'work_topics_items' );
		$merged['work_gains_items']  = $this->resolve_coaching_text_items( $stored, $defaults, 'work_gains_items' );
		$merged['expect_info_cards'] = $this->resolve_coaching_info_cards( $stored, $defaults );
		$merged['faq_items']         = $this->resolve_coaching_faq_items( $stored, $defaults );

		if ( ! empty( $merged['hero_tags'] ) && is_string( $merged['hero_tags'] ) ) {
			$tags = preg_split( '/\r\n|\r|\n/', $merged['hero_tags'] );
			$merged['hero_tags'] = array_values( array_filter( array_map( 'trim', (array) $tags ) ) );
		}

		return $merged;
	}

	/**
	 * Resolve text repeater items.
	 *
	 * @param array  $stored   Saved meta.
	 * @param array  $defaults Defaults.
	 * @param string $key      Field key.
	 * @return array
	 */
	private function resolve_coaching_text_items( $stored, $defaults, $key ) {
		if ( isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) && ! empty( $stored[ $key ] ) ) {
			$items = function_exists( 'anna_normalize_coaching_text_items' ) ? anna_normalize_coaching_text_items( $stored[ $key ] ) : $stored[ $key ];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults[ $key ] ?? array();
		return function_exists( 'anna_normalize_coaching_text_items' ) ? anna_normalize_coaching_text_items( $default_items ) : $default_items;
	}

	/**
	 * Resolve info card items.
	 *
	 * @param array $stored   Saved meta.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private function resolve_coaching_info_cards( $stored, $defaults ) {
		if ( isset( $stored['expect_info_cards'] ) && is_array( $stored['expect_info_cards'] ) && ! empty( $stored['expect_info_cards'] ) ) {
			$items = function_exists( 'anna_normalize_coaching_info_cards' ) ? anna_normalize_coaching_info_cards( $stored['expect_info_cards'] ) : $stored['expect_info_cards'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['expect_info_cards'] ?? array();
		return function_exists( 'anna_normalize_coaching_info_cards' ) ? anna_normalize_coaching_info_cards( $default_items ) : $default_items;
	}

	/**
	 * Resolve pillar items.
	 *
	 * @param array $stored   Saved meta.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private function resolve_coaching_pillar_items( $stored, $defaults ) {
		if ( isset( $stored['pillar_items'] ) && is_array( $stored['pillar_items'] ) && ! empty( $stored['pillar_items'] ) ) {
			$items = function_exists( 'anna_normalize_coaching_pillar_items' ) ? anna_normalize_coaching_pillar_items( $stored['pillar_items'] ) : $stored['pillar_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['pillar_items'] ?? array();
		return function_exists( 'anna_normalize_coaching_pillar_items' ) ? anna_normalize_coaching_pillar_items( $default_items ) : $default_items;
	}

	/**
	 * Resolve FAQ items.
	 *
	 * @param array $stored   Saved meta.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private function resolve_coaching_faq_items( $stored, $defaults ) {
		if ( isset( $stored['faq_items'] ) && is_array( $stored['faq_items'] ) && ! empty( $stored['faq_items'] ) ) {
			$items = function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $stored['faq_items'] ) : $stored['faq_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['faq_items'] ?? array();
		return function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $default_items ) : $default_items;
	}

	/**
	 * Backfill blank Coaching page meta from defaults (one-time per page).
	 *
	 * @param int   $post_id Post ID.
	 * @param array $data    Resolved content.
	 */
	private function maybe_backfill_coaching_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! is_array( $data ) ) {
			return;
		}

		if ( get_post_meta( $post_id, '_anna_coaching_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_coaching_page', true );
		$stored  = is_array( $stored ) ? $stored : array();
		$changed = false;

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, array( 'work_topics_items', 'work_gains_items', 'expect_info_cards', 'faq_items' ), true ) ) {
				continue;
			}

			if ( ! array_key_exists( $key, $stored ) || $this->is_blank_section_value( $stored[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $value, $key ) ) {
					$stored[ $key ] = $value;
					$changed        = true;
				}
			}
		}

		$repeater_keys = array( 'what_card_items', 'pillar_items', 'work_topics_items', 'work_gains_items', 'expect_info_cards', 'faq_items' );
		foreach ( $repeater_keys as $repeater_key ) {
			$has_items = false;
			if ( isset( $stored[ $repeater_key ] ) && is_array( $stored[ $repeater_key ] ) ) {
				$has_items = ! empty( $stored[ $repeater_key ] );
			}
			if ( ! $has_items && ! empty( $data[ $repeater_key ] ) ) {
				$stored[ $repeater_key ] = $data[ $repeater_key ];
				$changed                 = true;
			}
		}

		if ( $changed ) {
			update_post_meta( $post_id, '_anna_content_coaching_page', $stored );
		}

		update_post_meta( $post_id, '_anna_coaching_meta_backfilled_v1', 1 );
	}

	/**
	 * Sanitize Coaching page content.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	private function sanitize_coaching_page_content( $input ) {
		$data = array(
			'hero_eyebrow'         => sanitize_text_field( $input['hero_eyebrow'] ?? '' ),
			'hero_heading'         => sanitize_textarea_field( $input['hero_heading'] ?? '' ),
			'hero_description'     => sanitize_textarea_field( $input['hero_description'] ?? '' ),
			'hero_image_id'        => absint( $input['hero_image_id'] ?? 0 ),
			'hero_button_text'     => sanitize_text_field( $input['hero_button_text'] ?? '' ),
			'hero_button_url'      => esc_url_raw( $input['hero_button_url'] ?? '' ),
			'what_eyebrow'         => sanitize_text_field( $input['what_eyebrow'] ?? '' ),
			'what_heading'         => sanitize_text_field( $input['what_heading'] ?? '' ),
			'what_body'            => sanitize_textarea_field( $input['what_body'] ?? '' ),
			'what_button_text'     => sanitize_text_field( $input['what_button_text'] ?? '' ),
			'what_button_url'      => esc_url_raw( $input['what_button_url'] ?? '' ),
			'what_card_heading'    => sanitize_text_field( $input['what_card_heading'] ?? '' ),
			'pillars_eyebrow'      => sanitize_text_field( $input['pillars_eyebrow'] ?? '' ),
			'pillars_heading'      => sanitize_text_field( $input['pillars_heading'] ?? '' ),
			'work_eyebrow'         => sanitize_text_field( $input['work_eyebrow'] ?? '' ),
			'work_heading'         => sanitize_text_field( $input['work_heading'] ?? '' ),
			'work_gains_heading'   => sanitize_text_field( $input['work_gains_heading'] ?? '' ),
			'expect_eyebrow'       => sanitize_text_field( $input['expect_eyebrow'] ?? '' ),
			'expect_heading_line1' => sanitize_text_field( $input['expect_heading_line1'] ?? '' ),
			'expect_heading_line2' => sanitize_text_field( $input['expect_heading_line2'] ?? '' ),
			'expect_body'          => sanitize_textarea_field( $input['expect_body'] ?? '' ),
			'expect_quote'         => sanitize_textarea_field( $input['expect_quote'] ?? '' ),
			'expect_button_text'   => sanitize_text_field( $input['expect_button_text'] ?? '' ),
			'expect_button_url'    => esc_url_raw( $input['expect_button_url'] ?? '' ),
			'faq_heading'          => sanitize_text_field( $input['faq_heading'] ?? '' ),
		);

		$data['what_card_items'] = function_exists( 'anna_normalize_coaching_text_items' )
			? anna_normalize_coaching_text_items( $input['what_card_items'] ?? array() )
			: array();
		$data['pillar_items'] = function_exists( 'anna_normalize_coaching_pillar_items' )
			? anna_normalize_coaching_pillar_items( $input['pillar_items'] ?? array() )
			: array();
		$data['work_topics_items'] = function_exists( 'anna_normalize_coaching_text_items' )
			? anna_normalize_coaching_text_items( $input['work_topics_items'] ?? array() )
			: array();
		$data['work_gains_items'] = function_exists( 'anna_normalize_coaching_text_items' )
			? anna_normalize_coaching_text_items( $input['work_gains_items'] ?? array() )
			: array();
		$data['expect_info_cards'] = function_exists( 'anna_normalize_coaching_info_cards' )
			? anna_normalize_coaching_info_cards( $input['expect_info_cards'] ?? array() )
			: array();
		$data['faq_items'] = function_exists( 'anna_normalize_coaching_faq_items' )
			? anna_normalize_coaching_faq_items( $input['faq_items'] ?? array() )
			: array();

		$defaults = $this->get_coaching_page_defaults();
		$merged   = wp_parse_args( $data, $defaults );

		foreach ( array( 'what_card_items', 'pillar_items', 'work_topics_items', 'work_gains_items', 'expect_info_cards', 'faq_items' ) as $repeater_key ) {
			if ( empty( $merged[ $repeater_key ] ) ) {
				$merged[ $repeater_key ] = $defaults[ $repeater_key ] ?? array();
			}
		}

		return $merged;
	}

	/**
	 * Map theme options to Coaching page meta keys.
	 *
	 * @return array<string, string>
	 */
	private function get_coaching_page_theme_option_map() {
		if ( ! function_exists( 'anna_get_coaching_page_option_map' ) ) {
			return array();
		}

		$map = anna_get_coaching_page_option_map();
		$out = array();
		foreach ( $map as $template_key => $option_key ) {
			if ( in_array( $template_key, array( 'work_topics_items', 'work_gains_items', 'expect_info_cards', 'faq_items', 'hero_tags' ), true ) ) {
				continue;
			}
			$out[ $template_key ] = $option_key;
		}

		return $out;
	}

	/**
	 * Pull Coaching defaults from theme options.
	 *
	 * @return array
	 */
	private function get_theme_mapped_coaching_defaults() {
		$theme = self::get_theme_options_with_defaults();
		$map   = $this->get_coaching_page_theme_option_map();
		$out   = array();

		foreach ( $map as $plugin_key => $theme_key ) {
			if ( ! isset( $theme[ $theme_key ] ) ) {
				continue;
			}

			$value = $theme[ $theme_key ];
			if ( str_ends_with( $plugin_key, '_image_id' ) ) {
				$out[ $plugin_key ] = absint( $value );
			} else {
				$out[ $plugin_key ] = $value;
			}
		}

		if ( ! empty( $theme['coaching_pg_hero_tags_text'] ) ) {
			$tags = preg_split( '/\r\n|\r|\n/', (string) $theme['coaching_pg_hero_tags_text'] );
			$out['hero_tags'] = array_values( array_filter( array_map( 'trim', (array) $tags ) ) );
		}

		if ( function_exists( 'anna_get_coaching_repeater_from_options' ) ) {
			$out['what_card_items']   = anna_get_coaching_repeater_from_options( 'coaching_pg_what_card_items' );
			$out['pillar_items']      = anna_get_coaching_repeater_from_options( 'coaching_pg_pillar_items' );
			$out['work_topics_items'] = anna_get_coaching_repeater_from_options( 'coaching_pg_work_topics_items' );
			$out['work_gains_items']  = anna_get_coaching_repeater_from_options( 'coaching_pg_work_gains_items' );
			$out['expect_info_cards'] = anna_get_coaching_repeater_from_options( 'coaching_pg_expect_info_cards' );
			$out['faq_items']         = anna_get_coaching_repeater_from_options( 'coaching_pg_faq_items' );
		}

		return $out;
	}

	/**
	 * Default Coaching page content.
	 *
	 * @return array
	 */
	private function get_coaching_page_defaults() {
		$defaults = array(
			'hero_eyebrow'         => __( 'Coaching with Anna', 'anna-baylis' ),
			'hero_heading'         => __( "One-to-one coaching\nfor lasting change.", 'anna-baylis' ),
			'hero_description'     => __( 'A trauma-informed, whole-person approach that works through the body and subconscious — not just talk.', 'anna-baylis' ),
			'hero_tags'            => array( __( 'Trauma-Informed', 'anna-baylis' ), __( 'IFS Trained', 'anna-baylis' ) ),
			'hero_image_id'        => 0,
			'hero_button_text'     => __( 'Book a Discovery Call', 'anna-baylis' ),
			'hero_button_url'      => function_exists( 'anna_get_discovery_call_url' ) ? anna_get_discovery_call_url() : ANNA_DISCOVERY_CALL_URL,
			'what_eyebrow'         => __( 'What this is', 'anna-baylis' ),
			'what_heading'         => __( 'Different to most talk therapies.', 'anna-baylis' ),
			'what_body'            => '',
			'what_button_text'     => __( 'Book a Discovery Call', 'anna-baylis' ),
			'what_button_url'      => function_exists( 'anna_get_discovery_call_url' ) ? anna_get_discovery_call_url() : ANNA_DISCOVERY_CALL_URL,
			'what_card_heading'    => __( 'Does this sound like you?', 'anna-baylis' ),
			'what_card_items'      => array(),
			'pillars_eyebrow'      => __( 'How I Work', 'anna-baylis' ),
			'pillars_heading'      => __( 'Three pillars of lasting change.', 'anna-baylis' ),
			'pillar_items'         => array(),
			'work_eyebrow'         => __( 'What We Work On', 'anna-baylis' ),
			'work_heading'         => __( 'In our sessions together we explore.', 'anna-baylis' ),
			'work_gains_heading'   => __( 'What clients gain', 'anna-baylis' ),
			'work_topics_items'    => array(),
			'work_gains_items'     => array(),
			'expect_eyebrow'       => __( 'Keep what clients gain!', 'anna-baylis' ),
			'expect_heading_line1' => __( 'What to expect', 'anna-baylis' ),
			'expect_heading_line2' => __( 'when we work together.', 'anna-baylis' ),
			'expect_body'          => '',
			'expect_quote'         => '',
			'expect_button_text'   => __( 'Book a Discovery Call', 'anna-baylis' ),
			'expect_button_url'    => function_exists( 'anna_get_discovery_call_url' ) ? anna_get_discovery_call_url() : ANNA_DISCOVERY_CALL_URL,
			'expect_info_cards'    => array(),
			'faq_heading'          => __( 'Everything you need to know.', 'anna-baylis' ),
			'faq_items'            => array(),
		);

		$theme_defaults = $this->get_theme_mapped_coaching_defaults();
		if ( ! empty( $theme_defaults ) ) {
			$defaults = wp_parse_args( $theme_defaults, $defaults );
		}

		return $defaults;
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

/**
 * Public helper for the fixed About page template.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function anna_content_get_about_page_content( $post_id ) {
	return Anna_Content_Manager::instance()->get_about_page_content( $post_id );
}

/**
 * Public helper for the fixed Coaching page template.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function anna_content_get_coaching_page_content( $post_id ) {
	return Anna_Content_Manager::instance()->get_coaching_page_content( $post_id );
}

/**
 * Public helper for the fixed Oasis page template.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function anna_content_get_oasis_page_content( $post_id ) {
	return Anna_Content_Manager::instance()->get_oasis_page_content( $post_id );
}

/**
 * Public helper for the fixed Speaking page template.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function anna_content_get_speaking_page_content( $post_id ) {
	return Anna_Content_Manager::instance()->get_speaking_page_content( $post_id );
}

/**
 * Public helper for the Mental Health Support page template.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function anna_content_get_mhs_page_content( $post_id ) {
	return Anna_Content_Manager::instance()->get_mhs_page_content( $post_id );
}

/**
 * Public helper for the MOVE page template.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function anna_content_get_move_page_content( $post_id ) {
	return Anna_Content_Manager::instance()->get_move_page_content( $post_id );
}

/**
 * Public helper for scaffolded theme pages (Anna Page Scaffolder).
 *
 * @param int    $post_id Post ID.
 * @param string $code    Page code prefix (e.g. contact).
 * @return array<string, mixed>
 */
function anna_content_get_scaffold_page_content( $post_id, $code ) {
	return Anna_Content_Manager::instance()->get_scaffold_page_content( $post_id, $code );
}
