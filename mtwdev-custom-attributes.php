<?php
/**
 * Plugin Name:       MTWDev Custom Block Attributes
 * Plugin URI:        https://github.com/mtwdeveloper/mtwdev-custom-attributes
 * Description:       Easily add custom HTML attributes (data-*, aria-*, boolean, and key-value pairs) to any Gutenberg block with active security, quick presets, and native rendering.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            MTWdev
 * Author URI:        https://github.com/mtwdeveloper
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mtwdev-custom-attributes
 * Domain Path:       /languages
 *
 * @package           MTWDev\CustomAttributes
 */

namespace MTWDev\CustomAttributes;

// Exit if accessed directly to prevent arbitrary execution.
if (!\defined('ABSPATH')) {
	exit;
}

// Plugin constants.
\define('MTWDEV_CUSTOM_ATTRIBUTES_VERSION', '1.0.0');
\define('MTWDEV_CUSTOM_ATTRIBUTES_DIR', \plugin_dir_path(__FILE__));
\define('MTWDEV_CUSTOM_ATTRIBUTES_URL', \plugin_dir_url(__FILE__));
\define('MTWDEV_CUSTOM_ATTRIBUTES_BASENAME', \plugin_basename(__FILE__));

/**
 * Loads the plugin textdomain for translations.
 */
\add_action('init', function (): void {
	\load_plugin_textdomain(
		'mtwdev-custom-attributes',
		false,
		\dirname(\plugin_basename(__FILE__)) . '/languages'
	);
});

/**
 * Returns the list of block slugs where custom HTML attributes should not be attached.
 *
 * @return array<string> Incompatible block slugs.
 */
function get_unsupported_blocks(): array
{
	$unsupported = [
		'core/freeform',      // Classic editor within blocks
		'core/html',          // Direct raw custom HTML block
		'core/shortcode',     // Shortcode block
		'core/legacy-widget', // Legacy widgets
	];

	/**
	 * Filters the list of unsupported blocks.
	 *
	 * @param array $unsupported List of unsupported block slugs.
	 */
	return (array) \apply_filters('mtwdev_custom_attributes_unsupported_blocks', $unsupported);
}

/**
 * Returns the list of disallowed HTML attribute names for security and markup integrity.
 *
 * Prevents inline event handlers (on*), style injections, ID conflicts, and executable attributes.
 *
 * @return array<string> List of disallowed attribute names.
 */
function get_disallowed_attributes(): array
{
	$disallowed = [
		// DOM event handlers (inline script execution)
		'onabort', 'onafterprint', 'onbeforeprint', 'onbeforeunload', 'onblur',
		'oncanplay', 'oncanplaythrough', 'onchange', 'onclick', 'oncontextmenu',
		'oncopy', 'oncuechange', 'oncut', 'ondblclick', 'ondrag', 'ondragend',
		'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop',
		'ondurationchange', 'onemptied', 'onended', 'onerror', 'onfocus',
		'onhashchange', 'oninput', 'oninvalid', 'onkeydown', 'onkeypress',
		'onkeyup', 'onload', 'onloadeddata', 'onloadedmetadata', 'onloadstart',
		'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup',
		'onmousewheel', 'onoffline', 'ononline', 'onpagehide', 'onpageshow',
		'onpaste', 'onpause', 'onplay', 'onplaying', 'onpopstate', 'onprogress',
		'onratechange', 'onreset', 'onresize', 'onscroll', 'onsearch', 'onseeked',
		'onseeking', 'onselect', 'onstalled', 'onstorage', 'onsubmit', 'onsuspend',
		'ontimeupdate', 'ontoggle', 'onunload', 'onvolumechange', 'onwaiting',
		'onwheel',
		// Executable / structural / reserved attributes
		'srcdoc', 'formaction', 'action', 'xmlns', 'xlink:href', 'poster',
		'style', 'class', 'id',
	];

	/**
	 * Filters the list of disallowed HTML attributes.
	 *
	 * @param array $disallowed List of disallowed attribute keys.
	 */
	return (array) \apply_filters('mtwdev_custom_attributes_disallowed', $disallowed);
}

/**
 * Strictly sanitizes and validates an HTML attribute name.
 *
 * @param string $key Attribute key to validate.
 * @return string Sanitized attribute key, or empty string if invalid/disallowed.
 */
function sanitize_attribute_name(string $key): string
{
	$clean_key = \strtolower(\trim($key));

	// Only allow valid HTML5 attribute characters (letters, digits, hyphen, underscore, colon)
	if (!\preg_match('/^[a-z0-9_\-:]+$/', $clean_key)) {
		return '';
	}

	// Disallow event handlers starting with 'on' and attributes in the disallowed list
	if (\strpos($clean_key, 'on') === 0 || \in_array($clean_key, get_disallowed_attributes(), true)) {
		return '';
	}

	return $clean_key;
}

/**
 * Validates whether an attribute value is safe from dangerous pseudo-protocols.
 *
 * Mitigates XSS vectors using whitespace/control-character evasion in protocols.
 *
 * @param string $value Attribute value to inspect.
 * @return bool True if safe, false if potentially dangerous.
 */
function is_safe_attribute_value(string $value): bool
{
	if ($value === '') {
		return true;
	}

	// Remove whitespace and control characters before inspecting pseudo-protocols
	$normalized = (string) \preg_replace('/[\x00-\x20\s]+/u', '', \strtolower($value));

	$dangerous_protocols = [
		'javascript:',
		'vbscript:',
		'data:text/html',
		'data:application/javascript',
		'data:application/x-javascript',
	];

	foreach ($dangerous_protocols as $protocol) {
		if (\strpos($normalized, $protocol) !== false) {
			return false;
		}
	}

	return true;
}

/**
 * 1. Registers the 'customAttributes' attribute in the server-side definition for supported blocks.
 */
\add_filter('register_block_type_args', function (array $args, string $name): array {
	if (\in_array($name, get_unsupported_blocks(), true)) {
		return $args;
	}

	if (!isset($args['attributes'])) {
		$args['attributes'] = [];
	}

	$args['attributes']['customAttributes'] = [
		'type'    => 'array',
		'default' => [],
		'items'   => [
			'type' => 'object',
		],
	];

	return $args;
}, 10, 2);

/**
 * 2. Injects validated custom attributes into the opening HTML tag of the block on frontend.
 *
 * Uses WP_HTML_Tag_Processor (WordPress 6.2+ standard) for accurate, safe, and fast injection.
 */
\add_filter('render_block', function (string $block_content, array $block): string {
	// Early bailout: skip if in admin, empty content, no HTML tag, or no customAttributes defined
	if (
		\is_admin() ||
		empty($block_content) ||
		empty($block['attrs']['customAttributes']) ||
		!\is_array($block['attrs']['customAttributes']) ||
		\strpos($block_content, '<') === false
	) {
		return $block_content;
	}

	$block_name = $block['blockName'] ?? '';
	if (!empty($block_name) && \in_array($block_name, get_unsupported_blocks(), true)) {
		return $block_content;
	}

	// Filter and validate attributes first to avoid unnecessary HTML processing
	$valid_attributes = [];
	foreach ($block['attrs']['customAttributes'] as $attr) {
		if (empty($attr['key']) || !\is_string($attr['key'])) {
			continue;
		}

		$key = sanitize_attribute_name($attr['key']);
		if ($key === '') {
			continue;
		}

		$is_boolean = !empty($attr['isBoolean']);
		$value      = isset($attr['value']) ? \trim((string) $attr['value']) : '';

		if (!$is_boolean && $value !== '' && !is_safe_attribute_value($value)) {
			continue;
		}

		$valid_attributes[] = [
			'key'        => $key,
			'value'      => $value,
			'is_boolean' => $is_boolean,
		];
	}

	if (empty($valid_attributes)) {
		return $block_content;
	}

	// Preferred high-performance method: WP_HTML_Tag_Processor (WordPress 6.2+)
	if (\class_exists(\WP_HTML_Tag_Processor::class)) {
		$processor = new \WP_HTML_Tag_Processor($block_content);

		if ($processor->next_tag()) {
			foreach ($valid_attributes as $attr) {
				if ($attr['is_boolean'] || $attr['value'] === '') {
					$processor->set_attribute($attr['key'], true);
				} else {
					$processor->set_attribute($attr['key'], $attr['value']);
				}
			}

			return $processor->get_updated_html();
		}

		return $block_content;
	}

	// Fallback regex for environments earlier than WordPress 6.2
	$html_to_inject = '';
	foreach ($valid_attributes as $attr) {
		if ($attr['is_boolean'] || $attr['value'] === '') {
			$html_to_inject .= ' ' . $attr['key'];
		} else {
			$html_to_inject .= ' ' . $attr['key'] . '="' . \esc_attr($attr['value']) . '"';
		}
	}

	if ($html_to_inject !== '') {
		$block_content = (string) \preg_replace('/<([a-z0-9\-]+)(\s|>)/i', '<$1' . $html_to_inject . '$2', $block_content, 1);
	}

	return $block_content;
}, 10, 2);

/**
 * 3. Security hardening: if the current user lacks the required capability,
 * strip customAttributes to prevent unauthorized attribute injection.
 */
\add_filter('pre_kses', function (string $content): string {
	if (\strpos($content, '<!-- wp:') === false || \strpos($content, 'customAttributes') === false) {
		return $content;
	}

	/**
	 * Filters the capability required to edit and persist custom HTML attributes.
	 *
	 * Default: 'unfiltered_html'.
	 *
	 * @param string $capability WordPress capability.
	 */
	$required_cap = (string) \apply_filters('mtwdev_custom_attributes_capability', 'unfiltered_html');
	if (\current_user_can($required_cap)) {
		return $content;
	}

	// Safely clear customAttributes array inside block comment JSON without breaking syntax
	return (string) \preg_replace(
		'/("customAttributes"\s*:\s*\[)[^\]]*(\])/',
		'$1$2',
		$content
	);
});

/**
 * 4. Enqueues editor scripts and styles in Gutenberg.
 */
\add_action('enqueue_block_editor_assets', function (): void {
	$asset_file = MTWDEV_CUSTOM_ATTRIBUTES_DIR . 'build/index.asset.php';

	if (!\file_exists($asset_file)) {
		return;
	}

	$asset = include $asset_file;

	\wp_enqueue_script(
		'mtwdev-custom-attributes-editor',
		MTWDEV_CUSTOM_ATTRIBUTES_URL . 'build/index.js',
		$asset['dependencies'] ?? ['wp-block-editor', 'wp-components', 'wp-compose', 'wp-element', 'wp-hooks', 'wp-i18n'],
		$asset['version'] ?? MTWDEV_CUSTOM_ATTRIBUTES_VERSION,
		true
	);

	// Enqueue editor stylesheet if compiled
	if (\file_exists(MTWDEV_CUSTOM_ATTRIBUTES_DIR . 'build/index.css')) {
		\wp_enqueue_style(
			'mtwdev-custom-attributes-editor',
			MTWDEV_CUSTOM_ATTRIBUTES_URL . 'build/index.css',
			['wp-components'],
			$asset['version'] ?? MTWDEV_CUSTOM_ATTRIBUTES_VERSION
		);
	}

	/**
	 * Resolve user capability dynamically so both PHP and React editor UI stay in sync.
	 */
	$required_cap = (string) \apply_filters('mtwdev_custom_attributes_capability', 'unfiltered_html');

	\wp_localize_script(
		'mtwdev-custom-attributes-editor',
		'mtwdevCustomAttributesData',
		[
			'unsupportedBlocks'        => get_unsupported_blocks(),
			'canUserUseUnfilteredHTML' => \current_user_can($required_cap),
		]
	);

	// Register script translations with WordPress core
	\wp_set_script_translations(
		'mtwdev-custom-attributes-editor',
		'mtwdev-custom-attributes',
		MTWDEV_CUSTOM_ATTRIBUTES_DIR . 'languages'
	);
});

