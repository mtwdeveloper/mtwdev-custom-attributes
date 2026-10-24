# MTWDev Custom Block Attributes

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Updates](https://img.shields.io/badge/Updates-GitHub%20Direct-orange.svg)](https://github.com/GWKaplan/wp-custom-attributes)

*Read this in other languages:* [English](README.md) | [Español](README.es.md)

**MTWDev Custom Block Attributes** is a lightweight, secure, and developer-friendly WordPress plugin that lets site editors and developers inject custom HTML attributes (`data-*`, `aria-*`, `title`, boolean attributes like `download` or `inert`, and custom key-value pairs) into **any Gutenberg block** directly from the block editor.

---

## 🚀 Key Features

- **Native Block Editor Integration:** Neatly embedded within the official **Advanced** panel in the block settings sidebar.
- **Boolean & Key-Value Support:**
  - Key-value attributes: `data-user-id="105"`, `aria-label="Close modal"`, `title="Information"`.
  - Clean boolean/valueless attributes: `download`, `inert`, `novalidate`, `required`.
- **Quick Presets Bar:** One-click shortcuts for commonly used attributes (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
- **Robust Security & XSS Mitigation:**
  - Strict HTML5 attribute name validation (`/^[a-z0-9_\-:]+$/`).
  - Automatic blocking of inline DOM events (`onclick`, `onload`, `on*`) and executable attributes (`srcdoc`, `formaction`).
  - Safe output escaping using `esc_attr()` and stripping of `javascript:` pseudo-protocols.
- **Zero Frontend Overhead ($O(1)$):**
  - Zero additional CSS or JS files enqueued on the frontend.
  - Ultra-fast injection at runtime using the native WordPress 6.2+ `WP_HTML_Tag_Processor` API.
- **Zero Block Invalidation:** Attributes are stored in block metadata and injected dynamically at render time (`render_block`). If the plugin is ever deactivated, your blocks will **never** throw Gutenberg invalidation errors (*"This block contains unexpected or invalid content"*).
- **Automatic GitHub Updates:** Integrated *Plugin Update Checker* (PUC v5.7) for automatic updates directly from GitHub releases or the `main` branch.
- **Capability Control (`unfiltered_html`):** Native permission checks for multi-author environments and WordPress Multisite.
- **Developer Friendly & Extensible:** PHP filters to customize disallowed attributes (`mtwdev_custom_attributes_disallowed`) and unsupported blocks (`mtwdev_custom_attributes_unsupported_blocks`).

---

## 📋 Requirements

- **WordPress:** 6.0 or higher (6.2+ recommended for native `WP_HTML_Tag_Processor`).
- **PHP:** 7.4 or higher (fully compatible with PHP 8.0, 8.1, 8.2, and 8.3).
- **Gutenberg Block Editor:** Enabled.

---

## 📦 Installation

### Method 1: Direct Download (ZIP)
1. Download the latest `.zip` from [Releases](https://github.com/GWKaplan/wp-custom-attributes/releases) or click the green **Code > Download ZIP** button.
2. In your WordPress admin dashboard, navigate to **Plugins > Add New > Upload Plugin**.
3. Choose the downloaded ZIP file and click **Install Now**.
4. Click **Activate Plugin**.
5. You're set! Future updates will appear automatically in your WordPress dashboard.

### Method 2: Git Clone (Developers)
1. Clone the repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/GWKaplan/wp-custom-attributes.git mtwdev-custom-attributes
   ```
2. Install dependencies and build the editor assets:
   ```bash
   cd mtwdev-custom-attributes
   pnpm install
   pnpm run build
   ```
3. Activate **MTWDev Custom Block Attributes** in **Plugins > Installed Plugins** (or run `wp plugin activate mtwdev-custom-attributes`).

---

## 🛠️ How to Use

1. Open any post, page, or template in the **Block Editor (Gutenberg)**.
2. Select any block you wish to customize (Button, Group, Image, Paragraph, Heading, etc.).
3. In the right-hand sidebar, expand the **Advanced** panel.
4. Under **Custom HTML Attributes**:
   - Click any **Quick Preset** button (e.g., `+data-*` or `+download`) or type the attribute name manually.
   - If it is a valueless boolean attribute (such as `download` or `inert`), toggle **Simple / boolean attribute**.
   - Click **Add**.
   - If it is not boolean, enter the desired value in the input field.
5. Save or publish your content. The attribute will be rendered cleanly on the root tag of the block in the frontend.

---

## 🛡️ Security & Best Practices

This plugin adheres strictly to the WordPress Coding Standards (WPCS):

1. **Attribute Blacklist:** Denies all event handlers starting with `on` (`onclick`, `onerror`, `onload`, etc.) and code execution vectors (`srcdoc`, `formaction`).
2. **Sanitization & Escaping:** Attribute keys are strictly sanitized and values are escaped with `esc_attr()`.
3. **Protocol Sanitization:** Automatically blocks values containing `javascript:`.
4. **Capability Checks:** Users lacking `unfiltered_html` capabilities cannot add or edit attributes, and content is safely sanitized via the `pre_kses` filter.

---

## 🔌 Developer API & Filters

### Customizing the Disallowed Attributes List
```php
add_filter('mtwdev_custom_attributes_disallowed', function (array $disallowed): array {
    // Add custom restricted attributes according to your project policy
    $disallowed[] = 'custom-restricted-attr';
    return $disallowed;
});
```

### Customizing Unsupported Blocks
```php
add_filter('mtwdev_custom_attributes_unsupported_blocks', function (array $blocks): array {
    $blocks[] = 'my-namespace/custom-block';
    return $blocks;
});
```

---

## 💻 Development & Build Scripts

Built with `@wordpress/scripts` for modern React/JSX and SCSS compilation:

```bash
# Install dependencies
pnpm install

# Watch mode for active development
pnpm run start

# Production build
pnpm run build
```

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) before submitting a Pull Request.

---

## 📄 License

This project is licensed under the **GPL-2.0-or-later** license. See [LICENSE](LICENSE) or visit [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html) for details.
