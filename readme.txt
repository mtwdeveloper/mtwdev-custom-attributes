=== MTWDev Custom Block Attributes ===
Contributors: mtwdev
Tags: gutenberg, attributes, html, data-attributes, accessibility, developer
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily add custom HTML attributes (data-*, aria-*, boolean, and key-value pairs) to any Gutenberg block.

== Description ==

**MTWDev Custom Block Attributes** is an extension for the WordPress Block Editor (Gutenberg) that allows site editors and developers to inject standard and custom HTML attributes into any existing block.

Whether you need to add custom attributes for analytics tracking (`data-track-event="click"`), improve accessibility (`aria-label`, `role`), or use modern HTML5 boolean attributes (`download`, `inert`), this plugin provides an intuitive, lightweight interface within the native block Advanced sidebar panel.

### Features

* **Advanced Panel Integration:** Seamlessly integrated into the block settings sidebar without intrusive floating panels.
* **Quick Presets Bar:** One-click shortcuts for `data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, and `inert`.
* **Clean Boolean Support:** True boolean attributes rendered without values (`download`, `inert`, `novalidate`).
* **Active Security:** Automatic blocking of inline JavaScript event handlers (`onclick`, `on*`) and strict validation of names and values.
* **Zero Frontend Bloat:** No frontend CSS or JavaScript enqueued; clean, high-performance injection at render time via `WP_HTML_Tag_Processor`.
* **Zero Block Invalidation:** Attributes are stored in block metadata and injected at render time. Deactivating the plugin will never break or invalidate your blocks.

== Installation ==

1. Upload the `mtwdev-custom-attributes` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin via the **Plugins** menu in WordPress.
3. Edit any post or page in the Block Editor, select a block, and expand the **Advanced** panel in the right sidebar to access the attributes settings.

== Frequently Asked Questions ==

= Does this plugin affect frontend page load speed? =
No. The plugin enqueues zero additional CSS or JS files on the frontend. Attribute injection is handled dynamically via WordPress core's native `WP_HTML_Tag_Processor` during the `render_block` filter in less than 0.1 ms.

= Can I use this to add onclick or JavaScript event attributes? =
No. For security reasons and to comply with WordPress.org guidelines and XSS prevention, inline event handlers starting with `on` are strictly prohibited.

= Does it work with third-party blocks? =
Yes. The plugin applies to all registered WordPress blocks that support block attributes.

== Changelog ==

= 1.0.0 =
* Initial release.
* Optimized injection using native WordPress core `WP_HTML_Tag_Processor`.
* Seamless integration in the Gutenberg Advanced panel.
* Quick presets bar and full boolean attribute support.
* Unsupported blocks exclusion (`core/html`, `core/shortcode`, `core/freeform`).
* Strict security validation, capability checks (`unfiltered_html`), and XSS prevention.
* Extensible PHP hooks: `mtwdev_custom_attributes_disallowed` and `mtwdev_custom_attributes_unsupported_blocks`.
* GitHub automatic updates via Plugin Update Checker (PUC v5.7).
