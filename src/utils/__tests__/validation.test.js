/**
 * Unit tests for custom attribute validation.
 */

import {
	sanitizeAttributeName,
	validateNewAttribute,
	RESERVED_ATTRIBUTES,
	DISALLOWED_ATTRIBUTES,
} from '../validation';

describe('sanitizeAttributeName', () => {
	it('converts uppercase to lowercase and trims whitespace', () => {
		expect(sanitizeAttributeName('  Data-Test  ')).toBe('data-test');
		expect(sanitizeAttributeName('ARIA-LABEL')).toBe('aria-label');
	});

	it('handles non-string inputs safely', () => {
		expect(sanitizeAttributeName(null)).toBe('');
		expect(sanitizeAttributeName(undefined)).toBe('');
		expect(sanitizeAttributeName(123)).toBe('');
	});
});

describe('validateNewAttribute', () => {
	it('rejects empty or whitespace-only keys', () => {
		const result = validateNewAttribute('   ');
		expect(result.isValid).toBe(false);
		expect(result.error).toContain('Please enter an attribute name');
	});

	it('rejects invalid characters (spaces, special symbols)', () => {
		const invalidKeys = ['data test', 'attr$name', 'attr<tag>', 'key"quotes"'];
		for (const key of invalidKeys) {
			const result = validateNewAttribute(key);
			expect(result.isValid).toBe(false);
			expect(result.error).toContain('Invalid characters');
		}
	});

	it('blocks event handlers starting with "on" (XSS mitigation)', () => {
		const eventKeys = ['onclick', 'onmouseover', 'ONLOAD', 'onfocus', 'ontouchstart'];
		for (const key of eventKeys) {
			const result = validateNewAttribute(key);
			expect(result.isValid).toBe(false);
			expect(result.error).toContain('on*');
		}
	});

	it('blocks reserved native WordPress attributes', () => {
		for (const reserved of RESERVED_ATTRIBUTES) {
			const result = validateNewAttribute(reserved);
			expect(result.isValid).toBe(false);
			expect(result.error).toContain('natively managed by WordPress');
		}
	});

	it('blocks disallowed security attributes', () => {
		for (const disallowed of DISALLOWED_ATTRIBUTES) {
			const result = validateNewAttribute(disallowed);
			expect(result.isValid).toBe(false);
			expect(result.error).toContain('disallowed for security reasons');
		}
	});

	it('detects duplicate attributes', () => {
		const existing = [{ key: 'data-track' }, { key: 'aria-label' }];
		const result = validateNewAttribute('data-track', existing);
		expect(result.isValid).toBe(false);
		expect(result.error).toContain('already added to this block');
	});

	it('rejects a bare prefix with a specific message', () => {
		for (const prefix of ['data-', 'aria-']) {
			const result = validateNewAttribute(prefix);
			expect(result.isValid).toBe(false);
			expect(result.error).toContain('is only a prefix');
		}
	});

	it('rejects names that are not on the allow list', () => {
		const refusedKeys = ['href', 'src', 'xmlns:xlink', 'col', 'custom:ns'];
		for (const key of refusedKeys) {
			const result = validateNewAttribute(key);
			expect(result.isValid).toBe(false);
			expect(result.error).toContain('disallowed for security reasons');
		}
	});

	it('accepts valid custom attributes', () => {
		const validKeys = [
			'data-tracking-id',
			'aria-describedby',
			'title',
			'download',
			'role',
			'tabindex',
			'lang',
			'dir',
			'inert',
			'xml:lang',
		];
		for (const key of validKeys) {
			const result = validateNewAttribute(key, []);
			expect(result.isValid).toBe(true);
			expect(result.cleanKey).toBe(key.toLowerCase());
			expect(result.error).toBeUndefined();
		}
	});
});
