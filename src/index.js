/**
 * MTWDev Custom Block Attributes - Editor Script
 *
 * Adds controls to the Gutenberg block "Advanced" panel
 * to inject custom HTML attributes into any supported block.
 */

import { InspectorControls } from '@wordpress/block-editor';
import { BaseControl, Button, TextControl, ToggleControl, Notice } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useState } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';

import { validateNewAttribute } from './utils/validation';
import './editor.scss';

// Recommended presets for quick insertion
const QUICK_PRESETS = [
	{ label: 'data-*', key: 'data-', isBoolean: false },
	{ label: 'aria-label', key: 'aria-label', isBoolean: false },
	{ label: 'title', key: 'title', isBoolean: false },
	{ label: 'download', key: 'download', isBoolean: true },
	{ label: 'role', key: 'role', isBoolean: false },
	{ label: 'tabindex', key: 'tabindex', isBoolean: false },
	{ label: 'inert', key: 'inert', isBoolean: true },
];

// Fallback list of unsupported blocks
const getUnsupportedBlocks = () => {
	return (
		window.mtwdevCustomAttributesData?.unsupportedBlocks || [
			'core/freeform',
			'core/html',
			'core/shortcode',
			'core/legacy-widget',
		]
	);
};

// 1. Declare 'customAttributes' attribute on all supported blocks.
// The server registers it too, but this hook also covers blocks registered only in JS.
addFilter(
	'blocks.registerBlockType',
	'mtwdev-custom-attributes/register-attributes',
	(settings, name) => {
		if (getUnsupportedBlocks().includes(name)) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...(settings.attributes || {}),
				customAttributes: {
					type: 'array',
					default: [],
				},
			},
		};
	},
);

// 2. Higher-Order Component (HOC) injecting controls into the "Advanced" panel
const withCustomAttributesControls = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		const { attributes, setAttributes, isSelected, name } = props;

		// Return unmodified if block is not supported
		if (getUnsupportedBlocks().includes(name)) {
			return <BlockEdit {...props} />;
		}

		const canUserUse =
			window.mtwdevCustomAttributesData?.canUserUseUnfilteredHTML !== false;

		const customAttributes = attributes.customAttributes || [];

		// Local state for the add-attribute form
		const [newKey, setNewKey] = useState('');
		const [isSimple, setIsSimple] = useState(false);
		const [errorMessage, setErrorMessage] = useState('');

		// Apply quick preset
		const handleApplyPreset = (preset) => {
			if (!canUserUse) return;
			setNewKey(preset.key);
			setIsSimple(preset.isBoolean);
			setErrorMessage('');
		};

		// Validate and add a new attribute
		const handleAddAttribute = () => {
			if (!canUserUse) return;

			const validation = validateNewAttribute(newKey, customAttributes);
			if (!validation.isValid) {
				setErrorMessage(validation.error);
				return;
			}

			const updatedAttributes = [
				...customAttributes,
				{ key: validation.cleanKey, value: '', isBoolean: isSimple },
			];

			setAttributes({ customAttributes: updatedAttributes });
			setNewKey('');
			setIsSimple(false);
			setErrorMessage('');
		};

		// Update value of existing attribute
		const handleUpdateValue = (index, value) => {
			if (!canUserUse) return;
			const updated = [...customAttributes];
			updated[index].value = value;
			setAttributes({ customAttributes: updated });
		};

		// Toggle boolean state
		const handleToggleBoolean = (index, isBoolean) => {
			if (!canUserUse) return;
			const updated = [...customAttributes];
			updated[index].isBoolean = isBoolean;
			if (isBoolean) {
				updated[index].value = '';
			}
			setAttributes({ customAttributes: updated });
		};

		// Remove attribute
		const handleRemoveAttribute = (index) => {
			if (!canUserUse) return;
			const updated = customAttributes.filter((_, i) => i !== index);
			setAttributes({ customAttributes: updated });
		};

		return (
			<Fragment>
				<BlockEdit {...props} />
				{isSelected && (
					<InspectorControls group='advanced'>
						<div className='mtwdev-custom-attributes-wrapper'>
							<BaseControl
								label={__('Custom HTML Attributes', 'mtwdev-custom-attributes')}
								help={__(
									'Add custom HTML attributes (data-*, aria-*, download, etc.) directly to the opening tag of this block.',
									'mtwdev-custom-attributes',
								)}
							>
								{!canUserUse && (
									<Notice status='warning' isDismissible={false} style={{ marginBottom: '12px' }}>
										{__(
											'Your user role does not have sufficient permissions to add or edit custom HTML attributes.',
											'mtwdev-custom-attributes',
										)}
									</Notice>
								)}

								{/* Quick Presets */}
								<div className='mtwdev-custom-attributes-presets'>
									{QUICK_PRESETS.map((preset) => (
										<button
											key={preset.label}
											type='button'
											disabled={!canUserUse}
											className='mtwdev-custom-attributes-preset-btn'
											onClick={() => handleApplyPreset(preset)}
											title={sprintf(
												/* translators: %s: Preset label */
												__('Use preset %s', 'mtwdev-custom-attributes'),
												preset.label,
											)}
										>
											+{preset.label}
										</button>
									))}
								</div>

								{/* Add Attribute Input Row */}
								<div className='mtwdev-custom-attributes-add-row'>
									<TextControl
										placeholder={__('e.g. title, data-track...', 'mtwdev-custom-attributes')}
										value={newKey}
										disabled={!canUserUse}
										onChange={(val) => {
											setNewKey(val);
											if (errorMessage) setErrorMessage('');
										}}
									/>
									<Button isPrimary onClick={handleAddAttribute} disabled={!canUserUse}>
										{__('Add', 'mtwdev-custom-attributes')}
									</Button>
								</div>

								{/* Validation Error Message */}
								{errorMessage && (
									<div className='mtwdev-custom-attributes-error' role='alert'>
										{errorMessage}
									</div>
								)}

								<ToggleControl
									label={__('Boolean / empty attribute (no value)', 'mtwdev-custom-attributes')}
									checked={isSimple}
									disabled={!canUserUse}
									onChange={(val) => setIsSimple(val)}
									help={__(
										'For attributes that do not require a value, such as download, inert, etc.',
										'mtwdev-custom-attributes',
									)}
								/>

								{/* Active Attributes List */}
								{customAttributes.length > 0 && (
									<div className='mtwdev-custom-attributes-list'>
										{customAttributes.map((attr, index) => (
											<div key={attr.key} className='mtwdev-custom-attribute-card'>
												<div className='mtwdev-card-header'>
													<code>{attr.key}</code>
													<Button
														isDestructive
														isSmall
														disabled={!canUserUse}
														onClick={() => handleRemoveAttribute(index)}
														title={__('Remove attribute', 'mtwdev-custom-attributes')}
													>
														✕
													</Button>
												</div>

												{!attr.isBoolean ? (
													<TextControl
														label={sprintf(
															/* translators: %s: Attribute key */
															__('Value for %s', 'mtwdev-custom-attributes'),
															attr.key,
														)}
														value={attr.value || ''}
														disabled={!canUserUse}
														onChange={(val) => handleUpdateValue(index, val)}
													/>
												) : (
													<div className='mtwdev-boolean-hint'>
														{__('Boolean attribute (rendered without a value)', 'mtwdev-custom-attributes')}
													</div>
												)}

												<ToggleControl
													label={__('Boolean attribute (no value)', 'mtwdev-custom-attributes')}
													checked={!!attr.isBoolean}
													disabled={!canUserUse}
													onChange={(val) => handleToggleBoolean(index, val)}
												/>
											</div>
										))}
									</div>
								)}
							</BaseControl>
						</div>
					</InspectorControls>
				)}
			</Fragment>
		);
	};
}, 'withCustomAttributesControls');

addFilter(
	'editor.BlockEdit',
	'mtwdev-custom-attributes/with-custom-attributes-controls',
	withCustomAttributesControls,
);
