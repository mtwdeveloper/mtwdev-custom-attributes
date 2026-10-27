=== MTWDev Custom Block Attributes ===
Contributors: mtwdev
Tags: gutenberg, attributes, html, data-attributes, accessibility, developer
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily add custom HTML attributes (data-*, aria-*, boolean, and key-value pairs) to any Gutenberg block with active security and native rendering.

== Description ==

**MTWDev Custom Block Attributes** is a lightweight extension for the WordPress Block Editor (Gutenberg) that allows site editors and developers to inject standard and custom HTML attributes into any supported block.

Whether you need to add custom attributes for analytics tracking (`data-track-event="click"`), improve accessibility (`aria-label`, `aria-describedby`, `role`), or use modern HTML5 boolean attributes (`download`, `inert`), this plugin provides an intuitive, high-performance interface directly within the native Gutenberg **Advanced** panel.

### Key Features

* **Native Block Inspector Integration:** Seamlessly placed in the block settings sidebar (Advanced panel) without intrusive floating buttons or separate inspectors.
* **Quick Presets Bar:** One-click shortcuts for commonly used attributes (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
* **Clean Boolean Attribute Support:** True HTML5 boolean attributes rendered cleanly without empty quotes or values (`download`, `inert`).
* **Multi-Layer Security Hardening:**
  * Automatic blocking of inline JavaScript event handlers (`onclick`, `on*`) to prevent XSS.
  * Deep validation against dangerous pseudo-protocols (`javascript:`, `vbscript:`, `data:text/html`).
  * Blocked modification of reserved layout and style attributes (`class`, `id`, `style`).
  * Capability check (`unfiltered_html`, fully customizable via filter) to prevent unauthorized attribute injection.
* **Zero Frontend Bloat:** Enqueues zero extra CSS or JS files on the frontend. Attribute injection is handled dynamically at render time via WordPress core's native `WP_HTML_Tag_Processor`.
* **Zero Block Invalidation:** Attributes are stored safely in block metadata. Disabling the plugin will never break or invalidate your blocks.
* **100% Internationalized (i18n):** Native support for English and Spanish, fully translatable through WordPress standards.
* **Fully Extensible:** Customizable via PHP filters for developers and agencies.

### Developer Extensibility & Hooks

* `mtwdev_custom_attributes_capability`: Customize the required user capability (default: `unfiltered_html`).
* `mtwdev_custom_attributes_unsupported_blocks`: Add or remove block slugs that should not allow custom attributes.
* `mtwdev_custom_attributes_disallowed`: Extend the disallowed HTML attribute blacklist.

== Installation ==

1. Upload the `mtwdev-custom-attributes` directory to your `/wp-content/plugins/` directory, or install directly through the WordPress plugin installer.
2. Activate the plugin via the **Plugins** menu in WordPress.
3. Edit any post, page, or template in the Block Editor, select a block, and expand the **Advanced** panel in the right sidebar to configure your attributes.

== Frequently Asked Questions ==

= Does this plugin affect frontend page load speed? =
No. The plugin enqueues zero additional CSS or JS files on the frontend. Attribute injection is handled dynamically via WordPress core's native `WP_HTML_Tag_Processor` during the `render_block` filter in less than 0.1 ms.

= Can I use this to add onclick or JavaScript event attributes? =
No. For security reasons and to strictly comply with WordPress.org guidelines and XSS prevention, inline event handlers starting with `on*` are disallowed.

= Can I change the capability required to edit attributes? =
Yes. You can use the `mtwdev_custom_attributes_capability` filter in a custom plugin or your theme functions:

`add_filter('mtwdev_custom_attributes_capability', function() { return 'edit_posts'; });`

= Does it work with third-party blocks? =
Yes. The plugin applies to all registered WordPress blocks that support block attributes.

== Changelog ==

= 1.0.0 =
* Initial release.
* Optimized injection using native WordPress core `WP_HTML_Tag_Processor` with early bailouts.
* Seamless integration into Gutenberg's native Advanced panel.
* Quick presets bar and full boolean attribute support.
* Multi-layer security hardening: blocked inline event handlers (`on*`), reserved attributes (`style`, `id`, `class`), and dangerous pseudo-protocols (`javascript:`, `vbscript:`, `data:text/html`).
* Added `mtwdev_custom_attributes_capability` filter for fine-grained permission control.
* Full internationalization (i18n) support with English base strings and complete Spanish (`es_ES`) translation.
* Complete automated test suite: 9 JavaScript Jest tests and 53 PHP unit/integration tests with 100% pass rate.
* WordPress.org guidelines compliance: isolated update checker and strict sanitization.
