<?php
/**
 * Speaking page content meta box, save, and defaults.
 *
 * @package Anna_Content_Manager
 */

if (!defined("ABSPATH")) {
    exit();
}

trait Anna_Speaking_Page_Content
{
    private function register_speaking_page_meta_box($post)
    {
        if (
            "speaking" !== $post->post_name &&
            "page-speaking.php" !== get_page_template_slug($post->ID)
        ) {
            return;
        }

        add_meta_box(
            "anna_content_speaking_page",
            __("Anna Speaking Page Content", "anna-baylis"),
            [$this, "render_speaking_page_meta_box"],
            "page",
            "normal",
            "high",
        );
    }

    public function render_speaking_page_meta_box($post)
    {
        wp_nonce_field("anna_content_save_page", "anna_content_page_nonce");
        $data = $this->get_speaking_page_content_with_defaults($post->ID);
        $this->maybe_backfill_speaking_page_meta($post->ID, $data);
        $prefix = "anna_content_speaking_page";
        ?>
		<p><?php esc_html_e(
      "Edit Speaking page copy, images, and repeatable sections.",
      "anna-baylis",
  ); ?></p>

		<h3><?php esc_html_e("Hero", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "hero_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["hero_eyebrow"],
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "hero_heading",
       __("Heading", "anna-baylis"),
       $data["hero_heading"],
       3,
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "hero_body",
       __("Description", "anna-baylis"),
       $data["hero_body"],
       4,
   ); ?>
			<?php $this->render_media_field(
       $prefix,
       "hero_image_id",
       __("Background Image", "anna-baylis"),
       $data["hero_image_id"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "hero_button_text",
       __("Primary Button Text", "anna-baylis"),
       $data["hero_button_text"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "hero_button_url",
       __("Primary Button URL", "anna-baylis"),
       $data["hero_button_url"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "hero_secondary_text",
       __("Secondary Link Text", "anna-baylis"),
       $data["hero_secondary_text"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "hero_secondary_url",
       __("Secondary Link URL", "anna-baylis"),
       $data["hero_secondary_url"],
   ); ?>
			<?php $this->render_speaking_stat_repeater_field(
       $data["hero_stat_items"] ?? [],
   ); ?>
		</table>

		<h3><?php esc_html_e("What I Bring to the Stage", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "bring_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["bring_eyebrow"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "bring_heading_line1",
       __("Heading Line 1", "anna-baylis"),
       $data["bring_heading_line1"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "bring_heading_line2",
       __("Heading Line 2", "anna-baylis"),
       $data["bring_heading_line2"],
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "bring_body",
       __("Body", "anna-baylis"),
       $data["bring_body"],
       8,
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "bring_quote",
       __("Quote", "anna-baylis"),
       $data["bring_quote"],
       3,
   ); ?>
			<?php $this->render_media_field(
       $prefix,
       "bring_image_id",
       __("Image", "anna-baylis"),
       $data["bring_image_id"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "bring_button_text",
       __("Button Text", "anna-baylis"),
       $data["bring_button_text"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "bring_button_url",
       __("Button URL", "anna-baylis"),
       $data["bring_button_url"],
   ); ?>
		</table>

		<h3><?php esc_html_e("Speaking Topics", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "topics_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["topics_eyebrow"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "topics_heading",
       __("Heading", "anna-baylis"),
       $data["topics_heading"],
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "topics_intro",
       __("Intro", "anna-baylis"),
       $data["topics_intro"],
       3,
   ); ?>
			<?php $this->render_speaking_topic_repeater_field(
       $data["topics_card_items"] ?? [],
   ); ?>
		</table>

		<h3><?php esc_html_e("Talk Formats", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "formats_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["formats_eyebrow"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "formats_heading",
       __("Heading", "anna-baylis"),
       $data["formats_heading"],
   ); ?>
			<?php $this->render_speaking_format_repeater_field(
       $data["formats_card_items"] ?? [],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "formats_audience_heading",
       __("Audience Heading", "anna-baylis"),
       $data["formats_audience_heading"],
   ); ?>
			<?php $this->render_speaking_text_repeater_field(
       "formats_audience_items",
       $data["formats_audience_items"] ?? [],
       __("Audience List", "anna-baylis"),
   ); ?>
		</table>

		<h3><?php esc_html_e("What Audiences Take Away", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "takeaway_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["takeaway_eyebrow"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "takeaway_heading",
       __("Heading", "anna-baylis"),
       $data["takeaway_heading"],
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "takeaway_body",
       __("Body", "anna-baylis"),
       $data["takeaway_body"],
       5,
   ); ?>
			<?php $this->render_speaking_text_repeater_field(
       "takeaway_items",
       $data["takeaway_items"] ?? [],
       __("Takeaway Items", "anna-baylis"),
   ); ?>
		</table>

		<h3><?php esc_html_e("Book Anna to Speak", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "book_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["book_eyebrow"] ?? "",
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_heading_line1",
       __("Heading Line 1", "anna-baylis"),
       $data["book_heading_line1"] ?? "",
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_heading_line2",
       __("Heading Line 2 (accent span)", "anna-baylis"),
       $data["book_heading_line2"] ?? "",
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "book_body",
       __("Body", "anna-baylis"),
       $data["book_body"] ?? "",
       5,
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_card_heading",
       __("Card Heading", "anna-baylis"),
       $data["book_card_heading"] ?? "",
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "book_card_body",
       __("Card Body", "anna-baylis"),
       $data["book_card_body"] ?? "",
       3,
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_card_button_text",
       __("Card Button Text", "anna-baylis"),
       $data["book_card_button_text"] ?? "",
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_card_button_url",
       __("Card Button URL", "anna-baylis"),
       $data["book_card_button_url"] ?? "",
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_card_contact_prefix",
       __("Contact Prefix Text", "anna-baylis"),
       $data["book_card_contact_prefix"] ?? "",
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_card_email",
       __("Contact Email", "anna-baylis"),
       $data["book_card_email"] ?? "",
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "book_card_footer",
       __("Card Footer Text", "anna-baylis"),
       $data["book_card_footer"] ?? "",
   ); ?>
		</table>

		<h3><?php esc_html_e("Recent Experience", "anna-baylis"); ?></h3>
		<table class="form-table">
			<?php $this->render_text_field(
       $prefix,
       "experience_eyebrow",
       __("Eyebrow", "anna-baylis"),
       $data["experience_eyebrow"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_heading_primary",
       __("Heading (Primary)", "anna-baylis"),
       $data["experience_heading_primary"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_heading_secondary",
       __("Heading (Secondary)", "anna-baylis"),
       $data["experience_heading_secondary"],
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "experience_body",
       __("Body", "anna-baylis"),
       $data["experience_body"],
       8,
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_link_prefix",
       __("Link Prefix", "anna-baylis"),
       $data["experience_link_prefix"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_link_label",
       __("Link Label", "anna-baylis"),
       $data["experience_link_label"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_link_url",
       __("Link URL", "anna-baylis"),
       $data["experience_link_url"],
   ); ?>
			<?php $this->render_textarea_field(
       $prefix,
       "experience_testimonial_quote",
       __("Testimonial Quote", "anna-baylis"),
       $data["experience_testimonial_quote"],
       4,
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_testimonial_name",
       __("Testimonial Name", "anna-baylis"),
       $data["experience_testimonial_name"],
   ); ?>
			<?php $this->render_text_field(
       $prefix,
       "experience_testimonial_role",
       __("Testimonial Role", "anna-baylis"),
       $data["experience_testimonial_role"],
   ); ?>
		</table>
		<?php
    }

    private function render_speaking_text_repeater_field($key, $items, $label)
    {
        $items = function_exists("anna_normalize_speaking_text_items")
            ? anna_normalize_speaking_text_items($items)
            : (array) $items; ?>
		<tr>
			<th scope="row"><?php echo esc_html($label); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="<?php echo esc_attr(
        $key,
    ); ?>">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ($items as $index => $item): ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="large-text" name="anna_content_speaking_page[<?php echo esc_attr(
            $key,
        ); ?>][<?php echo esc_attr(
    $index,
); ?>][text]" value="<?php echo esc_attr($item["text"] ?? ""); ?>"></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
            "Remove",
            "anna-baylis",
        ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e(
         "Add",
         "anna-baylis",
     ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="large-text" name="anna_content_speaking_page[<?php echo esc_attr(
           $key,
       ); ?>][__INDEX__][text]" value=""></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
           "Remove",
           "anna-baylis",
       ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
    }

    private function render_speaking_stat_repeater_field($items)
    {
        $items = function_exists("anna_normalize_speaking_stat_items")
            ? anna_normalize_speaking_stat_items($items)
            : (array) $items; ?>
		<tr>
			<th scope="row"><?php esc_html_e("Hero Stats", "anna-baylis"); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="speaking-stats">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ($items as $index => $item): ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="small-text" name="anna_content_speaking_page[hero_stat_items][<?php echo esc_attr(
            $index,
        ); ?>][value]" value="<?php echo esc_attr(
    $item["value"] ?? "",
); ?>"></p>
								<p><input type="text" class="large-text" name="anna_content_speaking_page[hero_stat_items][<?php echo esc_attr(
            $index,
        ); ?>][label]" value="<?php echo esc_attr(
    $item["label"] ?? "",
); ?>"></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
            "Remove",
            "anna-baylis",
        ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e(
         "Add Stat",
         "anna-baylis",
     ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="small-text" name="anna_content_speaking_page[hero_stat_items][__INDEX__][value]" value=""></p>
							<p><input type="text" class="large-text" name="anna_content_speaking_page[hero_stat_items][__INDEX__][label]" value=""></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
           "Remove",
           "anna-baylis",
       ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
    }

    private function render_speaking_topic_repeater_field($items)
    {
        $items = function_exists("anna_normalize_speaking_topic_cards")
            ? anna_normalize_speaking_topic_cards($items)
            : (array) $items; ?>
		<tr>
			<th scope="row"><?php esc_html_e("Topic Cards", "anna-baylis"); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="speaking-topics">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ($items as $index => $item): ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="small-text" name="anna_content_speaking_page[topics_card_items][<?php echo esc_attr(
            $index,
        ); ?>][icon]" value="<?php echo esc_attr(
    $item["icon"] ?? "brain",
); ?>"></p>
								<p><input type="text" class="large-text" name="anna_content_speaking_page[topics_card_items][<?php echo esc_attr(
            $index,
        ); ?>][title]" value="<?php echo esc_attr(
    $item["title"] ?? "",
); ?>"></p>
								<p><textarea class="large-text" rows="3" name="anna_content_speaking_page[topics_card_items][<?php echo esc_attr(
            $index,
        ); ?>][body]"><?php echo esc_textarea(
    $item["body"] ?? "",
); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
            "Remove",
            "anna-baylis",
        ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e(
         "Add Topic",
         "anna-baylis",
     ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="small-text" name="anna_content_speaking_page[topics_card_items][__INDEX__][icon]" value="brain"></p>
							<p><input type="text" class="large-text" name="anna_content_speaking_page[topics_card_items][__INDEX__][title]" value=""></p>
							<p><textarea class="large-text" rows="3" name="anna_content_speaking_page[topics_card_items][__INDEX__][body]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
           "Remove",
           "anna-baylis",
       ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
    }

    private function render_speaking_format_repeater_field($items)
    {
        $items = function_exists("anna_normalize_speaking_format_cards")
            ? anna_normalize_speaking_format_cards($items)
            : (array) $items; ?>
		<tr>
			<th scope="row"><?php esc_html_e("Format Cards", "anna-baylis"); ?></th>
			<td>
				<div class="anna-content-repeater" data-anna-content-repeater="speaking-formats">
					<div class="anna-content-repeater__rows" data-anna-content-repeater-rows="true">
						<?php foreach ($items as $index => $item): ?>
							<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
								<p><input type="text" class="small-text" name="anna_content_speaking_page[formats_card_items][<?php echo esc_attr(
            $index,
        ); ?>][number]" value="<?php echo esc_attr(
    $item["number"] ?? "",
); ?>"></p>
								<p><input type="text" class="large-text" name="anna_content_speaking_page[formats_card_items][<?php echo esc_attr(
            $index,
        ); ?>][title]" value="<?php echo esc_attr(
    $item["title"] ?? "",
); ?>"></p>
								<p><textarea class="large-text" rows="3" name="anna_content_speaking_page[formats_card_items][<?php echo esc_attr(
            $index,
        ); ?>][body]"><?php echo esc_textarea(
    $item["body"] ?? "",
); ?></textarea></p>
								<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
            "Remove",
            "anna-baylis",
        ); ?></button></p><hr>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" data-anna-content-repeater-add="true"><?php esc_html_e(
         "Add Format",
         "anna-baylis",
     ); ?></button>
					<template data-anna-content-repeater-template="true">
						<div class="anna-content-repeater__row" data-anna-content-repeater-row="true">
							<p><input type="text" class="small-text" name="anna_content_speaking_page[formats_card_items][__INDEX__][number]" value=""></p>
							<p><input type="text" class="large-text" name="anna_content_speaking_page[formats_card_items][__INDEX__][title]" value=""></p>
							<p><textarea class="large-text" rows="3" name="anna_content_speaking_page[formats_card_items][__INDEX__][body]"></textarea></p>
							<p><button type="button" class="button-link-delete" data-anna-content-repeater-remove="true"><?php esc_html_e(
           "Remove",
           "anna-baylis",
       ); ?></button></p><hr>
						</div>
					</template>
				</div>
			</td>
		</tr>
		<?php
    }

    private function save_speaking_page_content($post_id)
    {
        if (
            !isset($_POST["anna_content_speaking_page"]) ||
            !is_array($_POST["anna_content_speaking_page"])
        ) {
            return;
        }
        $input = wp_unslash($_POST["anna_content_speaking_page"]);
        update_post_meta(
            $post_id,
            "_anna_content_speaking_page",
            $this->sanitize_speaking_page_content($input),
        );
    }

    public function get_speaking_page_content($post_id)
    {
        return $this->get_speaking_page_content_with_defaults($post_id);
    }

    private function get_speaking_page_content_with_defaults($post_id)
    {
        $stored = get_post_meta(
            absint($post_id),
            "_anna_content_speaking_page",
            true,
        );
        $stored = is_array($stored) ? $stored : [];
        $defaults = $this->get_speaking_page_defaults();
        $merged = wp_parse_args($stored, $defaults);

        $repeaters = [
            "hero_stat_items",
            "topics_card_items",
            "formats_card_items",
            "formats_audience_items",
            "takeaway_items",
        ];
        foreach ($defaults as $key => $default_value) {
            if (in_array($key, $repeaters, true)) {
                continue;
            }
            if (
                !array_key_exists($key, $merged) ||
                $this->is_blank_section_value($merged[$key], $key)
            ) {
                if (!$this->is_blank_section_value($default_value, $key)) {
                    $merged[$key] = $default_value;
                }
            }
        }

        $merged["hero_stat_items"] = $this->resolve_speaking_stat_items(
            $stored,
            $defaults,
        );
        $merged["topics_card_items"] = $this->resolve_speaking_topic_cards(
            $stored,
            $defaults,
        );
        $merged["formats_card_items"] = $this->resolve_speaking_format_cards(
            $stored,
            $defaults,
        );
        $merged["formats_audience_items"] = $this->resolve_speaking_text_items(
            $stored,
            $defaults,
            "formats_audience_items",
        );
        $merged["takeaway_items"] = $this->resolve_speaking_text_items(
            $stored,
            $defaults,
            "takeaway_items",
        );

        return $merged;
    }

    private function resolve_speaking_text_items($stored, $defaults, $key)
    {
        if (
            isset($stored[$key]) &&
            is_array($stored[$key]) &&
            !empty($stored[$key])
        ) {
            $items = function_exists("anna_normalize_speaking_text_items")
                ? anna_normalize_speaking_text_items($stored[$key])
                : $stored[$key];
            if (!empty($items)) {
                return $items;
            }
        }
        $default_items = $defaults[$key] ?? [];
        return function_exists("anna_normalize_speaking_text_items")
            ? anna_normalize_speaking_text_items($default_items)
            : $default_items;
    }

    private function resolve_speaking_stat_items($stored, $defaults)
    {
        if (
            isset($stored["hero_stat_items"]) &&
            is_array($stored["hero_stat_items"]) &&
            !empty($stored["hero_stat_items"])
        ) {
            $items = function_exists("anna_normalize_speaking_stat_items")
                ? anna_normalize_speaking_stat_items($stored["hero_stat_items"])
                : $stored["hero_stat_items"];
            if (!empty($items)) {
                return $items;
            }
        }
        $default_items = $defaults["hero_stat_items"] ?? [];
        return function_exists("anna_normalize_speaking_stat_items")
            ? anna_normalize_speaking_stat_items($default_items)
            : $default_items;
    }

    private function resolve_speaking_topic_cards($stored, $defaults)
    {
        if (
            isset($stored["topics_card_items"]) &&
            is_array($stored["topics_card_items"]) &&
            !empty($stored["topics_card_items"])
        ) {
            $items = function_exists("anna_normalize_speaking_topic_cards")
                ? anna_normalize_speaking_topic_cards(
                    $stored["topics_card_items"],
                )
                : $stored["topics_card_items"];
            if (!empty($items)) {
                return $items;
            }
        }
        $default_items = $defaults["topics_card_items"] ?? [];
        return function_exists("anna_normalize_speaking_topic_cards")
            ? anna_normalize_speaking_topic_cards($default_items)
            : $default_items;
    }

    private function resolve_speaking_format_cards($stored, $defaults)
    {
        if (
            isset($stored["formats_card_items"]) &&
            is_array($stored["formats_card_items"]) &&
            !empty($stored["formats_card_items"])
        ) {
            $items = function_exists("anna_normalize_speaking_format_cards")
                ? anna_normalize_speaking_format_cards(
                    $stored["formats_card_items"],
                )
                : $stored["formats_card_items"];
            if (!empty($items)) {
                return $items;
            }
        }
        $default_items = $defaults["formats_card_items"] ?? [];
        return function_exists("anna_normalize_speaking_format_cards")
            ? anna_normalize_speaking_format_cards($default_items)
            : $default_items;
    }

    private function maybe_backfill_speaking_page_meta($post_id, $data)
    {
        $post_id = absint($post_id);
        if (
            !$post_id ||
            !is_array($data) ||
            get_post_meta($post_id, "_anna_speaking_meta_backfilled_v1", true)
        ) {
            return;
        }

        $stored = get_post_meta($post_id, "_anna_content_speaking_page", true);
        $stored = is_array($stored) ? $stored : [];
        $changed = false;
        $repeaters = [
            "hero_stat_items",
            "topics_card_items",
            "formats_card_items",
            "formats_audience_items",
            "takeaway_items",
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $repeaters, true)) {
                continue;
            }
            if (
                !array_key_exists($key, $stored) ||
                $this->is_blank_section_value($stored[$key], $key)
            ) {
                if (!$this->is_blank_section_value($value, $key)) {
                    $stored[$key] = $value;
                    $changed = true;
                }
            }
        }

        foreach ($repeaters as $repeater_key) {
            $has_items =
                isset($stored[$repeater_key]) &&
                is_array($stored[$repeater_key]) &&
                !empty($stored[$repeater_key]);
            if (!$has_items && !empty($data[$repeater_key])) {
                $stored[$repeater_key] = $data[$repeater_key];
                $changed = true;
            }
        }

        if ($changed) {
            update_post_meta($post_id, "_anna_content_speaking_page", $stored);
        }
        update_post_meta($post_id, "_anna_speaking_meta_backfilled_v1", 1);
    }

    private function sanitize_speaking_page_content($input)
    {
        $scalar_keys = [
            "hero_eyebrow",
            "hero_button_text",
            "hero_secondary_text",
            "bring_eyebrow",
            "bring_heading_line1",
            "bring_heading_line2",
            "bring_button_text",
            "topics_eyebrow",
            "topics_heading",
            "formats_eyebrow",
            "formats_heading",
            "formats_audience_heading",
            "takeaway_eyebrow",
            "takeaway_heading",
            "book_eyebrow",
            "book_heading_line1",
            "book_heading_line2",
            "book_card_heading",
            "book_card_button_text",
            "book_card_contact_prefix",
            "book_card_email",
            "book_card_footer",
            "experience_eyebrow",
            "experience_heading_primary",
            "experience_heading_secondary",
            "experience_link_prefix",
            "experience_link_label",
            "experience_testimonial_name",
            "experience_testimonial_role",
        ];
        $url_keys = [
            "hero_button_url",
            "hero_secondary_url",
            "bring_button_url",
            "book_card_button_url",
            "experience_link_url",
        ];
        $textarea_keys = [
            "hero_heading",
            "hero_body",
            "bring_body",
            "bring_quote",
            "topics_intro",
            "takeaway_body",
            "book_body",
            "book_card_body",
            "experience_body",
            "experience_testimonial_quote",
        ];
        $image_keys = ["hero_image_id", "bring_image_id"];

        $data = [];
        foreach ($scalar_keys as $key) {
            $data[$key] = sanitize_text_field($input[$key] ?? "");
        }
        foreach ($url_keys as $key) {
            $data[$key] = esc_url_raw($input[$key] ?? "");
        }
        foreach ($textarea_keys as $key) {
            $data[$key] = sanitize_textarea_field($input[$key] ?? "");
        }
        foreach ($image_keys as $key) {
            $data[$key] = absint($input[$key] ?? 0);
        }

        $data["hero_stat_items"] = function_exists(
            "anna_normalize_speaking_stat_items",
        )
            ? anna_normalize_speaking_stat_items(
                $input["hero_stat_items"] ?? [],
            )
            : [];
        $data["topics_card_items"] = function_exists(
            "anna_normalize_speaking_topic_cards",
        )
            ? anna_normalize_speaking_topic_cards(
                $input["topics_card_items"] ?? [],
            )
            : [];
        $data["formats_card_items"] = function_exists(
            "anna_normalize_speaking_format_cards",
        )
            ? anna_normalize_speaking_format_cards(
                $input["formats_card_items"] ?? [],
            )
            : [];
        $data["formats_audience_items"] = function_exists(
            "anna_normalize_speaking_text_items",
        )
            ? anna_normalize_speaking_text_items(
                $input["formats_audience_items"] ?? [],
            )
            : [];
        $data["takeaway_items"] = function_exists(
            "anna_normalize_speaking_text_items",
        )
            ? anna_normalize_speaking_text_items($input["takeaway_items"] ?? [])
            : [];

        return wp_parse_args($data, $this->get_speaking_page_defaults());
    }

    private function get_theme_mapped_speaking_defaults()
    {
        if (!function_exists("anna_get_speaking_page_option_map")) {
            return [];
        }
        $theme = self::get_theme_options_with_defaults();
        $map = anna_get_speaking_page_option_map();
        $out = [];
        $repeaters = [
            "hero_stat_items",
            "topics_card_items",
            "formats_card_items",
            "formats_audience_items",
            "takeaway_items",
        ];

        foreach ($map as $plugin_key => $theme_key) {
            if (in_array($plugin_key, $repeaters, true)) {
                continue;
            }
            if (!isset($theme[$theme_key])) {
                continue;
            }
            $value = $theme[$theme_key];
            $out[$plugin_key] = str_ends_with($plugin_key, "_image_id")
                ? absint($value)
                : $value;
        }

        if (function_exists("anna_get_speaking_repeater_from_options")) {
            $out["hero_stat_items"] = anna_get_speaking_repeater_from_options(
                "hero_stat_items",
            );
            $out["topics_card_items"] = anna_get_speaking_repeater_from_options(
                "topics_card_items",
            );
            $out[
                "formats_card_items"
            ] = anna_get_speaking_repeater_from_options("formats_card_items");
            $out[
                "formats_audience_items"
            ] = anna_get_speaking_repeater_from_options(
                "formats_audience_items",
            );
            $out["takeaway_items"] = anna_get_speaking_repeater_from_options(
                "takeaway_items",
            );
        }

        return $out;
    }

    private function get_speaking_page_defaults()
    {
        $defaults = function_exists("anna_get_speaking_default_content")
            ? anna_get_speaking_default_content()
            : [];
        $theme = $this->get_theme_mapped_speaking_defaults();
        return !empty($theme) ? wp_parse_args($theme, $defaults) : $defaults;
    }
}
