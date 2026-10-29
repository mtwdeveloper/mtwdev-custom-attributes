=== Custom Block Attributes ===
Contributors: mtwdev
Tags: gutenberg, attributes, data-attributes, accessibility, html
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily add custom HTML attributes (data-*, aria-*, boolean, and key-value pairs) to any Gutenberg block with active security and native rendering.

== Description ==

**Custom Block Attributes** is a lightweight extension for the WordPress Block Editor (Gutenberg) that allows site editors and developers to inject standard and custom HTML attributes into any supported block.

Whether you need to add custom attributes for analytics tracking (`data-track-event="click"`), improve accessibility (`aria-label`, `aria-describedby`, `role`), or use modern HTML5 boolean attributes (`download`, `inert`), this plugin provides an intuitive, high-performance interface directly within the native Gutenberg **Advanced** panel.

### Key Features

* **Native Block Inspector Integration:** Seamlessly placed in the block settings sidebar (Advanced panel) without intrusive floating buttons or separate inspectors.
* **Quick Presets Bar:** One-click shortcuts for commonly used attributes (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
* **Clean Boolean Attribute Support:** True HTML5 boolean attributes rendered cleanly without empty quotes or values (`download`, `inert`).
* **Allow-List Validation:** Only `data-*`, `aria-*`, and a documented set of standard attributes (`role`, `title`, `tabindex`, `lang`, `dir`, `inert`, `hidden`, `download`, `rel`, `target`, `name`, `for`, `autocomplete`, `xml:lang`) are injected. URL-bearing and namespace attributes (`href`, `src`, `xmlns:*`, `xlink:*`) are always refused.
* **Multi-Layer Security Hardening:**
  * Automatic blocking of inline JavaScript event handlers (any name starting with `on`, see the FAQ).
  * Deep validation against dangerous pseudo-protocols (`javascript:`, `vbscript:`, `data:text/html`).
  * Blocked modification of reserved layout and style attributes (`class`, `id`, `style`).
  * Capability check (`unfiltered_html`, fully customizable via filter) enforced on save for every post type and editor through `parse_blocks()`/`serialize_blocks()`.
* **Zero Frontend Bloat:** Enqueues zero extra CSS or JS files on the frontend. Attribute injection is handled dynamically at render time via WordPress core's native `WP_HTML_Tag_Processor`.
* **Zero Block Invalidation:** Attributes are stored safely in block metadata. Disabling the plugin will never break or invalidate your blocks.
* **Translatable:** Every string uses the plugin text domain and is ready for the community translations published as language packs on translate.wordpress.org. A `.pot` template ships in `languages/`.
* **Fully Extensible:** Customizable via PHP filters for developers and agencies.

### Source Code

The plugin ships with its compiled assets (`build/`). The human-readable source of every compiled file is available in the public repository: [https://github.com/mtwdeveloper/mtwdev-custom-attributes](https://github.com/mtwdeveloper/mtwdev-custom-attributes). It is also included in the distributed ZIP (`src/`), together with the build configuration (`package.json`).

### Developer Extensibility & Hooks

* `mtwdev_custom_attributes_capability`: Customize the required user capability (default: `unfiltered_html`).
* `mtwdev_custom_attributes_allowed`: Extend the allowed attribute names and prefixes.
* `mtwdev_custom_attributes_unsupported_blocks`: Add or remove block slugs that should not allow custom attributes.
* `mtwdev_custom_attributes_disallowed`: Extend the additional disallowed HTML attribute blacklist.

== Installation ==

1. Upload the `mtwdev-custom-attributes` directory to your `/wp-content/plugins/` directory, or install directly through the WordPress plugin installer.
2. Activate the plugin via the **Plugins** menu in WordPress.
3. Edit any post, page, or template in the Block Editor, select a block, and expand the **Advanced** panel in the right sidebar to configure your attributes.

== Frequently Asked Questions ==

= Does this plugin affect frontend page load speed? =
No. The plugin enqueues zero additional CSS or JS files on the frontend. Attribute injection is handled dynamically via WordPress core's native `WP_HTML_Tag_Processor` during the `render_block` filter in less than 0.1 ms.

= Which attributes can I add? =
Any `data-*` and `aria-*` attribute, plus `role`, `title`, `tabindex`, `lang`, `dir`, `inert`, `hidden`, `download`, `rel`, `target`, `name`, `for`, `autocomplete` and `xml:lang`. The list can be extended with the `mtwdev_custom_attributes_allowed` filter. Attribute names outside the list are refused, both in the editor and when rendering.

= Why is an attribute like `onboarding-label` rejected? =
Every name starting with `on` is treated as a potential inline event handler and blocked, because that is the prefix browsers use for event attributes (`onclick`, `onerror`, ...). Use a `data-` attribute instead, e.g. `data-onboarding-label`.

= Can I use this to add onclick or JavaScript event attributes? =
No. For security reasons and to strictly comply with WordPress.org guidelines and XSS prevention, inline event handlers starting with `on*` are disallowed.

= Are the attributes visible in the editor canvas? =
The block editor builds its markup in JavaScript, so the attributes are applied by PHP at render time: you will see them on the front end, in server-side previews requested through the REST API (where `is_admin()` is false), in feeds and in block templates, but not in the JavaScript editor canvas.

= Can I change the capability required to edit attributes? =
Yes. You can use the `mtwdev_custom_attributes_capability` filter in a custom plugin or your theme functions:

`add_filter('mtwdev_custom_attributes_capability', function() { return 'edit_posts'; });`

= Does it work with third-party blocks? =
Yes. The plugin applies to all registered WordPress blocks that support block attributes.

= In which languages is it available? =
The source strings are English. Translations are handled by the WordPress.org translation platform (translate.wordpress.org), so any locale with an approved translation is served automatically as a language pack. The plugin itself bundles no `.po`/`.mo`/`.json` files.

== Screenshots ==

1. The **Custom HTML Attributes** control inside the native *Advanced* panel of the block settings sidebar.
2. A block with key-value attributes (`data-track`, `aria-label`) and boolean attributes (`download`) configured.
3. The rendered front-end markup with the attributes injected into the block's opening tag.

== Changelog ==

= 1.1.0 =
* Security: attribute validation is now an allow list instead of a deny list; `xmlns:*`, `xlink:*`, `href`, `src` and unknown names no longer reach the markup.
* Security: unauthorized saves are sanitized with `parse_blocks()`/`serialize_blocks()` on `content_save_pre`, replacing the `pre_kses` regex that corrupted the block JSON when a value contained `]`.
* Security: the save-time check covers every post type and every editor path (block editor REST, classic editor, XML-RPC, imports), not just posts over REST.
* Editor: the dead `blocks.registerBlockTypeAttributes` filter was replaced by the real `blocks.registerBlockType`, so blocks registered only in JavaScript keep their attributes.
* Editor: the `data-*`/`aria-*` presets now report an incomplete name instead of creating a valueless attribute.
* Editor: block editor data is passed with `wp_add_inline_script()` + `wp_json_encode()` instead of `wp_localize_script()`.
* i18n: translations are delivered as language packs from translate.wordpress.org; bundled `.po`, `.mo` and script `.json` files were removed and `load_plugin_textdomain()` is no longer needed.
* Docs: corrected changelog, screenshots section, allow-list documentation and the development/release workflow.
* Compatibility: `Requires at least: 6.6` (the compiled editor bundle loads `react-jsx-runtime`), `Tested up to: 7.1`.
* Tests: 11 JavaScript tests and 77 PHP tests.

= 1.0.0 =
* Initial release.
* Optimized injection using native WordPress core `WP_HTML_Tag_Processor` with early bailouts.
* Seamless integration into Gutenberg's native Advanced panel.
* Quick presets bar and full boolean attribute support.
* Full internationalization support with English source strings.
