=== MTWDev Custom Block Attributes ===
Contributors: GWKaplan, mtwdev
Tags: gutenberg, attributes, html, data-attributes, accessibility, developer
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Añade fácilmente atributos HTML personalizados (data-*, aria-*, booleanos y pares clave-valor) a cualquier bloque de Gutenberg.

== Description ==

**MTWDev Custom Block Attributes** es una extensión para el editor de bloques (Gutenberg) de WordPress que permite a desarrolladores y editores inyectar atributos HTML estándar y personalizados en cualquier bloque existente.

Ya sea que necesites añadir atributos para analítica y seguimiento (`data-track-event="click"`), mejorar la accesibilidad (`aria-label`, `role`), o utilizar atributos modernos de HTML5 (`download`, `inert`), este plugin proporciona una interfaz intuitiva dentro del panel Avanzado nativo del bloque.

### Características

* **Integración en panel Avanzado:** Sin paneles flotantes ni interfaces dispersas.
* **Presets rápidos:** Añade con un solo clic `data-*`, `aria-label`, `title`, `download`, `role`, `tabindex` o `inert`.
* **Soporte booleano:** Renderizado de atributos que no requieren valor (`download`, `inert`).
* **Seguridad activa:** Bloqueo preventivo de eventos JavaScript (`onclick`, `on*`) y validación estricta de nombres y valores.
* **Cero sobrecarga frontend:** Sin JavaScript ni CSS en el frontend; inyección limpia y ligera en tiempo de renderizado.

== Installation ==

1. Sube la carpeta `mtwdev-custom-attributes` al directorio `/wp-content/plugins/` de tu instalación.
2. Activa el plugin a través del menú **Plugins** en WordPress.
3. Edita cualquier entrada o página en el Editor de Bloques y despliega la pestaña **Avanzado** del bloque seleccionado para ver la sección de atributos.

== Frequently Asked Questions ==

= ¿Afecta a la velocidad de carga de mi web en el frontend? =
No. El plugin no carga ningún archivo CSS ni JS adicional en el frontend. La inyección de atributos se ejecuta de manera nativa mediante el hook `render_block` de WordPress en menos de 0.1 ms.

= ¿Puedo usarlo para añadir onclick o eventos JavaScript? =
No. Por motivos de seguridad y para cumplir con las directrices oficiales de WordPress y prevención XSS, los manejadores de eventos inline que empiezan por `on` están estrictamente deshabilitados.

= ¿Funciona con bloques personalizados de terceros? =
Sí. El plugin se aplica a todos los bloques registrados en WordPress que soporten el filtro de atributos de Gutenberg.

== Changelog ==

= 1.0.0 =
* Versión inicial.
* Inyección optimizada con WP_HTML_Tag_Processor nativo de WordPress core.
* Panel integrado en la sección Avanzado de Gutenberg.
* Barra de presets rápidos y soporte para atributos booleanos.
* Exclusión de bloques no soportados (core/html, core/shortcode, core/freeform).
* Validación estricta de seguridad, control de unfiltered_html y prevención XSS.
* Hooks PHP mtwdev_custom_attributes_disallowed y mtwdev_custom_attributes_unsupported_blocks.
