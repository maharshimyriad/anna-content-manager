<?php
/**
 * Mental Health Support page content meta box, save, and defaults.
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Mhs_Page_Content {

	private function register_mhs_page_meta_box( $post ) {
		if ( 'mental-health-support' !== $post->post_name && 'page-mental-health-support.php' !== get_page_template_slug( $post->ID ) ) {
			return;
		}

		add_meta_box(
			'anna_content_mhs_page',
			__( 'Anna Mental Health Support Page Content', 'anna-baylis' ),
			array( $this, 'render_mhs_page_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	public function render_mhs_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );
		$data   = $this->get_mhs_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_mhs_page_meta( $post->ID, $data );
		$prefix = 'anna_content_mhs_page';
		?>
		<p><?php esc_html_e( 'Edit Mental Health Support page copy and images.', 'anna-baylis' ); ?></p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'hero_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['hero_eyebrow'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'] ?? '' ); ?>
			<?php $this->render_media_field( $prefix, 'hero_image_id', __( 'Background Image', 'anna-baylis' ), $data['hero_image_id'] ?? 0 ); ?>
		</table>

		<h3><?php esc_html_e( 'Opening — Your Story as an Athlete', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'opening_heading', __( 'Heading', 'anna-baylis' ), $data['opening_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'opening_body', __( 'Body', 'anna-baylis' ), $data['opening_body'] ?? '', 10 ); ?>
			<?php $this->render_media_field( $prefix, 'opening_image_id', __( 'Portrait Image', 'anna-baylis' ), $data['opening_image_id'] ?? 0 ); ?>
		</table>

		<h3><?php esc_html_e( 'Mental Programs', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'programs_heading', __( 'Heading', 'anna-baylis' ), $data['programs_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'programs_body', __( 'Body', 'anna-baylis' ), $data['programs_body'] ?? '', 10 ); ?>
		</table>

		<h3><?php esc_html_e( 'Inner Health', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'inner_heading', __( 'Heading', 'anna-baylis' ), $data['inner_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'inner_body', __( 'Body', 'anna-baylis' ), $data['inner_body'] ?? '', 12 ); ?>
			<?php $this->render_media_field( $prefix, 'inner_image_id', __( 'Image', 'anna-baylis' ), $data['inner_image_id'] ?? 0 ); ?>
		</table>

		<h3><?php esc_html_e( 'How I Work', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'work_heading', __( 'Heading', 'anna-baylis' ), $data['work_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'work_body', __( 'Body', 'anna-baylis' ), $data['work_body'] ?? '', 10 ); ?>
		</table>

		<h3><?php esc_html_e( 'My Daily Practice', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'practice_heading', __( 'Heading', 'anna-baylis' ), $data['practice_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'practice_body', __( 'Body', 'anna-baylis' ), $data['practice_body'] ?? '', 6 ); ?>
			<?php $this->render_text_field( $prefix, 'practice_link_text', __( 'Link Text', 'anna-baylis' ), $data['practice_link_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'practice_link_url', __( 'Link URL', 'anna-baylis' ), $data['practice_link_url'] ?? '' ); ?>
		</table>

		<h3><?php esc_html_e( 'Ready to Go Deeper (CTA)', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'ready_heading', __( 'Heading', 'anna-baylis' ), $data['ready_heading'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'ready_subheading', __( 'Subheading', 'anna-baylis' ), $data['ready_subheading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'ready_body', __( 'Body', 'anna-baylis' ), $data['ready_body'] ?? '', 3 ); ?>
			<?php $this->render_text_field( $prefix, 'ready_button_primary_text', __( 'Primary Button Text', 'anna-baylis' ), $data['ready_button_primary_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'ready_button_primary_url', __( 'Primary Button URL', 'anna-baylis' ), $data['ready_button_primary_url'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'ready_button_secondary_text', __( 'Secondary Button Text', 'anna-baylis' ), $data['ready_button_secondary_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'ready_button_secondary_url', __( 'Secondary Button URL', 'anna-baylis' ), $data['ready_button_secondary_url'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'ready_button_tertiary_text', __( 'Tertiary Button Text', 'anna-baylis' ), $data['ready_button_tertiary_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'ready_button_tertiary_url', __( 'Tertiary Button URL', 'anna-baylis' ), $data['ready_button_tertiary_url'] ?? '' ); ?>
		</table>
		<?php
	}

	private function save_mhs_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_mhs_page'] ) || ! is_array( $_POST['anna_content_mhs_page'] ) ) {
			return;
		}
		$input = wp_unslash( $_POST['anna_content_mhs_page'] );
		update_post_meta( $post_id, '_anna_content_mhs_page', $this->sanitize_mhs_page_content( $input ) );
	}

	public function get_mhs_page_content( $post_id ) {
		return $this->get_mhs_page_content_with_defaults( $post_id );
	}

	private function get_mhs_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_mhs_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_mhs_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		foreach ( $defaults as $key => $default_value ) {
			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $default_value, $key ) ) {
					$merged[ $key ] = $default_value;
				}
			}
		}

		return $merged;
	}

	private function maybe_backfill_mhs_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! is_array( $data ) || get_post_meta( $post_id, '_anna_mhs_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_mhs_page', true );
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
			update_post_meta( $post_id, '_anna_content_mhs_page', $stored );
		}
		update_post_meta( $post_id, '_anna_mhs_meta_backfilled_v1', 1 );
	}

	private function sanitize_mhs_page_content( $input ) {
		$scalar_keys = array(
			'hero_eyebrow', 'hero_heading',
			'opening_heading', 'programs_heading', 'inner_heading', 'work_heading',
			'practice_heading', 'practice_link_text',
			'ready_heading', 'ready_subheading',
			'ready_button_primary_text', 'ready_button_secondary_text', 'ready_button_tertiary_text',
		);
		$url_keys = array(
			'practice_link_url',
			'ready_button_primary_url', 'ready_button_secondary_url', 'ready_button_tertiary_url',
		);
		$textarea_keys = array(
			'opening_body', 'programs_body', 'inner_body', 'work_body', 'practice_body', 'ready_body',
		);
		$image_keys = array( 'hero_image_id', 'opening_image_id', 'inner_image_id' );

		$data = array();
		foreach ( $scalar_keys as $key ) {
			$data[ $key ] = sanitize_text_field( $input[ $key ] ?? '' );
		}
		foreach ( $url_keys as $key ) {
			$data[ $key ] = esc_url_raw( $input[ $key ] ?? '' );
		}
		foreach ( $textarea_keys as $key ) {
			$data[ $key ] = sanitize_textarea_field( $input[ $key ] ?? '' );
		}
		foreach ( $image_keys as $key ) {
			$data[ $key ] = absint( $input[ $key ] ?? 0 );
		}

		return wp_parse_args( $data, $this->get_mhs_page_defaults() );
	}

	private function get_theme_mapped_mhs_defaults() {
		if ( ! function_exists( 'anna_get_mhs_page_option_map' ) ) {
			return array();
		}
		$theme = self::get_theme_options_with_defaults();
		$map   = anna_get_mhs_page_option_map();
		$out   = array();

		foreach ( $map as $plugin_key => $theme_key ) {
			if ( ! isset( $theme[ $theme_key ] ) ) {
				continue;
			}
			$value = $theme[ $theme_key ];
			$out[ $plugin_key ] = str_ends_with( $plugin_key, '_image_id' ) ? absint( $value ) : $value;
		}

		return $out;
	}

	private function get_mhs_page_defaults() {
		$defaults = function_exists( 'anna_get_mhs_default_content' ) ? anna_get_mhs_default_content() : array();
		$theme    = $this->get_theme_mapped_mhs_defaults();
		return ! empty( $theme ) ? wp_parse_args( $theme, $defaults ) : $defaults;
	}
}
