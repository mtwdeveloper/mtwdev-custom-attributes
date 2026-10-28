# WordPress.org Plugin Repository Compliance & Audit Report

**Plugin:** Custom Block Attributes (`mtwdev-custom-attributes`)
**Version:** 1.0.0
**Author:** Marco Torres Walker (`mtwdev`)

---

## Executive Summary

The plugin **Custom Block Attributes** is in **excellent shape** and follows modern WordPress development best practices. The code uses native Gutenberg APIs, modern performance mechanisms like `WP_HTML_Tag_Processor`, strict security filtering, and zero frontend bloat.

To ensure **100% approval on the official WordPress.org Plugin Directory** during manual review, this report outlines recommendations and guidelines regarding plugin slug selection, version header alignment, security, licensing, internationalization, and SVN deployment.

---

## 1. Plugin Slug & Naming Best Practices

### Current Slug
* `mtwdev-custom-attributes`

### Best Practice & Recommendations
1. **Uniqueness & Brand Guidelines:**
   * WordPress.org allows developer prefixes (like `mtwdev-`) in plugin slugs. It is acceptable and prevents slug collisions.
   * If you prefer a cleaner, generic slug for public discovery, consider:
     * `custom-block-attributes`
     * `custom-attributes-for-gutenberg`
     * `block-custom-attributes`
2. **Slug Consistency Requirement:**
   * Once approved on WordPress.org, **the slug cannot be changed**.
   * The text domain (`mtwdev-custom-attributes`), the folder name, and the main PHP filename (`mtwdev-custom-attributes.php`) should match the chosen repository slug.

---

## 2. Version Verification & Compatibility

### Verified Settings
* **Requires at least:** `6.0` (Verified: `WP_HTML_Tag_Processor` requires WP 6.2+, but the code includes a safe regex fallback for WP 6.0–6.1).
* **Tested up to:** `6.8` (WordPress core current major version line; `readme.txt` properly specifies `6.8`).
* **Requires PHP:** `7.4` (Verified: PHP code uses scalar type hints and void return types supported in PHP 7.4+).

### Compliance Note
Headers across `mtwdev-custom-attributes.php` and `readme.txt` match and adhere to WordPress standards:
```text
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
```

---

## 3. Detailed Code Compliance Audit

### A. Security & XSS Prevention (Rule Compliance: PASS)
* **Inline JS Event Handler Blocking:** Cleanly blocks `on*` attribute injection (`onclick`, `onload`, `onerror`, etc.).
* **Pseudo-protocol Sanitization:** `is_safe_attribute_value()` strips control characters and blocks `javascript:`, `vbscript:`, and dangerous `data:` URLs.
* **Reserved Attributes:** Prevents overriding core layout/style attributes (`class`, `id`, `style`).
* **Capability Control:** `pre_kses` hook strips `customAttributes` if the editing user lacks `unfiltered_html` capability (or custom cap set via `mtwdev_custom_attributes_capability`).

### B. Function & Global Namespace Prefixing (Rule Compliance: PASS)
* All PHP code uses PHP namespaces (`namespace MTWDev\CustomAttributes;`).
* Core constants and functions use `MTWDEV_CUSTOM_ATTRIBUTES_` and `mtwdev_` prefixes, preventing collisions with other plugins or core functions.

### C. Internationalization (i18n) (Rule Compliance: PASS)
* Text domain matches the plugin directory name (`mtwdev-custom-attributes`).
* Translation strings in PHP and JS use standard gettext functions (`__`, `sprintf`).
* English base strings and Spanish (`es_ES`) PO/MO files are properly structured in `/languages/`.

### D. External Dependencies & Remote Calls (Rule Compliance: PASS)
* No external tracking scripts, remote CDNs, or phone-home calls are present.
* Build artifacts are compiled locally.

---

## 4. Minor Recommended Enhancements Before SVN Upload

1. **Include Built Assets in SVN Release:**
   * Ensure `build/index.js`, `build/index.asset.php`, and `languages/*.mo` / `languages/*.json` are committed to SVN (`/trunk`), as WordPress.org SVN does not run `npm run build` or `pnpm build` during deployment.
2. **Repository Exclusions:**
   * Non-essential development files (`package.json`, `pnpm-lock.yaml`, `node_modules`, `src/`, `tests/`) can be excluded from the production zip using `.gitattributes` or `.distignore` if using automated release tools (e.g. GitHub Actions `10up/action-wordpress-plugin-deploy`).

---

## Summary Checklist for WordPress.org Submission

| Requirement | Status | Notes |
| :--- | :---: | :--- |
| **GPL License** | ✅ Passed | GPL-2.0-or-later declared in PHP & `readme.txt` |
| **Header Fields** | ✅ Passed | All required headers present and valid |
| **Security & Sanitization** | ✅ Passed | `WP_HTML_Tag_Processor` & input checks active |
| **Prefix / Namespacing** | ✅ Passed | PHP namespace & unique constant prefixes used |
| **No Remote Enqueues** | ✅ Passed | Zero third-party CDN scripts loaded |
| **Text Domain Match** | ✅ Passed | `mtwdev-custom-attributes` domain used |
| **PHP & WP Compatibility** | ✅ Passed | WP 6.0+ / PHP 7.4+ verified |
