/**
 * MTWDev Custom Block Attributes - Editor Script
 *
 * Añade controles en el panel "Avanzado" del Inspector de Gutenberg
 * para inyectar atributos HTML personalizados en cualquier bloque compatible.
 */

import { InspectorControls } from '@wordpress/block-editor';
import { BaseControl, Button, TextControl, ToggleControl, Notice } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useState } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';

import './editor.scss';

// Presets recomendados para inserción rápida
const QUICK_PRESETS = [
	{ label: 'data-*', key: 'data-', isBoolean: false },
	{ label: 'aria-label', key: 'aria-label', isBoolean: false },
	{ label: 'title', key: 'title', isBoolean: false },
	{ label: 'download', key: 'download', isBoolean: true },
	{ label: 'role', key: 'role', isBoolean: false },
	{ label: 'tabindex', key: 'tabindex', isBoolean: false },
	{ label: 'inert', key: 'inert', isBoolean: true },
];

// Atributos reservados o gestionados nativamente por Gutenberg
const RESERVED_ATTRIBUTES = ['class', 'id', 'style'];

// Lista de bloques incompatibles
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

// 1. Declarar el atributo 'customAttributes' en la definición de los bloques soportados
addFilter(
	'blocks.registerBlockTypeAttributes',
	'mtwdev-custom-attributes/register-attributes',
	(attributes, blockType) => {
		const blockName = blockType?.name || '';
		if (getUnsupportedBlocks().includes(blockName)) {
			return attributes;
		}

		return {
			...attributes,
			customAttributes: {
				type: 'array',
				default: [],
			},
		};
	},
);

// 2. Componente HOC para inyectar la UI en el panel "Avanzado"
const withCustomAttributesControls = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		const { attributes, setAttributes, isSelected, name } = props;

		// Si el bloque no está soportado, devolver sin modificar
		if (getUnsupportedBlocks().includes(name)) {
			return <BlockEdit {...props} />;
		}

		const canUserUse =
			window.mtwdevCustomAttributesData?.canUserUseUnfilteredHTML !== false;

		const customAttributes = attributes.customAttributes || [];

		// Estado local para el formulario de adición
		const [newKey, setNewKey] = useState('');
		const [isSimple, setIsSimple] = useState(false);
		const [errorMessage, setErrorMessage] = useState('');

		// Aplicar un preset rápido
		const handleApplyPreset = (preset) => {
			if (!canUserUse) return;
			setNewKey(preset.key);
			setIsSimple(preset.isBoolean);
			setErrorMessage('');
		};

		// Validar y añadir un nuevo atributo
		const handleAddAttribute = () => {
			if (!canUserUse) return;

			const cleanKey = newKey
				.trim()
				.toLowerCase();

			if (!cleanKey) {
				setErrorMessage(__('Introduce el nombre del atributo.', 'mtwdev-custom-attributes'));
				return;
			}

			// Validar caracteres HTML permitidos
			if (!/^[a-z0-9_\-:]+$/.test(cleanKey)) {
				setErrorMessage(
					__('Caracteres no permitidos. Usa solo minúsculas, números, guiones o barras bajas.', 'mtwdev-custom-attributes'),
				);
				return;
			}

			// Protección contra eventos inline (XSS)
			if (cleanKey.startsWith('on')) {
				setErrorMessage(
					__('Por motivos de seguridad, los atributos de evento "on*" no están permitidos.', 'mtwdev-custom-attributes'),
				);
				return;
			}

			// Comprobación de atributos reservados nativos
			if (RESERVED_ATTRIBUTES.includes(cleanKey)) {
				setErrorMessage(
					sprintf(
						__('El atributo "%s" ya está gestionado de forma nativa por WordPress en este panel.', 'mtwdev-custom-attributes'),
						cleanKey,
					),
				);
				return;
			}

			// Evitar duplicados
			if (customAttributes.some((attr) => attr.key === cleanKey)) {
				setErrorMessage(
					sprintf(
						__('El atributo "%s" ya está añadido a este bloque.', 'mtwdev-custom-attributes'),
						cleanKey,
					),
				);
				return;
			}

			const updatedAttributes = [
				...customAttributes,
				{ key: cleanKey, value: '', isBoolean: isSimple },
			];

			setAttributes({ customAttributes: updatedAttributes });
			setNewKey('');
			setIsSimple(false);
			setErrorMessage('');
		};

		// Actualizar el valor de un atributo existente
		const handleUpdateValue = (index, value) => {
			if (!canUserUse) return;
			const updated = [...customAttributes];
			updated[index].value = value;
			setAttributes({ customAttributes: updated });
		};

		// Alternar estado booleano
		const handleToggleBoolean = (index, isBoolean) => {
			if (!canUserUse) return;
			const updated = [...customAttributes];
			updated[index].isBoolean = isBoolean;
			if (isBoolean) {
				updated[index].value = '';
			}
			setAttributes({ customAttributes: updated });
		};

		// Eliminar atributo
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
								label={__('Atributos HTML Personalizados', 'mtwdev-custom-attributes')}
								help={__(
									'Añade atributos HTML (data-*, aria-*, download, etc.) directamente en la etiqueta de este bloque.',
									'mtwdev-custom-attributes',
								)}
							>
								{!canUserUse && (
									<Notice status='warning' isDismissible={false} style={{ marginBottom: '12px' }}>
										{__(
											'Tu rol de usuario actual no dispone de permisos suficientes (unfiltered_html) para añadir o editar atributos HTML.',
											'mtwdev-custom-attributes',
										)}
									</Notice>
								)}

								{/* Barra de presets rápidos */}
								<div className='mtwdev-custom-attributes-presets'>
									{QUICK_PRESETS.map((preset) => (
										<button
											key={preset.label}
											type='button'
											disabled={!canUserUse}
											className='mtwdev-custom-attributes-preset-btn'
											onClick={() => handleApplyPreset(preset)}
											title={sprintf(
												__('Usar preset %s', 'mtwdev-custom-attributes'),
												preset.label,
											)}
										>
											+{preset.label}
										</button>
									))}
								</div>

								{/* Fila de adición */}
								<div className='mtwdev-custom-attributes-add-row'>
									<TextControl
										placeholder={__('Ej: title, data-track...', 'mtwdev-custom-attributes')}
										value={newKey}
										disabled={!canUserUse}
										onChange={(val) => {
											setNewKey(val);
											if (errorMessage) setErrorMessage('');
										}}
									/>
									<Button isPrimary onClick={handleAddAttribute} disabled={!canUserUse}>
										{__('Añadir', 'mtwdev-custom-attributes')}
									</Button>
								</div>

								{/* Mensaje de error / validación */}
								{errorMessage && (
									<div className='mtwdev-custom-attributes-error' role='alert'>
										{errorMessage}
									</div>
								)}

								<ToggleControl
									label={__('Atributo simple / booleano (sin valor)', 'mtwdev-custom-attributes')}
									checked={isSimple}
									disabled={!canUserUse}
									onChange={(val) => setIsSimple(val)}
									help={__(
										'Para atributos que no llevan valor, como download, inert, etc.',
										'mtwdev-custom-attributes',
									)}
								/>

								{/* Lista de atributos activos */}
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
														title={__('Eliminar atributo', 'mtwdev-custom-attributes')}
													>
														✕
													</Button>
												</div>

												{!attr.isBoolean ? (
													<TextControl
														label={sprintf(__('Valor de %s', 'mtwdev-custom-attributes'), attr.key)}
														value={attr.value || ''}
														disabled={!canUserUse}
														onChange={(val) => handleUpdateValue(index, val)}
													/>
												) : (
													<div className='mtwdev-boolean-hint'>
														{__('Atributo simple (se renderiza sin valor)', 'mtwdev-custom-attributes')}
													</div>
												)}

												<ToggleControl
													label={__('Es atributo simple (sin valor)', 'mtwdev-custom-attributes')}
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
