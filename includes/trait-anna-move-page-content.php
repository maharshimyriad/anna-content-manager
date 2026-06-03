<?php
/**
 * MOVE page content meta box, save, and defaults.
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Move_Page_Content {

	private function register_move_page_meta_box( $post ) {
		if ( 'move' !== $post->post_name && 'page-move.php' !== get_page_template_slug( $post->ID ) ) {
			return;
		}

		add_meta_box(
			'anna_content_move_page',
			__( 'Anna MOVE Page Content', 'anna-baylis' ),
			array( $this, 'render_move_page_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	public function render_move_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );
		$data   = $this->get_move_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_move_page_meta( $post->ID, $data );
		$prefix = 'anna_content_move_page';
		?>
		<p><?php esc_html_e( 'Edit MOVE page copy, images, and repeatable sections.', 'anna-baylis' ); ?></p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'hero_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['hero_eyebrow'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'hero_heading', __( 'Heading', 'anna-baylis' ), $data['hero_heading'] ?? '' ); ?>
			<?php $this->render_media_field( $prefix, 'hero_image_id', __( 'Background Image', 'anna-baylis' ), $data['hero_image_id'] ?? 0 ); ?>
		</table>

		<h3><?php esc_html_e( 'Opening — The Evolution', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'evolution_heading', __( 'Heading', 'anna-baylis' ), $data['evolution_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'evolution_body', __( 'Body', 'anna-baylis' ), $data['evolution_body'] ?? '', 8 ); ?>
			<?php $this->render_textarea_field( $prefix, 'evolution_callout', __( 'OASIS Callout Line', 'anna-baylis' ), $data['evolution_callout'] ?? '', 2 ); ?>
			<?php $this->render_text_field( $prefix, 'evolution_gallery_heading', __( 'Gallery Heading', 'anna-baylis' ), $data['evolution_gallery_heading'] ?? '' ); ?>
			<?php $this->render_move_gallery_repeater_field( $data['evolution_gallery_items'] ?? array() ); ?>
		</table>

		<h3><?php esc_html_e( 'What M.O.V.E Was', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'was_heading', __( 'Heading', 'anna-baylis' ), $data['was_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'was_body', __( 'Body', 'anna-baylis' ), $data['was_body'] ?? '', 10 ); ?>
		</table>

		<h3><?php esc_html_e( 'What Women Said', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'said_heading', __( 'Heading', 'anna-baylis' ), $data['said_heading'] ?? '' ); ?>
			<?php $this->render_move_quote_repeater_field( 'said_items', $data['said_items'] ?? array(), __( 'Testimonials', 'anna-baylis' ) ); ?>
		</table>

		<h3><?php esc_html_e( 'Google Reviews', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'reviews_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['reviews_eyebrow'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'reviews_heading', __( 'Heading', 'anna-baylis' ), $data['reviews_heading'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'reviews_summary', __( 'Summary Line', 'anna-baylis' ), $data['reviews_summary'] ?? '' ); ?>
			<?php $this->render_move_review_repeater_field( $data['reviews_items'] ?? array() ); ?>
		</table>

		<h3><?php esc_html_e( 'Four Pillars', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'pillars_heading', __( 'Heading', 'anna-baylis' ), $data['pillars_heading'] ?? '' ); ?>
			<?php $this->render_move_pillar_repeater_field( $data['pillar_items'] ?? array() ); ?>
		</table>

		<h3><?php esc_html_e( 'M.O.V.E Has Evolved (CTA)', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'evolved_heading', __( 'Heading', 'anna-baylis' ), $data['evolved_heading'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'evolved_body', __( 'Body', 'anna-baylis' ), $data['evolved_body'] ?? '', 3 ); ?>
			<?php $this->render_text_field( $prefix, 'evolved_button_primary_text', __( 'Primary Button Text', 'anna-baylis' ), $data['evolved_button_primary_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'evolved_button_primary_url', __( 'Primary Button URL', 'anna-baylis' ), $data['evolved_button_primary_url'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'evolved_button_secondary_text', __( 'Secondary Button Text', 'anna-baylis' ), $data['evolved_button_secondary_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'evolved_button_secondary_url', __( 'Secondary Button URL', 'anna-baylis' ), $data['evolved_button_secondary_url'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'evolved_button_tertiary_text', __( 'Tertiary Button Text', 'anna-baylis' ), $data['evolved_button_tertiary_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'evolved_button_tertiary_url', __( 'Tertiary Button URL', 'anna-baylis' ), $data['evolved_button_tertiary_url'] ?? '' ); ?>
		</table>
		<?php
	}

	private function render_move_quote_repeater_field( $key, $items, $label ) {
		$items = function_exists( 'anna_normalize_move_quote_items' ) ? anna_normalize_move_quote_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="move-quotes">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><textarea class="large-text" rows="3" name="anna_content_move_page[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $index ); ?>][quote]"><?php echo esc_textarea( $item['quote'] ?? '' ); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Quote', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><textarea class="large-text" rows="3" name="anna_content_move_page[<?php echo esc_attr( $key ); ?>][__INDEX__][quote]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	private function render_move_gallery_media_input( $name, $value, $id_suffix ) {
		$id        = sanitize_key( 'move_gallery_' . $id_suffix );
		$preview   = $id . '_preview';
		$image_url = $value ? wp_get_attachment_image_url( absint( $value ), 'medium' ) : '';
		?>
		<p>
			<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<span id="<?php echo esc_attr( $preview ); ?>" style="display:block;margin-bottom:10px;">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:220px;height:auto;border-radius:10px;">
				<?php endif; ?>
			</span>
			<button type="button" class="button anna-content-media-select" data-target="<?php echo esc_attr( $id ); ?>" data-preview="<?php echo esc_attr( $preview ); ?>"><?php esc_html_e( 'Select Image', 'anna-baylis' ); ?></button>
			<button type="button" class="button anna-content-media-remove" data-target="<?php echo esc_attr( $id ); ?>" data-preview="<?php echo esc_attr( $preview ); ?>"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button>
		</p>
		<?php
	}

	private function render_move_gallery_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_move_gallery_items' ) ? anna_normalize_move_gallery_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Gallery Images', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="move-gallery">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<?php $image_id = absint( $item['image_id'] ?? 0 ); ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<?php $this->render_move_gallery_media_input( 'anna_content_move_page[evolution_gallery_items][' . $index . '][image_id]', $image_id, 'move-gallery-' . $index ); ?>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Image', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<?php $this->render_move_gallery_media_input( 'anna_content_move_page[evolution_gallery_items][__INDEX__][image_id]', 0, 'move-gallery-__INDEX__' ); ?>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	private function render_move_pillar_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_move_pillar_items' ) ? anna_normalize_move_pillar_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Pillars', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="move-pillars">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="large-text" name="anna_content_move_page[pillar_items][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>"></p>
								<p><textarea class="large-text" rows="5" name="anna_content_move_page[pillar_items][<?php echo esc_attr( $index ); ?>][body]"><?php echo esc_textarea( $item['body'] ?? '' ); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Pillar', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="large-text" name="anna_content_move_page[pillar_items][__INDEX__][title]" value=""></p>
							<p><textarea class="large-text" rows="5" name="anna_content_move_page[pillar_items][__INDEX__][body]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	private function render_move_review_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_move_review_items' ) ? anna_normalize_move_review_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Review Cards', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="move-reviews">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><textarea class="large-text" rows="4" name="anna_content_move_page[reviews_items][<?php echo esc_attr( $index ); ?>][quote]"><?php echo esc_textarea( $item['quote'] ?? '' ); ?></textarea></p>
								<p><input type="text" class="large-text" name="anna_content_move_page[reviews_items][<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $item['name'] ?? '' ); ?>"></p>
								<p><input type="text" class="large-text" name="anna_content_move_page[reviews_items][<?php echo esc_attr( $index ); ?>][role]" value="<?php echo esc_attr( $item['role'] ?? '' ); ?>"></p>
								<p><input type="number" min="1" max="5" class="small-text" name="anna_content_move_page[reviews_items][<?php echo esc_attr( $index ); ?>][rating]" value="<?php echo esc_attr( $item['rating'] ?? 5 ); ?>"></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Review', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><textarea class="large-text" rows="4" name="anna_content_move_page[reviews_items][__INDEX__][quote]"></textarea></p>
							<p><input type="text" class="large-text" name="anna_content_move_page[reviews_items][__INDEX__][name]" value=""></p>
							<p><input type="text" class="large-text" name="anna_content_move_page[reviews_items][__INDEX__][role]" value=""></p>
							<p><input type="number" min="1" max="5" class="small-text" name="anna_content_move_page[reviews_items][__INDEX__][rating]" value="5"></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	private function save_move_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_move_page'] ) || ! is_array( $_POST['anna_content_move_page'] ) ) {
			return;
		}
		$input = wp_unslash( $_POST['anna_content_move_page'] );
		update_post_meta( $post_id, '_anna_content_move_page', $this->sanitize_move_page_content( $input ) );
	}

	public function get_move_page_content( $post_id ) {
		return $this->get_move_page_content_with_defaults( $post_id );
	}

	private function get_move_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_move_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_move_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		$repeaters = array( 'evolution_gallery_items', 'said_items', 'pillar_items', 'reviews_items' );
		foreach ( $defaults as $key => $default_value ) {
			if ( in_array( $key, $repeaters, true ) ) {
				continue;
			}
			if ( ! array_key_exists( $key, $merged ) || $this->is_blank_section_value( $merged[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $default_value, $key ) ) {
					$merged[ $key ] = $default_value;
				}
			}
		}

		$merged['evolution_gallery_items'] = $this->resolve_move_gallery_items( $stored, $defaults );
		$merged['said_items']              = $this->resolve_move_quote_items( $stored, $defaults, 'said_items' );
		$merged['pillar_items']            = $this->resolve_move_pillar_items( $stored, $defaults );
		$merged['reviews_items']           = $this->resolve_move_review_items( $stored, $defaults );

		return $merged;
	}

	private function resolve_move_gallery_items( $stored, $defaults ) {
		if ( isset( $stored['evolution_gallery_items'] ) && is_array( $stored['evolution_gallery_items'] ) && ! empty( $stored['evolution_gallery_items'] ) ) {
			$items = function_exists( 'anna_normalize_move_gallery_items' ) ? anna_normalize_move_gallery_items( $stored['evolution_gallery_items'] ) : $stored['evolution_gallery_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}
		$default_items = $defaults['evolution_gallery_items'] ?? array();
		return function_exists( 'anna_normalize_move_gallery_items' ) ? anna_normalize_move_gallery_items( $default_items ) : $default_items;
	}

	private function resolve_move_quote_items( $stored, $defaults, $key ) {
		if ( isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) && ! empty( $stored[ $key ] ) ) {
			$items = function_exists( 'anna_normalize_move_quote_items' ) ? anna_normalize_move_quote_items( $stored[ $key ] ) : $stored[ $key ];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}
		$default_items = $defaults[ $key ] ?? array();
		return function_exists( 'anna_normalize_move_quote_items' ) ? anna_normalize_move_quote_items( $default_items ) : $default_items;
	}

	private function resolve_move_pillar_items( $stored, $defaults ) {
		if ( isset( $stored['pillar_items'] ) && is_array( $stored['pillar_items'] ) && ! empty( $stored['pillar_items'] ) ) {
			$items = function_exists( 'anna_normalize_move_pillar_items' ) ? anna_normalize_move_pillar_items( $stored['pillar_items'] ) : $stored['pillar_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}
		$default_items = $defaults['pillar_items'] ?? array();
		return function_exists( 'anna_normalize_move_pillar_items' ) ? anna_normalize_move_pillar_items( $default_items ) : $default_items;
	}

	private function resolve_move_review_items( $stored, $defaults ) {
		if ( isset( $stored['reviews_items'] ) && is_array( $stored['reviews_items'] ) && ! empty( $stored['reviews_items'] ) ) {
			$items = function_exists( 'anna_normalize_move_review_items' ) ? anna_normalize_move_review_items( $stored['reviews_items'] ) : $stored['reviews_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}
		$default_items = $defaults['reviews_items'] ?? array();
		return function_exists( 'anna_normalize_move_review_items' ) ? anna_normalize_move_review_items( $default_items ) : $default_items;
	}

	private function maybe_backfill_move_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! is_array( $data ) || get_post_meta( $post_id, '_anna_move_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored    = get_post_meta( $post_id, '_anna_content_move_page', true );
		$stored    = is_array( $stored ) ? $stored : array();
		$changed   = false;
		$repeaters = array( 'evolution_gallery_items', 'said_items', 'pillar_items', 'reviews_items' );

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $repeaters, true ) ) {
				continue;
			}
			if ( ! array_key_exists( $key, $stored ) || $this->is_blank_section_value( $stored[ $key ], $key ) ) {
				if ( ! $this->is_blank_section_value( $value, $key ) ) {
					$stored[ $key ] = $value;
					$changed        = true;
				}
			}
		}

		foreach ( $repeaters as $repeater_key ) {
			$has_items = isset( $stored[ $repeater_key ] ) && is_array( $stored[ $repeater_key ] ) && ! empty( $stored[ $repeater_key ] );
			if ( ! $has_items && ! empty( $data[ $repeater_key ] ) ) {
				$stored[ $repeater_key ] = $data[ $repeater_key ];
				$changed                 = true;
			}
		}

		if ( $changed ) {
			update_post_meta( $post_id, '_anna_content_move_page', $stored );
		}
		update_post_meta( $post_id, '_anna_move_meta_backfilled_v1', 1 );
	}

	private function sanitize_move_page_content( $input ) {
		$scalar_keys = array(
			'hero_eyebrow', 'hero_heading',
			'evolution_heading', 'evolution_gallery_heading',
			'was_heading', 'said_heading',
			'reviews_eyebrow', 'reviews_heading', 'reviews_summary',
			'pillars_heading',
			'evolved_heading',
			'evolved_button_primary_text', 'evolved_button_secondary_text', 'evolved_button_tertiary_text',
		);
		$url_keys = array(
			'evolved_button_primary_url', 'evolved_button_secondary_url', 'evolved_button_tertiary_url',
		);
		$textarea_keys = array(
			'evolution_body', 'evolution_callout', 'was_body', 'evolved_body',
		);
		$image_keys = array( 'hero_image_id' );

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

		$data['evolution_gallery_items'] = function_exists( 'anna_normalize_move_gallery_items' ) ? anna_normalize_move_gallery_items( $input['evolution_gallery_items'] ?? array() ) : array();
		$data['said_items']              = function_exists( 'anna_normalize_move_quote_items' ) ? anna_normalize_move_quote_items( $input['said_items'] ?? array() ) : array();
		$data['pillar_items']            = function_exists( 'anna_normalize_move_pillar_items' ) ? anna_normalize_move_pillar_items( $input['pillar_items'] ?? array() ) : array();
		$data['reviews_items']           = function_exists( 'anna_normalize_move_review_items' ) ? anna_normalize_move_review_items( $input['reviews_items'] ?? array() ) : array();

		return wp_parse_args( $data, $this->get_move_page_defaults() );
	}

	private function get_theme_mapped_move_defaults() {
		if ( ! function_exists( 'anna_get_move_page_option_map' ) ) {
			return array();
		}
		$theme = self::get_theme_options_with_defaults();
		$map   = anna_get_move_page_option_map();
		$out   = array();
		$repeaters = array( 'evolution_gallery_items', 'said_items', 'pillar_items', 'reviews_items' );

		foreach ( $map as $plugin_key => $theme_key ) {
			if ( in_array( $plugin_key, $repeaters, true ) ) {
				continue;
			}
			if ( ! isset( $theme[ $theme_key ] ) ) {
				continue;
			}
			$value = $theme[ $theme_key ];
			$out[ $plugin_key ] = str_ends_with( $plugin_key, '_image_id' ) ? absint( $value ) : $value;
		}

		if ( function_exists( 'anna_get_move_repeater_from_options' ) ) {
			$out['evolution_gallery_items'] = anna_get_move_repeater_from_options( 'evolution_gallery_items' );
			$out['said_items']              = anna_get_move_repeater_from_options( 'said_items' );
			$out['pillar_items']            = anna_get_move_repeater_from_options( 'pillar_items' );
			$out['reviews_items']           = anna_get_move_repeater_from_options( 'reviews_items' );
		}

		return $out;
	}

	private function get_move_page_defaults() {
		$defaults = function_exists( 'anna_get_move_default_content' ) ? anna_get_move_default_content() : array();
		$theme    = $this->get_theme_mapped_move_defaults();
		return ! empty( $theme ) ? wp_parse_args( $theme, $defaults ) : $defaults;
	}
}
