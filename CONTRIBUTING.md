# Contributing to MTWDev Custom Block Attributes

*Read this in other languages:* [English](CONTRIBUTING.md) | [Español](CONTRIBUTING.es.md)

Thank you for your interest in contributing to **MTWDev Custom Block Attributes**! This project is open source and we warmly welcome community feedback, bug reports, and code contributions.

To maintain a clean, consistent, and high-quality codebase, please review and follow these guidelines before contributing.

---

## 🛠️ Development Environment & Workflow

### 1. Prerequisites

* **Node.js:** v20.x or higher.
* **pnpm:** Recommended package manager (`npm install -g pnpm`).
* **PHP:** 7.4 or higher (PHP 8.2+ recommended).
* **WordPress:** Local environment running WordPress 6.2+ (Laravel Herd, LocalWP, or similar).

### 2. Pull Request Workflow

1. **Fork** the repository to your own GitHub account.
2. **Clone your fork** into your local WordPress `wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/YOUR-USERNAME/wp-custom-attributes.git mtwdev-custom-attributes
   cd mtwdev-custom-attributes
   ```
3. **Create a descriptive branch** for your work:
   ```bash
   git checkout -b feature/your-feature-name
   # or for bugfixes:
   git checkout -b fix/issue-description
   ```
4. **Install dependencies** using `pnpm`:
   ```bash
   pnpm install
   ```
5. **Start development mode** (with hot reload/watch):
   ```bash
   pnpm run start
   ```
6. **Make your changes** adhering to the coding standards below.
7. **Build for production**:
   ```bash
   pnpm run build
   ```
   > ⚠️ **Important:** Always run `pnpm run build` and ensure there are no syntax or build errors before committing.
8. **Push your changes** to your fork and submit a **Pull Request (PR)** to the `main` branch of the upstream repository, detailing what your PR changes and how to test it.

---

## 📏 Coding Standards & Guidelines

To ensure project stability and maintainability, all contributions must follow these guidelines:

### PHP & WordPress
* Follow the official **WordPress Coding Standards (WPCS)**.
* Use strict typing for function parameters and return types where applicable.
* **Security First:**
  * Always sanitize input using native WordPress functions (`sanitize_text_field`, `sanitize_key`, etc.).
  * Validate attribute names before persisting or rendering.
  * Always escape output with `esc_attr()` or rely on `WP_HTML_Tag_Processor`.
  * Never allow inline JavaScript event handlers (`on*`) or `javascript:` pseudo-protocols.
* **Performance:**
  * Do not enqueue frontend assets unless strictly necessary. DOM injection must run at render time with $O(1)$ efficiency.

### JavaScript & Gutenberg
* Write modern standard **React / JSX**.
* Use official WordPress packages (`@wordpress/block-editor`, `@wordpress/components`, `@wordpress/compose`, `@wordpress/element`, `@wordpress/hooks`, `@wordpress/i18n`).
* **Do not** manually call `wp.element.createElement` in source files under `src/`.
* Wrap all UI strings in internationalization functions: `__('...', 'mtwdev-custom-attributes')`.

---

## 🐛 Reporting Bugs

If you find a bug:

1. Check the [Issues](https://github.com/GWKaplan/wp-custom-attributes/issues) tab to see if it has already been reported.
2. Open a new issue with a clear and concise title.
3. Provide:
   * **Problem description:** What you expected to happen vs what actually happened.
   * **Steps to reproduce the issue.**
   * **Environment details:** WordPress version, PHP version, active theme, and browser.
   * **Screenshots or console errors** if relevant.

---

## 💡 Suggesting Enhancements

If you have an idea to improve the plugin:
* Open an issue or discussion first to share your proposal before investing time writing code.
* This ensures that the idea aligns with the plugin's goals of lightweight architecture and strict security.

---

## 🤝 Code of Conduct

We expect all contributors to adhere to standard community conduct:
* **Respectful & Professional:** Treat everyone with courtesy and empathy.
* **Constructive:** Focus on helpful, technically sound feedback.
* **Inclusive:** Welcome contributors of all background and experience levels.
