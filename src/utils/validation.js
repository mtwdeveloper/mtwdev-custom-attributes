/**
 * Validation utilities for custom HTML attributes.
 */

import { __, sprintf } from '@wordpress/i18n';

/**
 * Attributes natively managed by WordPress or reserved.
 */
export const RESERVED_ATTRIBUTES = ['class', 'id', 'style'];

/**
 * Prohibited attribute names for security and markup integrity.
 */
export const DISALLOWED_ATTRIBUTES = [
	'srcdoc',
	'formaction',
	'action',
	'xmlns',
	'xlink:href',
	'poster',
];

/**
 * Attribute names allowed on rendered blocks (mirrors the PHP allow list).
 */
export const ALLOWED_ATTRIBUTES = [
	'autocomplete',
	'dir',
	'download',
	'for',
	'hidden',
	'inert',
	'lang',
	'name',
	'rel',
	'role',
	'tabindex',
	'target',
	'title',
	'xml:lang',
];

/**
 * Allowed attribute name prefixes.
 */
export const ALLOWED_PREFIXES = ['aria-', 'data-'];

/**
 * Checks whether an attribute name is on the allow list.
 *
 * @param {string} key Sanitized attribute key.
 * @return {boolean} True when the name is allowed.
 */
export function isAllowedAttributeName(key) {
	if (ALLOWED_ATTRIBUTES.includes(key)) {
		return true;
	}

	return ALLOWED_PREFIXES.some(
		(prefix) => key.length > prefix.length && key.startsWith(prefix),
	);
}

/**
 * Sanitizes an attribute name (lowercase and trimmed).
 *
 * @param {string} key Attribute key.
 * @return {string} Sanitized key.
 */
export function sanitizeAttributeName(key) {
	if (typeof key !== 'string') {
		return '';
	}
	return key.trim().toLowerCase();
}

/**
 * Validates a new attribute name before adding.
 *
 * @param {string} key Raw attribute key.
 * @param {Array<{key: string}>} currentAttributes Existing attributes list.
 * @return {{ isValid: boolean, error?: string, cleanKey: string }} Validation result.
 */
export function validateNewAttribute(key, currentAttributes = []) {
	const cleanKey = sanitizeAttributeName(key);

	if (!cleanKey) {
		return {
			isValid: false,
			error: __('Please enter an attribute name.', 'mtwdev-custom-attributes'),
			cleanKey: '',
		};
	}

	// Validate allowed characters (letters, numbers, hyphens, colons, underscores)
	if (!/^[a-z0-9_\-:]+$/.test(cleanKey)) {
		return {
			isValid: false,
			error: __(
				'Invalid characters. Use only lowercase letters, numbers, hyphens, colons, or underscores.',
				'mtwdev-custom-attributes',
			),
			cleanKey,
		};
	}

	// Block inline event handlers (XSS prevention)
	if (cleanKey.startsWith('on')) {
		return {
			isValid: false,
			error: __(
				'For security reasons, "on*" event attributes are not allowed.',
				'mtwdev-custom-attributes',
			),
			cleanKey,
		};
	}

	// Check reserved native WordPress attributes
	if (RESERVED_ATTRIBUTES.includes(cleanKey)) {
		return {
			isValid: false,
			error: sprintf(
				/* translators: %s: Attribute name */
				__(
					'The attribute "%s" is already natively managed by WordPress.',
					'mtwdev-custom-attributes',
				),
				cleanKey,
			),
			cleanKey,
		};
	}

	// Check disallowed security attributes
	if (DISALLOWED_ATTRIBUTES.includes(cleanKey)) {
		return {
			isValid: false,
			error: sprintf(
				/* translators: %s: Attribute name */
				__(
					'The attribute "%s" is disallowed for security reasons.',
					'mtwdev-custom-attributes',
				),
				cleanKey,
			),
			cleanKey,
		};
	}

	// A bare prefix ("data-", "aria-") is not a complete attribute name
	if (ALLOWED_PREFIXES.includes(cleanKey)) {
		return {
			isValid: false,
			error: sprintf(
				/* translators: %s: Attribute prefix, e.g. "data-" */
				__(
					'Incomplete attribute: "%s" is only a prefix. Add a name after it, e.g. "data-track".',
					'mtwdev-custom-attributes',
				),
				cleanKey,
			),
			cleanKey,
		};
	}

	// Check the allow list: anything not explicitly allowed is refused
	if (!isAllowedAttributeName(cleanKey)) {
		return {
			isValid: false,
			error: sprintf(
				/* translators: %s: Attribute name */
				__(
					'The attribute "%s" is disallowed for security reasons.',
					'mtwdev-custom-attributes',
				),
				cleanKey,
			),
			cleanKey,
		};
	}

	// Check duplicates
	if (currentAttributes.some((attr) => attr.key === cleanKey)) {		return {
			isValid: false,
			error: sprintf(
				/* translators: %s: Attribute name */
				__(
					'The attribute "%s" is already added to this block.',
					'mtwdev-custom-attributes',
				),
				cleanKey,
			),
			cleanKey,
		};
	}

	return {
		isValid: true,
		cleanKey,
	};
}
