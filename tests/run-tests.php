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
assert_equals('custom_attr', sanitize_attribute_name('custom_attr'), 'Accepts underscores');
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
// Suite 6: pre_kses filter (Security Hardening)
// -------------------------------------------------------------
echo "\nSuite 6: pre_kses filter\n";

global $wp_test_current_user_caps;

// When user has capability (e.g. unfiltered_html)
$wp_test_current_user_caps = ['unfiltered_html' => true];
$content_with_attrs = '<!-- wp:paragraph {"customAttributes":[{"key":"data-id","value":"1"}]} --><p>Text</p><!-- /wp:paragraph -->';
$filtered_authorized = \apply_filters('pre_kses', $content_with_attrs);
assert_equals(
	$content_with_attrs,
	$filtered_authorized,
	'Preserves customAttributes when user has unfiltered_html capability'
);

// When user lacks capability
$wp_test_current_user_caps = ['unfiltered_html' => false];
$filtered_unauthorized = \apply_filters('pre_kses', $content_with_attrs);
assert_true(
	\strpos($filtered_unauthorized, '"customAttributes":[]') !== false,
	'Safely strips customAttributes array when user lacks capability'
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
