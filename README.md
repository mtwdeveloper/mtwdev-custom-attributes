# MTWDev Custom Block Attributes

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Tests](https://img.shields.io/badge/Tests-Passing%20(62%2F62)-brightgreen.svg)](#-automated-testing)
[![WordPress.org](https://img.shields.io/badge/WordPress.org-Plugin%20Ready-blue.svg)](https://wordpress.org/)

*Read this in other languages:* [English](README.md) | [Español](README.es.md)

**MTWDev Custom Block Attributes** is a lightweight, secure, and developer-friendly WordPress plugin that lets site editors and developers inject custom HTML attributes (`data-*`, `aria-*`, `title`, boolean attributes like `download` or `inert`, and custom key-value pairs) into **any Gutenberg block** directly from the block editor.

---

## 🚀 Key Features

- **Native Block Editor Integration:** Neatly embedded within the official **Advanced** panel in the block settings sidebar.
- **Boolean & Key-Value Support:**
  - Key-value attributes: `data-user-id="105"`, `aria-label="Close modal"`, `title="Information"`.
  - Clean boolean/valueless attributes: `download`, `inert`, `novalidate`, `required`.
- **Quick Presets Bar:** One-click shortcuts for commonly used attributes (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
- **Multi-Layer Security Hardening & XSS Mitigation:**
  - Strict HTML5 attribute name validation (`/^[a-z0-9_\-:]+$/`).
  - Automatic blocking of inline DOM events (`onclick`, `onload`, `on*`) and executable attributes (`srcdoc`, `formaction`, `action`, `xmlns`).
  - Native protection against dangerous pseudo-protocols (`javascript:`, `vbscript:`, `data:text/html`) including whitespace evasion.
  - Reserved attributes protection: prevents overriding `class`, `id`, and `style`.
- **Zero Frontend Overhead ($O(1)$):**
  - Zero additional CSS or JS files enqueued on the frontend.
  - Ultra-fast injection at runtime using native WordPress `WP_HTML_Tag_Processor` with early bailouts.
- **Zero Block Invalidation:** Attributes are stored in block metadata and injected dynamically at render time (`render_block`). If the plugin is deactivated, blocks will never throw Gutenberg invalidation errors (*"This block contains unexpected or invalid content"*).
- **100% Internationalized (i18n):** Default English source strings with bundled complete Spanish (`es_ES`) translation (PO, MO, and Gutenberg JSON script translations).
- **Automated Testing Suite:** 100% test coverage for sanitization, rendering, security protocols, and editor validation.
- **Developer Friendly & Extensible:** PHP filters for capability customization (`mtwdev_custom_attributes_capability`), disallowed attributes (`mtwdev_custom_attributes_disallowed`), and unsupported blocks (`mtwdev_custom_attributes_unsupported_blocks`).

---

## 📋 Requirements

- **WordPress:** 6.0 or higher (6.2+ recommended for native `WP_HTML_Tag_Processor`).
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
3. Activate **MTWDev Custom Block Attributes** in **Plugins > Installed Plugins** (or run `wp plugin activate mtwdev-custom-attributes`).

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

All 62 tests (9 JS tests + 53 PHP tests) run isolated and test sanitization, validation, security vectors, and HTML rendering.

---

## 🌍 Internationalization (i18n)

Generate translation templates and compiled files:

```bash
# Generate POT translation template
pnpm run i18n:pot

# Compile PO/MO and Gutenberg script JSON translations
pnpm run i18n:build
```

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
