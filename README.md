# Anna Content Manager

Classic-editor content management for Anna Baylis page sections.

## Current scope

- Home Hero
- Home Intro / Recognition
- Home Services section copy
- Home About
- Home Testimonials section copy
- Home Final CTA

The theme still controls layout, markup, and styling.
The plugin controls editable content.

## How to edit homepage content

1. Activate `Anna Content Manager`.
2. Open the page assigned as the homepage in WordPress.
3. Edit the section meta boxes:
   - Anna Hero Section Content
   - Anna Intro / Recognition Content
   - Anna Services Section Content
   - Anna About Section Content
   - Anna Testimonials Section Content
   - Anna Final CTA Section Content
4. Save/update the page.

## How to add a new section for a new page

This project does not use Gutenberg. New sections are added in a controlled developer workflow.

### 1. Add a new meta box in the plugin

In `includes/class-anna-content-manager.php`:

- register a new meta box in `register_meta_boxes()`
- add a `render_*_meta_box()` method
- add save logic in `save_page_content()`
- store data in post meta using `_anna_content_<section>`

Example meta key:

- `_anna_content_contact`

### 2. Add a theme helper/fallback

In `inc/helpers.php`:

- create a helper such as `anna_get_contact_section_content()`
- read plugin page meta first
- fall back to theme defaults or legacy values if needed

### 3. Add the section template in the theme

Create a section file in:

- `template-parts/sections/contact.php`

Use the helper to render content while keeping the design fixed.

### 4. Use the section in a page template

For a custom page template or a normal page template:

- call `get_template_part( 'template-parts/sections/contact' );`

or build a page template with multiple section includes in the order you want.

### 5. Create the page in admin

- Create a new page
- Fill the section meta boxes
- Assign the correct page template if the theme provides one

## Architecture rule

- Plugin owns content fields and content storage
- Theme owns templates and appearance
- Admin edits content only
- Developers control structure and section availability
