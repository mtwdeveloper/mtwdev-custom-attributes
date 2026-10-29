# Custom Block Attributes

[![WordPress](https://img.shields.io/badge/WordPress-6.6%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Tests](https://img.shields.io/badge/Tests-Passing%20(88%2F88)-brightgreen.svg)](#-automated-testing)
[![Version](https://img.shields.io/badge/Version-1.1.0-informational.svg)](https://github.com/mtwdeveloper/mtwdev-custom-attributes/releases)

*Read this in other languages:* [English](README.md) | [Español](README.es.md)

**Custom Block Attributes** is a lightweight, secure, and developer-friendly WordPress plugin that lets site editors and developers inject custom HTML attributes (`data-*`, `aria-*`, `title`, boolean attributes like `download` or `inert`, and custom key-value pairs) into **any Gutenberg block** directly from the block editor.

---

## 🚀 Key Features

- **Native Block Editor Integration:** Neatly embedded within the official **Advanced** panel in the block settings sidebar.
- **Boolean & Key-Value Support:**
  - Key-value attributes: `data-user-id="105"`, `aria-label="Close modal"`, `title="Information"`.
  - Clean boolean/valueless attributes: `download`, `inert`, `novalidate`, `required`.
- **Quick Presets Bar:** One-click shortcuts for commonly used attributes (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
- **Multi-Layer Security Hardening & XSS Mitigation:**
  - Strict HTML5 attribute name validation (`/^[a-z0-9_\-:]+$/`).
  - Allow-list validation: only `data-*`, `aria-*`, and a documented set of standard attributes (`role`, `title`, `tabindex`, `lang`, `dir`, `inert`, `hidden`, `download`, `rel`, `target`, `name`, `for`, `autocomplete`, `xml:lang`) are injected. URL-bearing and namespace attributes (`href`, `src`, `xmlns:*`, `xlink:*`) are always refused.
  - Automatic blocking of inline DOM events (`onclick`, `onload`, `on*`) and executable attributes (`srcdoc`, `formaction`, `action`).
  - Native protection against dangerous pseudo-protocols (`javascript:`, `vbscript:`, `data:text/html`) including whitespace evasion.
  - Reserved attributes protection: prevents overriding `class`, `id`, and `style`.
  - Save-time enforcement: users without the required capability lose `customAttributes` on save, parsed and rebuilt with `parse_blocks()`/`serialize_blocks()`, so block markup is never corrupted.
- **Zero Frontend Overhead ($O(1)$):**
  - Zero additional CSS or JS files enqueued on the frontend.
  - Ultra-fast injection at runtime using native WordPress `WP_HTML_Tag_Processor` with early bailouts.
- **Zero Block Invalidation:** Attributes are stored in block metadata and injected dynamically at render time (`render_block`). If the plugin is deactivated, blocks will never throw Gutenberg invalidation errors (*"This block contains unexpected or invalid content"*).
- **Translation Ready (i18n):** English source strings, a `languages/` template and community translations served as language packs from translate.wordpress.org. No bundled `.po`/`.mo`/`.json` files.
- **Automated Testing Suite:** PHP and JavaScript tests covering sanitization, rendering, security protocols, the save-time enforcement and editor validation.
- **Developer Friendly & Extensible:** PHP filters for capability customization (`mtwdev_custom_attributes_capability`), the attribute allow list (`mtwdev_custom_attributes_allowed`), the extra deny list (`mtwdev_custom_attributes_disallowed`), and unsupported blocks (`mtwdev_custom_attributes_unsupported_blocks`).

---

## 📋 Requirements

- **WordPress:** 6.6 or higher (required for the `react-jsx-runtime` script dependency used by the compiled editor bundle).
- **PHP:** 7.4 or higher (fully compatible with PHP 8.0, 8.1, 8.2, and 8.3).
- **Gutenberg Block Editor:** Enabled.

---

## 📦 Installation

### Method 1: Direct Download (ZIP)
1. Download the latest `.zip` from [Releases](https://github.com/mtwdeveloper/mtwdev-custom-attributes/releases) or click **Code > Download ZIP**.
2. In your WordPress admin dashboard, navigate to **Plugins > Add New > Upload Plugin**.
3. Choose the downloaded ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Method 2: Git Clone (Developers)
1. Clone the repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/mtwdeveloper/mtwdev-custom-attributes.git mtwdev-custom-attributes
   ```
2. Install dependencies and build the editor assets:
   ```bash
   cd mtwdev-custom-attributes
   pnpm install
   pnpm run build
   ```
3. Activate **Custom Block Attributes** in **Plugins > Installed Plugins** (or run `wp plugin activate mtwdev-custom-attributes`).

---

## 🧪 Automated Testing

The plugin includes automated tests for both JavaScript and PHP:

```bash
# Run all tests (JS + PHP)
pnpm test

# Run JavaScript unit tests (Jest via @wordpress/scripts)
pnpm run test:js

# Run PHP unit and integration tests
pnpm run test:php
```

All 88 tests (11 JS tests + 77 PHP tests) run isolated and test sanitization, validation, security vectors, save-time enforcement and HTML rendering.

---

## 🌍 Internationalization (i18n)

The plugin ships no compiled translations: every locale is served as a language pack from [translate.wordpress.org](https://translate.wordpress.org/). Only the template is generated locally:

```bash
# Refresh languages/mtwdev-custom-attributes.pot (PHP + JS strings)
pnpm run i18n:pot
```

When you add or change any user-facing string, run that command and commit the updated `.pot`, so GlotPress picks up the new strings. See [CONTRIBUTING.md](CONTRIBUTING.md#-translations) for the full workflow.

---

## 🛠️ How to Use

1. Open any post, page, or template in the **Block Editor (Gutenberg)**.
2. Select any block you wish to customize (Button, Group, Image, Paragraph, Heading, etc.).
3. In the right-hand sidebar, expand the **Advanced** panel.
4. Under **Custom HTML Attributes**:
   - Click any **Quick Preset** button (e.g., `+data-*` or `+download`) or type the attribute name manually.
   - If it is a valueless boolean attribute (such as `download` or `inert`), toggle **Boolean / empty attribute**.
   - Click **Add**.
   - If it is not boolean, enter the desired value in the input field.
5. Save or publish your content.

---

## 🛡️ Security & Best Practices

1. **Attribute Blacklist:** Denies all event handlers starting with `on*` (`onclick`, `onerror`, `onload`, etc.) and code execution vectors (`srcdoc`, `formaction`, `xmlns`).
2. **Sanitization & Escaping:** Attribute keys are strictly sanitized (`/^[a-z0-9_\-:]+$/`) and values are escaped with `esc_attr()` or processed via `WP_HTML_Tag_Processor`.
3. **Protocol Sanitization:** Deep inspection rejecting `javascript:`, `vbscript:`, and `data:text/html` even with whitespace or newline evasion.
4. **Capability Checks:** Users lacking the required capability (default: `unfiltered_html`) cannot add or edit attributes, and content is safely sanitized via the `pre_kses` filter.

---

## 🔌 Developer API & Filters

### Customizing the Required Capability
```php
add_filter('mtwdev_custom_attributes_capability', function (string $cap): string {
    // Allow any user who can edit posts
    return 'edit_posts';
});
```

### Extending the Allowed Attributes

Attributes are validated against an allow list. Add your own names or prefixes here; they are still checked against the deny list and the pseudo-protocol values.

```php
add_filter('mtwdev_custom_attributes_allowed', function (array $allowed): array {
    $allowed['exact'][]    = 'data-qa-id';
    $allowed['prefixes'][] = 'x-company-';
    return $allowed;
});
```

### Customizing Disallowed Attributes
```php
add_filter('mtwdev_custom_attributes_disallowed', function (array $disallowed): array {
    $disallowed[] = 'custom-restricted-attr';
    return $disallowed;
});
```

### Customizing Unsupported Blocks
```php
add_filter('mtwdev_custom_attributes_unsupported_blocks', function (array $blocks): array {
    $blocks[] = 'my-custom-plugin/unsupported-block';
    return $blocks;
});
```

---

## 📄 License

This project is open-source software licensed under the [GNU General Public License v2.0 or later (GPL-2.0-or-later)](LICENSE).
