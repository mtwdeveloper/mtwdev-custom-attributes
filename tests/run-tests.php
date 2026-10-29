<?php
/**
 * Test Runner for MTWDev Custom Block Attributes.
 */

require_once __DIR__ . '/bootstrap.php';

use function MTWDev\CustomAttributes\sanitize_attribute_name;
use function MTWDev\CustomAttributes\is_safe_attribute_value;
use function MTWDev\CustomAttributes\get_unsupported_blocks;
use function MTWDev\CustomAttributes\get_disallowed_attributes;

$tests_run    = 0;
$tests_passed = 0;
$failures     = [];

function assert_equals($expected, $actual, string $message): void
{
	global $tests_run, $tests_passed, $failures;
	$tests_run++;

	if ($expected === $actual) {
		$tests_passed++;
		echo "  \033[32m✔\033[0m {$message}\n";
	} else {
		$expected_str = \var_export($expected, true);
		$actual_str   = \var_export($actual, true);
		$failures[]   = "FAIL: {$message}\n    Expected: {$expected_str}\n    Actual:   {$actual_str}";
		echo "  \033[31m✖\033[0m {$message}\n";
	}
}

function assert_true(bool $condition, string $message): void
{
	assert_equals(true, $condition, $message);
}

function assert_false(bool $condition, string $message): void
{
	assert_equals(false, $condition, $message);
}

echo "\n--- Starting PHP Unit & Integration Tests ---\n\n";

// -------------------------------------------------------------
// Suite 1: sanitize_attribute_name()
// -------------------------------------------------------------
echo "Suite 1: sanitize_attribute_name()\n";

assert_equals('data-test', sanitize_attribute_name('data-test'), 'Accepts valid data-* attribute');
assert_equals('aria-label', sanitize_attribute_name('ARIA-LABEL'), 'Normalizes uppercase to lowercase');
assert_equals('title', sanitize_attribute_name('  title  '), 'Trims whitespace');
assert_equals('download', sanitize_attribute_name('download'), 'Accepts standard HTML5 attribute');
assert_equals('data-id-123', sanitize_attribute_name('data-id-123'), 'Accepts numbers and hyphens');
assert_equals('data-foo_bar', sanitize_attribute_name('data-foo_bar'), 'Accepts underscores');
assert_equals('xml:lang', sanitize_attribute_name('xml:lang'), 'Accepts colon namespace');

// Invalid characters
assert_equals('', sanitize_attribute_name('data test'), 'Rejects attribute with spaces');
assert_equals('', sanitize_attribute_name('data$test'), 'Rejects special symbols ($)');
assert_equals('', sanitize_attribute_name('data<tag>'), 'Rejects HTML tags (<>)');
assert_equals('', sanitize_attribute_name('key"quotes"'), 'Rejects quotes');

// Event handlers (XSS)
assert_equals('', sanitize_attribute_name('onclick'), 'Rejects onclick');
assert_equals('', sanitize_attribute_name('ONCLICK'), 'Rejects uppercase ONCLICK');
assert_equals('', sanitize_attribute_name('onload'), 'Rejects onload');
assert_equals('', sanitize_attribute_name('onerror'), 'Rejects onerror');
assert_equals('', sanitize_attribute_name('onmouseover'), 'Rejects onmouseover');
assert_equals('', sanitize_attribute_name('onCustomEvent'), 'Rejects onCustomEvent');

// Disallowed & Reserved
assert_equals('', sanitize_attribute_name('class'), 'Rejects reserved "class"');
assert_equals('', sanitize_attribute_name('id'), 'Rejects reserved "id"');
assert_equals('', sanitize_attribute_name('style'), 'Rejects reserved "style"');
assert_equals('', sanitize_attribute_name('srcdoc'), 'Rejects disallowed "srcdoc"');
assert_equals('', sanitize_attribute_name('formaction'), 'Rejects disallowed "formaction"');
assert_equals('', sanitize_attribute_name('action'), 'Rejects disallowed "action"');
assert_equals('', sanitize_attribute_name('xmlns'), 'Rejects disallowed "xmlns"');
assert_equals('', sanitize_attribute_name('xlink:href'), 'Rejects disallowed "xlink:href"');

// Allow list: valid names and prefixes pass
assert_equals('data-track-event', sanitize_attribute_name('data-track-event'), 'Accepts data-* prefix');
assert_equals('aria-describedby', sanitize_attribute_name('aria-describedby'), 'Accepts aria-* prefix');
assert_equals('role', sanitize_attribute_name('role'), 'Accepts role');
assert_equals('tabindex', sanitize_attribute_name('tabindex'), 'Accepts tabindex');
assert_equals('lang', sanitize_attribute_name('lang'), 'Accepts lang');
assert_equals('dir', sanitize_attribute_name('dir'), 'Accepts dir');
assert_equals('inert', sanitize_attribute_name('inert'), 'Accepts inert');
assert_equals('hidden', sanitize_attribute_name('hidden'), 'Accepts hidden');
assert_equals('rel', sanitize_attribute_name('rel'), 'Accepts rel');
assert_equals('target', sanitize_attribute_name('target'), 'Accepts target');
assert_equals('xml:lang', sanitize_attribute_name('xml:lang'), 'Accepts xml:lang');

// Allow list: names outside it are refused
assert_equals('', sanitize_attribute_name('xmlns:xlink'), 'Rejects xmlns:xlink (namespace)');
assert_equals('', sanitize_attribute_name('href'), 'Rejects href (not on allow list)');
assert_equals('', sanitize_attribute_name('src'), 'Rejects src (not on allow list)');
assert_equals('', sanitize_attribute_name('col'), 'Rejects unknown attribute names');
assert_equals('', sanitize_attribute_name('data-'), 'Rejects a bare "data-" prefix');
assert_equals('', sanitize_attribute_name('aria-'), 'Rejects a bare "aria-" prefix');

// -------------------------------------------------------------
// Suite 2: is_safe_attribute_value()
// -------------------------------------------------------------
echo "\nSuite 2: is_safe_attribute_value()\n";

assert_true(is_safe_attribute_value('my-custom-value'), 'Accepts standard string value');
assert_true(is_safe_attribute_value('https://example.com/page'), 'Accepts valid HTTPS URL');
assert_true(is_safe_attribute_value('12345'), 'Accepts numeric value');
assert_true(is_safe_attribute_value(''), 'Accepts empty value');

assert_false(is_safe_attribute_value('javascript:alert(1)'), 'Rejects javascript: pseudo-protocol');
assert_false(is_safe_attribute_value('JAVASCRIPT:alert(1)'), 'Rejects uppercase JAVASCRIPT:');
assert_false(is_safe_attribute_value("java\tscript:alert(1)"), 'Rejects tab evasion in javascript:');
assert_false(is_safe_attribute_value("java\nscript:alert(1)"), 'Rejects newline evasion in javascript:');
assert_false(is_safe_attribute_value("j a v a script:alert(1)"), 'Rejects space evasion in javascript:');
assert_false(is_safe_attribute_value('vbscript:msgbox(1)'), 'Rejects vbscript: pseudo-protocol');
assert_false(is_safe_attribute_value('data:text/html;base64,PHNjcmlwdD4='), 'Rejects data:text/html protocol');
assert_false(is_safe_attribute_value('data:application/javascript;base64,...'), 'Rejects data:application/javascript protocol');

// -------------------------------------------------------------
// Suite 3: get_unsupported_blocks() & get_disallowed_attributes()
// -------------------------------------------------------------
echo "\nSuite 3: Configuration & Hooks\n";

$unsupported = get_unsupported_blocks();
assert_true(\in_array('core/freeform', $unsupported, true), 'Default unsupported includes core/freeform');
assert_true(\in_array('core/html', $unsupported, true), 'Default unsupported includes core/html');
assert_true(\in_array('core/shortcode', $unsupported, true), 'Default unsupported includes core/shortcode');
assert_true(\in_array('core/legacy-widget', $unsupported, true), 'Default unsupported includes core/legacy-widget');

$disallowed = get_disallowed_attributes();
assert_true(\in_array('onclick', $disallowed, true), 'Disallowed attributes include onclick');
assert_true(\in_array('style', $disallowed, true), 'Disallowed attributes include style');
assert_true(\in_array('class', $disallowed, true), 'Disallowed attributes include class');

// -------------------------------------------------------------
// Suite 4: register_block_type_args filter
// -------------------------------------------------------------
echo "\nSuite 4: register_block_type_args filter\n";

$args_p = \apply_filters('register_block_type_args', [], 'core/paragraph');
assert_true(isset($args_p['attributes']['customAttributes']), 'Registers customAttributes for core/paragraph');

$args_html = \apply_filters('register_block_type_args', [], 'core/html');
assert_false(isset($args_html['attributes']['customAttributes']), 'Skips customAttributes for core/html');

// -------------------------------------------------------------
// Suite 5: render_block filter
// -------------------------------------------------------------
echo "\nSuite 5: render_block filter\n";

// Test 5.1: Key-value attribute injection
$block_kv = [
	'blockName' => 'core/paragraph',
	'attrs'     => [
		'customAttributes' => [
			['key' => 'data-analytics', 'value' => 'button-click', 'isBoolean' => false],
		],
	],
];
$rendered_kv = \apply_filters(
	'render_block',
	'<p class="intro">Hello World</p>',
	$block_kv
);
assert_true(
	\strpos($rendered_kv, 'data-analytics="button-click"') !== false,
	'Injects key-value attribute into opening tag'
);

// Test 5.2: Boolean attribute injection (no value)
$block_bool = [
	'blockName' => 'core/group',
	'attrs'     => [
		'customAttributes' => [
			['key' => 'download', 'value' => '', 'isBoolean' => true],
			['key' => 'inert', 'value' => '', 'isBoolean' => true],
		],
	],
];
$rendered_bool = \apply_filters(
	'render_block',
	'<div class="box">Content</div>',
	$block_bool
);
assert_true(
	\strpos($rendered_bool, 'download') !== false && \strpos($rendered_bool, 'inert') !== false,
	'Injects boolean attributes cleanly'
);

// Test 5.3: Dangerous value blocked
$block_xss = [
	'blockName' => 'core/button',
	'attrs'     => [
		'customAttributes' => [
			['key' => 'data-payload', 'value' => 'javascript:alert(1)', 'isBoolean' => false],
		],
	],
];
$rendered_xss = \apply_filters(
	'render_block',
	'<button class="wp-element-button">Click Me</button>',
	$block_xss
);
assert_false(
	\strpos($rendered_xss, 'javascript:'),
	'Blocks dangerous javascript: payload from injection'
);

// Test 5.4: Unsupported block skipped
$block_unsupported = [
	'blockName' => 'core/html',
	'attrs'     => [
		'customAttributes' => [
			['key' => 'data-test', 'value' => 'val', 'isBoolean' => false],
		],
	],
];
$rendered_unsupported = \apply_filters(
	'render_block',
	'<div>Raw HTML</div>',
	$block_unsupported
);
assert_equals('<div>Raw HTML</div>', $rendered_unsupported, 'Leaves unsupported block unmodified');

// Test 5.5: Non-HTML content or empty customAttributes skipped
$block_empty = ['blockName' => 'core/paragraph', 'attrs' => []];
assert_equals(
	'Just plain text without HTML',
	\apply_filters('render_block', 'Just plain text without HTML', $block_empty),
	'Bails early on non-HTML content'
);

// -------------------------------------------------------------
// Suite 6: content_save_pre filter (Security Hardening)
// -------------------------------------------------------------
echo "\nSuite 6: content_save_pre filter\n";

global $wp_test_current_user_caps, $wp_test_is_user_logged_in;

$content_with_attrs = '<!-- wp:paragraph {"align":"left","customAttributes":[{"key":"data-id","value":"1","isBoolean":false}]} --><p>Text</p><!-- /wp:paragraph -->';

// User with the capability: content untouched
$wp_test_is_user_logged_in = true;
$wp_test_current_user_caps = ['unfiltered_html' => true];
// 'content_save_pre' receives and returns slashed data, like core's own kses filters.
assert_equals(
	$content_with_attrs,
	\wp_unslash(\apply_filters('content_save_pre', \wp_slash($content_with_attrs))),
	'Preserves customAttributes when user has unfiltered_html capability'
);

// User without the capability: attributes stripped, rest of the block intact
$wp_test_current_user_caps = ['unfiltered_html' => false];
$filtered                   = \wp_unslash(\apply_filters('content_save_pre', \wp_slash($content_with_attrs)));
$parsed_filtered            = \parse_blocks($filtered);

assert_false(
	\strpos($filtered, 'customAttributes') !== false,
	'Strips customAttributes when user lacks capability'
);
assert_equals('left', $parsed_filtered[0]['attrs']['align'] ?? null, 'Keeps other block attributes intact');
assert_equals('<p>Text</p>', $parsed_filtered[0]['innerHTML'] ?? null, 'Keeps block markup intact');

// Regression: a value containing "\]" used to corrupt the JSON (old pre_kses regex)
$content_brackets = '<!-- wp:paragraph {"customAttributes":[{"key":"data-cfg","value":"[1,2]","isBoolean":false},{"key":"title","value":"x","isBoolean":false}]} --><p>x</p><!-- /wp:paragraph -->';
$filtered_brackets = \wp_unslash(\apply_filters('content_save_pre', \wp_slash($content_brackets)));
$parsed_brackets   = \parse_blocks($filtered_brackets);

assert_true(\is_array($parsed_brackets[0]['attrs'] ?? null), 'Keeps block JSON valid when a value contains "]"');
assert_false(\strpos($filtered_brackets, 'isBoolean') !== false, 'Strips every entry, leaving no orphan JSON fragments');

// Inner blocks are sanitized as well
$content_nested = '<!-- wp:group {"customAttributes":[{"key":"data-x","value":"1","isBoolean":false}]} --><div class="wp-block-group"><!-- wp:paragraph {"customAttributes":[{"key":"data-y","value":"2","isBoolean":false}]} --><p>z</p><!-- /wp:paragraph --></div><!-- /wp:group -->';
$filtered_nested = \wp_unslash(\apply_filters('content_save_pre', \wp_slash($content_nested)));
assert_false(
	\strpos($filtered_nested, 'customAttributes') !== false,
	'Strips customAttributes in nested inner blocks'
);

// Programmatic saves without a logged-in user (WP-CLI, imports, cron)
$wp_test_is_user_logged_in = false;
assert_equals(
	$content_with_attrs,
	\wp_unslash(\apply_filters('content_save_pre', \wp_slash($content_with_attrs))),
	'Leaves programmatic saves without a logged-in user untouched'
);
$wp_test_is_user_logged_in = true;

// Content without block markup short-circuits
assert_equals(
	'<p>Hello</p>',
	\apply_filters('content_save_pre', '<p>Hello</p>'),
	'Bails early on content without block markup'
);

// -------------------------------------------------------------
// Test Summary
// -------------------------------------------------------------
echo "\n============================================\n";
echo "Tests Run:    {$tests_run}\n";
echo "Tests Passed: {$tests_passed}\n";
echo "Tests Failed: " . \count($failures) . "\n";
echo "============================================\n";

if (!empty($failures)) {
	echo "\nFailure Details:\n";
	foreach ($failures as $failure) {
		echo "{$failure}\n\n";
	}
	exit(1);
}

echo "\nAll PHP tests passed successfully!\n\n";
exit(0);
