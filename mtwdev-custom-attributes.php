<?php
/**
 * Plugin Name:       MTWDev Custom Block Attributes
 * Plugin URI:        https://github.com/GWKaplan/wp-custom-attributes
 * Description:       Añade fácilmente atributos HTML personalizados (data-*, aria-*, booleanos y pares clave-valor) a cualquier bloque de Gutenberg con validación de seguridad, presets rápidos y renderizado nativo.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            GWKaplan / MTWdev
 * Author URI:        https://github.com/GWKaplan
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mtwdev-custom-attributes
 * Domain Path:       /languages
 *
 * @package           MTWDev\CustomAttributes
 */

namespace MTWDev\CustomAttributes;

// Salir si se accede directamente para prevenir ejecución arbitraria.
if (!\defined('ABSPATH')) {
	exit;
}

// Constantes maestras del plugin.
\define('MTWDEV_CUSTOM_ATTRIBUTES_VERSION', '1.0.0');
\define('MTWDEV_CUSTOM_ATTRIBUTES_DIR', \plugin_dir_path(__FILE__));
\define('MTWDEV_CUSTOM_ATTRIBUTES_URL', \plugin_dir_url(__FILE__));
\define('MTWDEV_CUSTOM_ATTRIBUTES_BASENAME', \plugin_basename(__FILE__));

/**
 * Retorna la lista de bloques donde no está soportado añadir atributos HTML en la raíz.
 *
 * @return array<string> Slugs de bloques incompatibles.
 */
function get_unsupported_blocks(): array
{
	$unsupported = [
		'core/freeform',      // Editor clásico dentro de bloques
		'core/html',          // Bloque de HTML personalizado directo
		'core/shortcode',     // Bloque de Shortcode
		'core/legacy-widget', // Widgets heredados
	];

	/**
	 * Permite filtrar la lista de bloques incompatibles.
	 *
	 * @param array $unsupported Lista de slugs de bloques no soportados.
	 */
	return (array) \apply_filters('mtwdev_custom_attributes_unsupported_blocks', $unsupported);
}

/**
 * Retorna la lista de nombres de atributos HTML prohibidos por motivos de seguridad o integridad.
 *
 * Bloquea manejadores de eventos JavaScript en línea (on*) y atributos ejecutables para mitigar riesgos XSS.
 *
 * @return array<string> Lista de claves prohibidas.
 */
function get_disallowed_attributes(): array
{
	$disallowed = [
		// Handlers de eventos DOM (ejecución de scripts inline)
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
		// Atributos de documento/recurso ejecutable
		'srcdoc', 'formaction',
	];

	/**
	 * Permite filtrar o extender la lista negra de atributos HTML prohibidos.
	 *
	 * @param array $disallowed Lista de atributos prohibidos.
	 */
	return (array) \apply_filters('mtwdev_custom_attributes_disallowed', $disallowed);
}

/**
 * Sanea y valida estrictamente un nombre de atributo HTML.
 *
 * @param string $key Nombre del atributo a validar.
 * @return string Nombre saneado o cadena vacía si es inválido o no permitido.
 */
function sanitize_attribute_name(string $key): string
{
	$clean_key = \strtolower(\trim($key));

	// Solo permitir caracteres válidos para atributos HTML5 (letras, dígitos, guion, guion bajo y dos puntos para namespaces)
	if (!\preg_match('/^[a-z0-9_\-:]+$/', $clean_key)) {
		return '';
	}

	// Prohibir manejadores 'on*' y cualquier atributo en la lista negra
	if (\strpos($clean_key, 'on') === 0 || \in_array($clean_key, get_disallowed_attributes(), true)) {
		return '';
	}

	return $clean_key;
}

/**
 * 1. Registra el atributo 'customAttributes' en la definición del servidor para bloques soportados.
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
 * 2. Inyecta los atributos personalizados validados en la primera etiqueta HTML de apertura del bloque en frontend.
 *
 * Utiliza WP_HTML_Tag_Processor (estándar oficial WP 6.2+) para una inyección 100% precisa, segura y rápida.
 */
\add_filter('render_block', function (string $block_content, array $block): string {
	// Omitir en administración, si el contenido está vacío o si no hay atributos definidos
	if (\is_admin() || empty($block_content) || empty($block['attrs']['customAttributes']) || !\is_array($block['attrs']['customAttributes'])) {
		return $block_content;
	}

	$block_name = $block['blockName'] ?? '';
	if (!empty($block_name) && \in_array($block_name, get_unsupported_blocks(), true)) {
		return $block_content;
	}

	// Método principal: WP_HTML_Tag_Processor (WordPress 6.2+)
	if (\class_exists(\WP_HTML_Tag_Processor::class)) {
		$processor = new \WP_HTML_Tag_Processor($block_content);

		if ($processor->next_tag()) {
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

				if ($is_boolean || $value === '') {
					// Atributo booleano/simple limpio (ej: <a download ...>)
					$processor->set_attribute($key, true);
				} else {
					// Mitigación de seguridad: evitar pseudo-protocolo 'javascript:'
					if (\stripos($value, 'javascript:') !== false) {
						continue;
					}
					$processor->set_attribute($key, $value);
				}
			}

			return $processor->get_updated_html();
		}

		return $block_content;
	}

	// Fallback para entornos anteriores a WP 6.2 mediante regex
	$html_to_inject = '';
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

		if ($is_boolean || $value === '') {
			$html_to_inject .= ' ' . $key;
		} else {
			if (\stripos($value, 'javascript:') !== false) {
				continue;
			}
			$html_to_inject .= ' ' . $key . '="' . \esc_attr($value) . '"';
		}
	}

	if ($html_to_inject !== '') {
		$block_content = (string) \preg_replace('/<([a-z0-9\-]+)(\s|>)/i', '<$1' . $html_to_inject . '$2', $block_content, 1);
	}

	return $block_content;
}, 10, 2);

/**
 * 3. Blindaje de seguridad pre_kses: si el usuario no tiene la capacidad unfiltered_html,
 * se limpian los customAttributes al guardar para evitar inyecciones en instalaciones multi-autor o multisite.
 */
\add_filter('pre_kses', function (string $content): string {
	if (\strpos($content, '<!-- wp:') === false || \strpos($content, 'customAttributes') === false) {
		return $content;
	}

	if (\current_user_can('unfiltered_html')) {
		return $content;
	}

	$matches = null;
	if (\preg_match_all('/\\\?"customAttributes\\\?"\s*:\s*\[[^\]]*\]/', $content, $matches)) {
		foreach ($matches[0] as $match) {
			$empty_replacement = '"customAttributes":[]';
			if (\wp_unslash($match) !== $match) {
				$empty_replacement = \wp_slash($empty_replacement);
			}
			$content = \str_replace($match, $empty_replacement, $content);
		}
	}

	return $content;
});

/**
 * 4. Encola los scripts y estilos en el editor de bloques Gutenberg.
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

	// Encolar CSS del editor si se compila
	if (\file_exists(MTWDEV_CUSTOM_ATTRIBUTES_DIR . 'build/index.css')) {
		\wp_enqueue_style(
			'mtwdev-custom-attributes-editor',
			MTWDEV_CUSTOM_ATTRIBUTES_URL . 'build/index.css',
			['wp-components'],
			$asset['version'] ?? MTWDEV_CUSTOM_ATTRIBUTES_VERSION
		);
	}

	// Inyectar datos de configuración y permisos al script del editor
	\wp_localize_script(
		'mtwdev-custom-attributes-editor',
		'mtwdevCustomAttributesData',
		[
			'unsupportedBlocks'        => get_unsupported_blocks(),
			'canUserUseUnfilteredHTML' => \current_user_can('unfiltered_html'),
		]
	);

	// Traducciones i18n
	\wp_set_script_translations(
		'mtwdev-custom-attributes-editor',
		'mtwdev-custom-attributes',
		MTWDEV_CUSTOM_ATTRIBUTES_DIR . 'languages'
	);
});

/* ==========================================================================
   5. ACTUALIZADOR AUTOMÁTICO DESDE GITHUB (PUC)
   ========================================================================== */
$mtwdev_puc_file = MTWDEV_CUSTOM_ATTRIBUTES_DIR . 'lib/plugin-update-checker/plugin-update-checker.php';
if (\file_exists($mtwdev_puc_file)) {
	require_once $mtwdev_puc_file;

	$mtwdev_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/GWKaplan/wp-custom-attributes',
		__FILE__,
		'mtwdev-custom-attributes'
	);

	// Indicar que la rama principal es 'main'
	$mtwdev_update_checker->setBranch('main');
}
