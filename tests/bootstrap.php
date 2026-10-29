<?php
/**
 * PHP Test Bootstrap for MTWDev Custom Block Attributes.
 *
 * All WordPress mock functions must live in the global namespace.
 */

if (!\defined('ABSPATH')) {
	\define('ABSPATH', \dirname(__DIR__, 4) . '/');
}

if (!\defined('WP_PLUGIN_DIR')) {
	\define('WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins');
}

// Simple in-memory hook registry for WordPress action/filter testing
global $wp_test_filters, $wp_test_current_user_caps, $wp_test_is_admin, $wp_test_is_user_logged_in;
$wp_test_filters             = [];
$wp_test_current_user_caps   = ['unfiltered_html' => true];
$wp_test_is_admin            = false;
$wp_test_is_user_logged_in   = true;

if (!\function_exists('add_filter')) {
	function add_filter(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool
	{
		global $wp_test_filters;
		$wp_test_filters[$tag][] = [
			'callback'      => $function_to_add,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
		return true;
	}
}

if (!\function_exists('add_action')) {
	function add_action(string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1): bool
	{
		return add_filter($tag, $function_to_add, $priority, $accepted_args);
	}
}

if (!\function_exists('apply_filters')) {
	function apply_filters(string $tag, $value, ...$args)
	{
		global $wp_test_filters;
		if (empty($wp_test_filters[$tag])) {
			return $value;
		}

		foreach ($wp_test_filters[$tag] as $filter) {
			$cb_args = \array_merge([$value], $args);
			$cb_args = \array_slice($cb_args, 0, $filter['accepted_args']);
			$value   = \call_user_func_array($filter['callback'], $cb_args);
		}

		return $value;
	}
}

if (!\function_exists('is_admin')) {
	function is_admin(): bool
	{
		global $wp_test_is_admin;
		return (bool) $wp_test_is_admin;
	}
}

if (!\function_exists('current_user_can')) {
	function current_user_can(string $capability): bool
	{
		global $wp_test_current_user_caps;
		return !empty($wp_test_current_user_caps[$capability]);
	}
}

if (!\function_exists('esc_attr')) {
	function esc_attr(string $text): string
	{
		return \htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}
}

if (!\function_exists('plugin_dir_path')) {
	function plugin_dir_path(string $file): string
	{
		return \rtrim(\dirname($file), '/\\') . '/';
	}
}

if (!\function_exists('plugin_dir_url')) {
	function plugin_dir_url(string $file): string
	{
		return 'https://example.com/wp-content/plugins/mtwdev-custom-attributes/';
	}
}

if (!\function_exists('plugin_basename')) {
	function plugin_basename(string $file): string
	{
		return \basename(\dirname($file)) . '/' . \basename($file);
	}
}

if (!\function_exists('load_plugin_textdomain')) {
	function load_plugin_textdomain(string $domain, $deprecated = false, $plugin_rel_path = false): bool
	{
		return true;
	}
}

if (!\function_exists('wp_set_script_translations')) {
	function wp_set_script_translations(string $handle, string $domain = 'default', ?string $path = null): bool
	{
		return true;
	}
}

if (!\function_exists('wp_enqueue_script')) {
	function wp_enqueue_script(...$args): void {}
}

if (!\function_exists('wp_enqueue_style')) {
	function wp_enqueue_style(...$args): void {}
}

if (!\function_exists('wp_localize_script')) {
	function wp_localize_script(...$args): void {}
}

// Load UTF8 helper for HTML Tag Processor
if (\file_exists(ABSPATH . 'wp-includes/utf8.php')) {
	require_once ABSPATH . 'wp-includes/utf8.php';
}

if (!\function_exists('wp_has_noncharacters')) {
	function wp_has_noncharacters(string $text): bool
	{
		return false;
	}
}

if (!\function_exists('wp_kses_uri_attributes')) {
	function wp_kses_uri_attributes(): array
	{
		return [
			'action', 'archive', 'background', 'cite', 'classid', 'codebase',
			'data', 'formaction', 'href', 'icon', 'longdesc', 'manifest',
			'poster', 'profile', 'src', 'usemap', 'xmlns',
		];
	}
}

// Load real WordPress HTML API classes if available
$html_api_files = [
	'class-wp-html-span.php',
	'class-wp-html-text-replacement.php',
	'class-wp-html-token.php',
	'class-wp-html-attribute-token.php',
	'class-wp-html-decoder.php',
	'class-wp-html-tag-processor.php',
];

foreach ($html_api_files as $file) {
	$path = ABSPATH . 'wp-includes/html-api/' . $file;
	if (\file_exists($path)) {
		require_once $path;
	}
}

// Slashing helpers used by the save-time sanitizer.
if (!\function_exists('wp_unslash')) {
	function wp_unslash($value)
	{
		return \is_string($value) ? \stripslashes($value) : $value;
	}
}

if (!\function_exists('wp_slash')) {
	function wp_slash($value)
	{
		return \is_string($value) ? \addslashes($value) : $value;
	}
}

if (!\function_exists('is_user_logged_in')) {
	function is_user_logged_in(): bool
	{
		global $wp_test_is_user_logged_in;
		return (bool) $wp_test_is_user_logged_in;
	}
}

// Real block parser and serializer from core, so the save filter is tested
// against WordPress' own block comment handling and not against a mock.
require_once ABSPATH . 'wp-includes/class-wp-block-parser.php';

foreach (['class-wp-block-parser-block.php', 'class-wp-block-parser-frame.php'] as $parser_file) {
	$parser_path = ABSPATH . 'wp-includes/' . $parser_file;
	if (\file_exists($parser_path)) {
		require_once $parser_path;
	}
}

if (!\function_exists('wp_json_encode')) {
	function wp_json_encode($data, $options = 0, $depth = 512)
	{
		return \json_encode($data, $options, $depth);
	}
}

require_once ABSPATH . 'wp-includes/blocks.php';

// Load plugin main file
require_once \dirname(__DIR__) . '/mtwdev-custom-attributes.php';
