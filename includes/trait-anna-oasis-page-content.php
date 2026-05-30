<?php
/**
 * Oasis page content meta box, save, and defaults.
 *
 * @package Anna_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Anna_Oasis_Page_Content {

	/**
	 * Register Oasis page meta box when applicable.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function register_oasis_page_meta_box( $post ) {
		$is_oasis_page = ( 'oasis' === $post->post_name || 'page-oasis.php' === get_page_template_slug( $post->ID ) );

		if ( ! $is_oasis_page ) {
			return;
		}

		add_meta_box(
			'anna_content_oasis_page',
			__( 'Anna Oasis Page Content', 'anna-baylis' ),
			array( $this, 'render_oasis_page_meta_box' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Render Oasis page editor fields.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_oasis_page_meta_box( $post ) {
		wp_nonce_field( 'anna_content_save_page', 'anna_content_page_nonce' );

		$data = $this->get_oasis_page_content_with_defaults( $post->ID );
		$this->maybe_backfill_oasis_page_meta( $post->ID, $data );
		$prefix = 'anna_content_oasis_page';
		?>
		<p><?php esc_html_e( 'These fields feed the fixed Oasis page design. Edit copy, images, and repeatable lists.', 'anna-baylis' ); ?></p>

		<h3><?php esc_html_e( 'Hero', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'hero_breadcrumb', __( 'Breadcrumb', 'anna-baylis' ), $data['hero_breadcrumb'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'hero_heading', __( 'Title', 'anna-baylis' ), $data['hero_heading'] ); ?>
			<?php $this->render_text_field( $prefix, 'hero_subheading', __( 'Subheading', 'anna-baylis' ), $data['hero_subheading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'hero_body', __( 'Body', 'anna-baylis' ), $data['hero_body'], 8 ); ?>
			<?php $this->render_media_field( $prefix, 'hero_image_id', __( 'Background Image', 'anna-baylis' ), $data['hero_image_id'] ); ?>
			<?php $this->render_text_field( $prefix, 'hero_button_text', __( 'Button Text', 'anna-baylis' ), $data['hero_button_text'] ); ?>
			<?php $this->render_text_field( $prefix, 'hero_button_url', __( 'Button URL', 'anna-baylis' ), $data['hero_button_url'] ); ?>
		</table>

		<h3><?php esc_html_e( 'What Oasis Is', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'what_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['what_eyebrow'] ); ?>
			<?php $this->render_text_field( $prefix, 'what_heading', __( 'Heading', 'anna-baylis' ), $data['what_heading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'what_body', __( 'Body', 'anna-baylis' ), $data['what_body'], 5 ); ?>
			<?php $this->render_text_field( $prefix, 'what_footer_line', __( 'Footer Link Text', 'anna-baylis' ), $data['what_footer_line'] ); ?>
			<?php $this->render_text_field( $prefix, 'what_footer_url', __( 'Footer Link URL', 'anna-baylis' ), $data['what_footer_url'] ?? '' ); ?>
		</table>

		<h3><?php esc_html_e( 'Where Oasis Began', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'begun_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['begun_eyebrow'] ); ?>
			<?php $this->render_text_field( $prefix, 'begun_heading', __( 'Heading', 'anna-baylis' ), $data['begun_heading'] ); ?>
			<?php $this->render_text_field( $prefix, 'begun_subheading', __( 'Credentials Line', 'anna-baylis' ), $data['begun_subheading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'begun_body', __( 'Body', 'anna-baylis' ), $data['begun_body'], 10 ); ?>
			<?php $this->render_text_field( $prefix, 'begun_quote', __( 'Quote', 'anna-baylis' ), $data['begun_quote'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'begun_closing', __( 'Closing', 'anna-baylis' ), $data['begun_closing'], 4 ); ?>
			<?php $this->render_media_field( $prefix, 'begun_image_id', __( 'Portrait', 'anna-baylis' ), $data['begun_image_id'] ); ?>
			<?php $this->render_text_field( $prefix, 'begun_callout_label', __( 'Callout Label', 'anna-baylis' ), $data['begun_callout_label'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'begun_callout_body', __( 'Callout Body', 'anna-baylis' ), $data['begun_callout_body'], 3 ); ?>
			<?php $this->render_text_field( $prefix, 'begun_link_text', __( 'Story Link Text', 'anna-baylis' ), $data['begun_link_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'begun_link_url', __( 'Story Link URL', 'anna-baylis' ), $data['begun_link_url'] ?? '' ); ?>
		</table>

		<h3><?php esc_html_e( 'Inside Oasis', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'inside_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['inside_eyebrow'] ); ?>
			<?php $this->render_text_field( $prefix, 'inside_heading', __( 'Heading', 'anna-baylis' ), $data['inside_heading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'inside_body', __( 'Body', 'anna-baylis' ), $data['inside_body'], 6 ); ?>
			<?php $this->render_text_field( $prefix, 'inside_highlight', __( 'Highlight', 'anna-baylis' ), $data['inside_highlight'] ); ?>
			<?php $this->render_text_field( $prefix, 'inside_pills_intro', __( 'Pills Intro', 'anna-baylis' ), $data['inside_pills_intro'] ); ?>
			<?php $this->render_oasis_text_repeater_field( 'inside_pill_items', $data['inside_pill_items'] ?? array(), __( 'Value Pills', 'anna-baylis' ) ); ?>
			<?php $this->render_oasis_schedule_repeater_field( $data['inside_schedule_items'] ?? array() ); ?>
		</table>

		<h3><?php esc_html_e( 'How It Works', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'how_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['how_eyebrow'] ); ?>
			<?php $this->render_text_field( $prefix, 'how_heading', __( 'Heading', 'anna-baylis' ), $data['how_heading'] ); ?>
			<?php $this->render_textarea_field( $prefix, 'how_intro', __( 'Intro', 'anna-baylis' ), $data['how_intro'], 3 ); ?>
			<?php $this->render_oasis_how_repeater_field( $data['how_card_items'] ?? array() ); ?>
			<?php $this->render_text_field( $prefix, 'how_footer', __( 'Footer', 'anna-baylis' ), $data['how_footer'] ); ?>
		</table>

		<h3><?php esc_html_e( 'Choose Your Experience', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'choose_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['choose_eyebrow'] ); ?>
			<?php $this->render_text_field( $prefix, 'choose_heading', __( 'Heading', 'anna-baylis' ), $data['choose_heading'] ); ?>
			<?php $this->render_text_field( $prefix, 'choose_intro', __( 'Intro', 'anna-baylis' ), $data['choose_intro'] ); ?>
			<?php $this->render_oasis_plan_repeater_field( $data['choose_plan_items'] ?? array() ); ?>
			<?php $this->render_textarea_field( $prefix, 'choose_footer', __( 'Footer Note', 'anna-baylis' ), $data['choose_footer'], 3 ); ?>
		</table>

		<h3><?php esc_html_e( 'Is Oasis Right For You', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'ready_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['ready_eyebrow'] ); ?>
			<?php $this->render_text_field( $prefix, 'ready_heading', __( 'Heading', 'anna-baylis' ), $data['ready_heading'] ); ?>
			<?php $this->render_oasis_text_repeater_field( 'ready_items', $data['ready_items'] ?? array(), __( 'Cards', 'anna-baylis' ) ); ?>
		</table>

		<h3><?php esc_html_e( 'Waitlist', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'waitlist_eyebrow', __( 'Eyebrow', 'anna-baylis' ), $data['waitlist_eyebrow'] ?? '' ); ?>
			<?php $this->render_textarea_field( $prefix, 'waitlist_heading', __( 'Heading', 'anna-baylis' ), $data['waitlist_heading'] ?? '', 3 ); ?>
			<?php $this->render_text_field( $prefix, 'waitlist_button_text', __( 'Button Text', 'anna-baylis' ), $data['waitlist_button_text'] ?? '' ); ?>
			<?php $this->render_text_field( $prefix, 'waitlist_button_url', __( 'Button URL', 'anna-baylis' ), $data['waitlist_button_url'] ?? '' ); ?>
		</table>

		<h3><?php esc_html_e( 'FAQ', 'anna-baylis' ); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field( $prefix, 'faq_heading', __( 'Section Heading', 'anna-baylis' ), $data['faq_heading'] ?? '' ); ?>
			<?php $this->render_oasis_faq_repeater_field( $data['faq_items'] ?? array() ); ?>
		</table>
		<?php
	}

	/**
	 * @param array $items FAQ rows.
	 */
	private function render_oasis_faq_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'FAQ Items', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="oasis-faq">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="large-text" name="anna_content_oasis_page[faq_items][<?php echo esc_attr( $index ); ?>][question]" value="<?php echo esc_attr( $item['question'] ?? '' ); ?>"></p>
								<p><textarea class="large-text" rows="3" name="anna_content_oasis_page[faq_items][<?php echo esc_attr( $index ); ?>][answer]"><?php echo esc_textarea( $item['answer'] ?? '' ); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add FAQ', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="large-text" name="anna_content_oasis_page[faq_items][__INDEX__][question]" value=""></p>
							<p><textarea class="large-text" rows="3" name="anna_content_oasis_page[faq_items][__INDEX__][answer]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param string $key   Field key.
	 * @param array  $items Items.
	 * @param string $label Label.
	 */
	private function render_oasis_text_repeater_field( $key, $items, $label ) {
		$items = function_exists( 'anna_normalize_oasis_text_items' ) ? anna_normalize_oasis_text_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="<?php echo esc_attr( $key ); ?>">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="large-text" name="anna_content_oasis_page[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $index ); ?>][text]" value="<?php echo esc_attr( $item['text'] ?? '' ); ?>"></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="large-text" name="anna_content_oasis_page[<?php echo esc_attr( $key ); ?>][__INDEX__][text]" value=""></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param array $items Schedule rows.
	 */
	private function render_oasis_schedule_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_oasis_schedule_items' ) ? anna_normalize_oasis_schedule_items( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Weekly Rhythm', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="oasis-schedule">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="large-text" name="anna_content_oasis_page[inside_schedule_items][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>"></p>
								<p><textarea class="large-text" rows="3" name="anna_content_oasis_page[inside_schedule_items][<?php echo esc_attr( $index ); ?>][body]"><?php echo esc_textarea( $item['body'] ?? '' ); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Day', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="large-text" name="anna_content_oasis_page[inside_schedule_items][__INDEX__][title]" value=""></p>
							<p><textarea class="large-text" rows="3" name="anna_content_oasis_page[inside_schedule_items][__INDEX__][body]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param array $items How cards.
	 */
	private function render_oasis_how_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_oasis_how_cards' ) ? anna_normalize_oasis_how_cards( $items ) : (array) $items;
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Rhythm Cards', 'anna-baylis' ); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="oasis-how">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ( $items as $index => $item ) : ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><label><?php esc_html_e( 'Icon', 'anna-baylis' ); ?> <input type="text" class="small-text" name="anna_content_oasis_page[how_card_items][<?php echo esc_attr( $index ); ?>][icon]" value="<?php echo esc_attr( $item['icon'] ?? 'roots' ); ?>"></label></p>
								<p><input type="text" class="large-text" name="anna_content_oasis_page[how_card_items][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>"></p>
								<p><textarea class="large-text" rows="3" name="anna_content_oasis_page[how_card_items][<?php echo esc_attr( $index ); ?>][body]"><?php echo esc_textarea( $item['body'] ?? '' ); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e( 'Add Card', 'anna-baylis' ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="small-text" name="anna_content_oasis_page[how_card_items][__INDEX__][icon]" value="roots"></p>
							<p><input type="text" class="large-text" name="anna_content_oasis_page[how_card_items][__INDEX__][title]" value=""></p>
							<p><textarea class="large-text" rows="3" name="anna_content_oasis_page[how_card_items][__INDEX__][body]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e( 'Remove', 'anna-baylis' ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param array $items Plan rows.
	 */
	private function render_oasis_plan_repeater_field( $items ) {
		$items = function_exists( 'anna_normalize_oasis_plan_items' ) ? anna_normalize_oasis_plan_items( $items ) : (array) $items;
		if ( count( $items ) < 2 && function_exists( 'anna_get_oasis_default_content' ) ) {
			$defaults = anna_get_oasis_default_content()['choose_plan_items'] ?? array();
			$items    = array_merge( $items, array_slice( $defaults, count( $items ), 2 - count( $items ) ) );
		}
		?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Pricing Plans', 'anna-baylis' ); ?></th>
			<td>
				<?php foreach ( $items as $index => $plan ) : ?>
					<div style="border:1px solid #ccd0d4;padding:12px;margin-bottom:12px;">
						<p><strong><?php echo esc_html( sprintf( __( 'Plan %d', 'anna-baylis' ), $index + 1 ) ); ?></strong></p>
						<p><input type="text" class="regular-text" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $plan['title'] ?? '' ); ?>"></p>
						<p>
							<input type="text" class="small-text" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][price]" value="<?php echo esc_attr( $plan['price'] ?? '' ); ?>">
							<input type="text" class="regular-text" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][price_suffix]" value="<?php echo esc_attr( $plan['price_suffix'] ?? '' ); ?>">
						</p>
						<p><input type="text" class="large-text" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][annual]" value="<?php echo esc_attr( $plan['annual'] ?? '' ); ?>"></p>
						<p><input type="text" class="large-text" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][founding]" value="<?php echo esc_attr( $plan['founding'] ?? '' ); ?>"></p>
						<p><input type="text" class="regular-text" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][badge]" value="<?php echo esc_attr( $plan['badge'] ?? '' ); ?>"></p>
						<p><label><input type="checkbox" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][featured]" value="1" <?php checked( ! empty( $plan['featured'] ) ); ?>> <?php esc_html_e( 'Featured', 'anna-baylis' ); ?></label></p>
						<p><textarea class="large-text" rows="6" name="anna_content_oasis_page[choose_plan_items][<?php echo esc_attr( $index ); ?>][features_text]" placeholder="<?php esc_attr_e( 'One feature per line', 'anna-baylis' ); ?>"><?php
							if ( ! empty( $plan['features'] ) && is_array( $plan['features'] ) ) {
								$lines = array();
								foreach ( $plan['features'] as $feature ) {
									$lines[] = is_array( $feature ) ? (string) ( $feature['text'] ?? '' ) : (string) $feature;
								}
								echo esc_textarea( implode( "\n", $lines ) );
							}
						?></textarea></p>
					</div>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save Oasis meta from POST.
	 *
	 * @param int $post_id Post ID.
	 */
	private function save_oasis_page_content( $post_id ) {
		if ( ! isset( $_POST['anna_content_oasis_page'] ) || ! is_array( $_POST['anna_content_oasis_page'] ) ) {
			return;
		}

		$input = wp_unslash( $_POST['anna_content_oasis_page'] );
		update_post_meta( $post_id, '_anna_content_oasis_page', $this->sanitize_oasis_page_content( $input ) );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public function get_oasis_page_content( $post_id ) {
		return $this->get_oasis_page_content_with_defaults( $post_id );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_oasis_page_content_with_defaults( $post_id ) {
		$stored   = get_post_meta( absint( $post_id ), '_anna_content_oasis_page', true );
		$stored   = is_array( $stored ) ? $stored : array();
		$defaults = $this->get_oasis_page_defaults();
		$merged   = wp_parse_args( $stored, $defaults );

		$repeaters = array( 'inside_pill_items', 'inside_schedule_items', 'how_card_items', 'choose_plan_items', 'ready_items', 'faq_items' );
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

		$merged['inside_pill_items']     = $this->resolve_oasis_text_items( $stored, $defaults, 'inside_pill_items' );
		$merged['inside_schedule_items'] = $this->resolve_oasis_schedule_items( $stored, $defaults );
		$merged['how_card_items']        = $this->resolve_oasis_how_cards( $stored, $defaults );
		$merged['choose_plan_items']     = $this->resolve_oasis_plan_items( $stored, $defaults );
		$merged['ready_items']           = $this->resolve_oasis_text_items( $stored, $defaults, 'ready_items' );
		$merged['faq_items']             = $this->resolve_oasis_faq_items( $stored, $defaults );

		return $merged;
	}

	/**
	 * @param array  $stored   Stored meta.
	 * @param array  $defaults Defaults.
	 * @param string $key      Field key.
	 * @return array
	 */
	private function resolve_oasis_text_items( $stored, $defaults, $key ) {
		if ( isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) && ! empty( $stored[ $key ] ) ) {
			$items = function_exists( 'anna_normalize_oasis_text_items' ) ? anna_normalize_oasis_text_items( $stored[ $key ] ) : $stored[ $key ];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults[ $key ] ?? array();
		return function_exists( 'anna_normalize_oasis_text_items' ) ? anna_normalize_oasis_text_items( $default_items ) : $default_items;
	}

	/**
	 * @param array $stored   Stored meta.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private function resolve_oasis_schedule_items( $stored, $defaults ) {
		if ( isset( $stored['inside_schedule_items'] ) && is_array( $stored['inside_schedule_items'] ) && ! empty( $stored['inside_schedule_items'] ) ) {
			$items = function_exists( 'anna_normalize_oasis_schedule_items' ) ? anna_normalize_oasis_schedule_items( $stored['inside_schedule_items'] ) : $stored['inside_schedule_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['inside_schedule_items'] ?? array();
		return function_exists( 'anna_normalize_oasis_schedule_items' ) ? anna_normalize_oasis_schedule_items( $default_items ) : $default_items;
	}

	/**
	 * @param array $stored   Stored meta.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private function resolve_oasis_how_cards( $stored, $defaults ) {
		if ( isset( $stored['how_card_items'] ) && is_array( $stored['how_card_items'] ) && ! empty( $stored['how_card_items'] ) ) {
			$items = function_exists( 'anna_normalize_oasis_how_cards' ) ? anna_normalize_oasis_how_cards( $stored['how_card_items'] ) : $stored['how_card_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['how_card_items'] ?? array();
		return function_exists( 'anna_normalize_oasis_how_cards' ) ? anna_normalize_oasis_how_cards( $default_items ) : $default_items;
	}

	/**
	 * @param array $stored   Stored meta.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private function resolve_oasis_faq_items( $stored, $defaults ) {
		if ( isset( $stored['faq_items'] ) && is_array( $stored['faq_items'] ) && ! empty( $stored['faq_items'] ) ) {
			$items = function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $stored['faq_items'] ) : $stored['faq_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['faq_items'] ?? array();
		return function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $default_items ) : $default_items;
	}

	private function resolve_oasis_plan_items( $stored, $defaults ) {
		if ( isset( $stored['choose_plan_items'] ) && is_array( $stored['choose_plan_items'] ) && ! empty( $stored['choose_plan_items'] ) ) {
			$items = function_exists( 'anna_normalize_oasis_plan_items' ) ? anna_normalize_oasis_plan_items( $stored['choose_plan_items'] ) : $stored['choose_plan_items'];
			if ( ! empty( $items ) ) {
				return $items;
			}
		}

		$default_items = $defaults['choose_plan_items'] ?? array();
		return function_exists( 'anna_normalize_oasis_plan_items' ) ? anna_normalize_oasis_plan_items( $default_items ) : $default_items;
	}

	/**
	 * @param int   $post_id Post ID.
	 * @param array $data    Resolved content.
	 */
	private function maybe_backfill_oasis_page_meta( $post_id, $data ) {
		$post_id = absint( $post_id );
		if ( ! $post_id || ! is_array( $data ) ) {
			return;
		}

		if ( get_post_meta( $post_id, '_anna_oasis_meta_backfilled_v1', true ) ) {
			return;
		}

		$stored  = get_post_meta( $post_id, '_anna_content_oasis_page', true );
		$stored  = is_array( $stored ) ? $stored : array();
		$changed = false;

		$repeaters = array( 'inside_pill_items', 'inside_schedule_items', 'how_card_items', 'choose_plan_items', 'ready_items', 'faq_items' );

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
			update_post_meta( $post_id, '_anna_content_oasis_page', $stored );
		}

		update_post_meta( $post_id, '_anna_oasis_meta_backfilled_v1', 1 );
	}

	/**
	 * @param array $input Raw POST.
	 * @return array
	 */
	private function sanitize_oasis_page_content( $input ) {
		$data = array(
			'hero_eyebrow'        => sanitize_text_field( $input['hero_eyebrow'] ?? '' ),
			'hero_heading'        => sanitize_text_field( $input['hero_heading'] ?? '' ),
			'hero_subheading'     => sanitize_text_field( $input['hero_subheading'] ?? '' ),
			'hero_body'           => sanitize_textarea_field( $input['hero_body'] ?? '' ),
			'hero_image_id'       => absint( $input['hero_image_id'] ?? 0 ),
			'hero_button_text'    => sanitize_text_field( $input['hero_button_text'] ?? '' ),
			'hero_button_url'     => esc_url_raw( $input['hero_button_url'] ?? '' ),
			'what_eyebrow'        => sanitize_text_field( $input['what_eyebrow'] ?? '' ),
			'what_heading'        => sanitize_text_field( $input['what_heading'] ?? '' ),
			'what_body'           => sanitize_textarea_field( $input['what_body'] ?? '' ),
			'what_footer_line'    => sanitize_text_field( $input['what_footer_line'] ?? '' ),
			'begun_eyebrow'       => sanitize_text_field( $input['begun_eyebrow'] ?? '' ),
			'begun_heading'       => sanitize_text_field( $input['begun_heading'] ?? '' ),
			'begun_subheading'    => sanitize_text_field( $input['begun_subheading'] ?? '' ),
			'begun_body'          => sanitize_textarea_field( $input['begun_body'] ?? '' ),
			'begun_quote'         => sanitize_text_field( $input['begun_quote'] ?? '' ),
			'begun_closing'       => sanitize_textarea_field( $input['begun_closing'] ?? '' ),
			'begun_image_id'      => absint( $input['begun_image_id'] ?? 0 ),
			'begun_callout_label' => sanitize_text_field( $input['begun_callout_label'] ?? '' ),
			'begun_callout_body'  => sanitize_textarea_field( $input['begun_callout_body'] ?? '' ),
			'inside_eyebrow'      => sanitize_text_field( $input['inside_eyebrow'] ?? '' ),
			'inside_heading'      => sanitize_text_field( $input['inside_heading'] ?? '' ),
			'inside_body'         => sanitize_textarea_field( $input['inside_body'] ?? '' ),
			'inside_highlight'    => sanitize_text_field( $input['inside_highlight'] ?? '' ),
			'inside_pills_intro'  => sanitize_text_field( $input['inside_pills_intro'] ?? '' ),
			'how_eyebrow'         => sanitize_text_field( $input['how_eyebrow'] ?? '' ),
			'how_heading'         => sanitize_text_field( $input['how_heading'] ?? '' ),
			'how_intro'           => sanitize_textarea_field( $input['how_intro'] ?? '' ),
			'how_footer'          => sanitize_text_field( $input['how_footer'] ?? '' ),
			'choose_eyebrow'      => sanitize_text_field( $input['choose_eyebrow'] ?? '' ),
			'choose_heading'      => sanitize_text_field( $input['choose_heading'] ?? '' ),
			'choose_intro'        => sanitize_text_field( $input['choose_intro'] ?? '' ),
			'choose_footer'       => sanitize_textarea_field( $input['choose_footer'] ?? '' ),
			'ready_eyebrow'       => sanitize_text_field( $input['ready_eyebrow'] ?? '' ),
			'ready_heading'       => sanitize_text_field( $input['ready_heading'] ?? '' ),
			'hero_breadcrumb'     => sanitize_text_field( $input['hero_breadcrumb'] ?? '' ),
			'what_footer_url'     => esc_url_raw( $input['what_footer_url'] ?? '' ),
			'begun_link_text'     => sanitize_text_field( $input['begun_link_text'] ?? '' ),
			'begun_link_url'      => esc_url_raw( $input['begun_link_url'] ?? '' ),
			'waitlist_eyebrow'    => sanitize_text_field( $input['waitlist_eyebrow'] ?? '' ),
			'waitlist_heading'    => sanitize_textarea_field( $input['waitlist_heading'] ?? '' ),
			'waitlist_button_text'=> sanitize_text_field( $input['waitlist_button_text'] ?? '' ),
			'waitlist_button_url' => esc_url_raw( $input['waitlist_button_url'] ?? '' ),
			'faq_heading'         => sanitize_text_field( $input['faq_heading'] ?? '' ),
		);

		$data['inside_pill_items']     = function_exists( 'anna_normalize_oasis_text_items' ) ? anna_normalize_oasis_text_items( $input['inside_pill_items'] ?? array() ) : array();
		$data['inside_schedule_items'] = function_exists( 'anna_normalize_oasis_schedule_items' ) ? anna_normalize_oasis_schedule_items( $input['inside_schedule_items'] ?? array() ) : array();
		$data['how_card_items']        = function_exists( 'anna_normalize_oasis_how_cards' ) ? anna_normalize_oasis_how_cards( $input['how_card_items'] ?? array() ) : array();
		$data['choose_plan_items']     = function_exists( 'anna_normalize_oasis_plan_items' ) ? anna_normalize_oasis_plan_items( $input['choose_plan_items'] ?? array() ) : array();
		$data['ready_items']           = function_exists( 'anna_normalize_oasis_text_items' ) ? anna_normalize_oasis_text_items( $input['ready_items'] ?? array() ) : array();
		$data['faq_items']             = function_exists( 'anna_normalize_coaching_faq_items' ) ? anna_normalize_coaching_faq_items( $input['faq_items'] ?? array() ) : array();

		$defaults = $this->get_oasis_page_defaults();
		$merged   = wp_parse_args( $data, $defaults );

		foreach ( array( 'inside_pill_items', 'inside_schedule_items', 'how_card_items', 'choose_plan_items', 'ready_items', 'faq_items' ) as $repeater_key ) {
			if ( empty( $merged[ $repeater_key ] ) ) {
				$merged[ $repeater_key ] = $defaults[ $repeater_key ] ?? array();
			}
		}

		return $merged;
	}

	/**
	 * @return array
	 */
	private function get_theme_mapped_oasis_defaults() {
		if ( ! function_exists( 'anna_get_oasis_page_option_map' ) ) {
			return array();
		}

		$theme = self::get_theme_options_with_defaults();
		$map   = anna_get_oasis_page_option_map();
		$out   = array();

		foreach ( $map as $plugin_key => $theme_key ) {
			if ( in_array( $plugin_key, array( 'inside_pill_items', 'inside_schedule_items', 'how_card_items', 'choose_plan_items', 'ready_items', 'faq_items' ), true ) ) {
				continue;
			}
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

		if ( function_exists( 'anna_get_oasis_repeater_from_options' ) ) {
			$out['inside_pill_items']     = anna_get_oasis_repeater_from_options( 'inside_pill_items' );
			$out['inside_schedule_items'] = anna_get_oasis_repeater_from_options( 'inside_schedule_items' );
			$out['how_card_items']        = anna_get_oasis_repeater_from_options( 'how_card_items' );
			$out['choose_plan_items']     = anna_get_oasis_repeater_from_options( 'choose_plan_items' );
			$out['ready_items']           = anna_get_oasis_repeater_from_options( 'ready_items' );
			$out['faq_items']             = anna_get_oasis_repeater_from_options( 'faq_items' );
		}

		return $out;
	}

	/**
	 * @return array
	 */
	private function get_oasis_page_defaults() {
		$defaults = function_exists( 'anna_get_oasis_default_content' )
			? anna_get_oasis_default_content()
			: array();

		$theme_defaults = $this->get_theme_mapped_oasis_defaults();
		if ( ! empty( $theme_defaults ) ) {
			$defaults = wp_parse_args( $theme_defaults, $defaults );
		}

		return $defaults;
	}
}
