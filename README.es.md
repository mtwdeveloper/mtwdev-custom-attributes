# Custom Block Attributes

[![WordPress](https://img.shields.io/badge/WordPress-6.6%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![Licencia: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Tests](https://img.shields.io/badge/Tests-Superados%20(88%2F88)-brightgreen.svg)](#-tests-automatizados)
[![Versión](https://img.shields.io/badge/Versi%C3%B3n-1.1.0-informational.svg)](https://github.com/mtwdeveloper/mtwdev-custom-attributes/releases)

*Leer en otros idiomas:* [English](README.md) | [Español](README.es.md)

**Custom Block Attributes** es un plugin para WordPress ligero, seguro y optimizado para desarrolladores que permite a editores de contenido y desarrolladores inyectar atributos HTML personalizados (`data-*`, `aria-*`, `title`, atributos booleanos como `download` o `inert`, y pares clave-valor) en **cualquier bloque de Gutenberg** directamente desde el editor de bloques.

---

## 🚀 Características Principales

- **Integración Nativa en Gutenberg:** Integrado de forma limpia dentro del panel oficial **Avanzado** en la barra lateral de ajustes de bloque.
- **Soporte para Atributos Booleanos y Clave-Valor:**
  - Atributos clave-valor: `data-user-id="105"`, `aria-label="Cerrar modal"`, `title="Información"`.
  - Atributos booleanos limpios sin valor: `download`, `inert`, `novalidate`, `required`.
- **Barra de Presets Rápidos:** Accesos directos de un solo clic para los atributos más frecuentes (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
- **Seguridad Multicapa y Mitigación de XSS:**
  - Validación estricta del nombre del atributo según estándar HTML5 (`/^[a-z0-9_\-:]+$/`).
  - Validación por lista blanca: solo se inyectan `data-*`, `aria-*` y un conjunto documentado de atributos estándar (`role`, `title`, `tabindex`, `lang`, `dir`, `inert`, `hidden`, `download`, `rel`, `target`, `name`, `for`, `autocomplete`, `xml:lang`). Los atributos con URL y los de espacio de nombres (`href`, `src`, `xmlns:*`, `xlink:*`) se rechazan siempre.
  - Bloqueo automático de manejadores de eventos DOM en línea (`onclick`, `onload`, `on*`) y atributos ejecutables (`srcdoc`, `formaction`, `action`).
  - Detección profunda de pseudo-protocolos peligrosos (`javascript:`, `vbscript:`, `data:text/html`), incluyendo evasión por espacios o saltos de línea.
  - Protección de atributos reservados: impide sobrescribir `class`, `id` o `style`.
  - Refuerzo al guardar: los usuarios sin la capacidad requerida pierden `customAttributes` al guardar, analizando y reconstruyendo el documento con `parse_blocks()`/`serialize_blocks()`, de modo que el marcado del bloque nunca se corrompe.
- **Cero Impacto en el Rendimiento del Frontend ($O(1)$):**
  - Cero CSS o JS encolado en el frontend.
  - Inyección dinámica y ultrarrápida al renderizar con `WP_HTML_Tag_Processor` nativo de WordPress 6.2+.
- **Cero Invalidación de Bloques:** Los atributos se guardan en metadatos del bloque. Si se desactiva el plugin, los bloques nunca arrojarán errores de validación de Gutenberg (*"Este bloque contiene contenido inesperado o no válido"*).
- **Preparado para traducir (i18n):** Cadenas base en inglés, plantilla en `languages/` y traducciones de la comunidad servidas como language packs desde translate.wordpress.org. Sin archivos `.po`/`.mo`/`.json` empaquetados.
- **Suite de Pruebas Automatizadas:** Tests PHP y JavaScript de validación, sanitización, vectores de seguridad, refuerzo al guardar y renderizado.
- **Extensible para Desarrolladores:** Filtros PHP para personalizar permisos (`mtwdev_custom_attributes_capability`), la lista blanca de atributos (`mtwdev_custom_attributes_allowed`), la lista negra adicional (`mtwdev_custom_attributes_disallowed`) y bloques incompatibles (`mtwdev_custom_attributes_unsupported_blocks`).

---

## 📋 Requisitos

- **WordPress:** 6.6 o superior (necesario por la dependencia de script `react-jsx-runtime` que usa el bundle compilado del editor).
- **PHP:** 7.4 o superior (compatible con PHP 8.0, 8.1, 8.2 y 8.3).
- **Editor de Bloques (Gutenberg):** Activado.

---

## 📦 Instalación

### Método 1: Descarga Directa (ZIP)
1. Descarga el archivo `.zip` más reciente desde [Releases](https://github.com/mtwdeveloper/mtwdev-custom-attributes/releases).
2. En tu panel de administración de WordPress, dirígete a **Plugins > Añadir nuevo > Subir plugin**.
3. Selecciona el archivo ZIP y pulsa **Instalar ahora**.
4. Pulsa **Activar plugin**.

### Método 2: Git Clone (Desarrolladores)
1. Clona el repositorio en la carpeta de plugins:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/mtwdeveloper/mtwdev-custom-attributes.git mtwdev-custom-attributes
   ```
2. Instala dependencias y compila los assets del editor:
   ```bash
   cd mtwdev-custom-attributes
   pnpm install
   pnpm run build
   ```
3. Activa el plugin en el panel de WordPress.

---

## 🧪 Tests Automatizados

El plugin incluye pruebas automáticas completas tanto en JavaScript como en PHP:

```bash
# Ejecutar toda la suite de tests (JS + PHP)
pnpm test

# Ejecutar tests unitarios de JavaScript (Jest mediante @wordpress/scripts)
pnpm run test:js

# Ejecutar tests unitarios e integrados de PHP
pnpm run test:php
```

Los 88 tests (11 de JS y 77 de PHP) se ejecutan de forma aislada y verifican sanitización, validación, protocolos de seguridad, el refuerzo al guardar y el renderizado.

---

## 🌍 Internacionalización (i18n)

El plugin no incluye traducciones compiladas: cada idioma se sirve como language pack desde [translate.wordpress.org](https://translate.wordpress.org/). En local solo se genera la plantilla:

```bash
# Regenerar languages/mtwdev-custom-attributes.pot (cadenas PHP + JS)
pnpm run i18n:pot
```

Si añades o cambias cualquier cadena visible, ejecuta ese comando y haz commit del `.pot` actualizado, para que GlotPress detecte las cadenas nuevas. El flujo completo está en [CONTRIBUTING.es.md](CONTRIBUTING.es.md#-traducciones).

---

## 🛠️ Cómo Utilizarlo

1. Abre cualquier entrada, página o plantilla en el **Editor de Bloques (Gutenberg)**.
2. Selecciona cualquier bloque que desees personalizar (Botón, Grupo, Imagen, Párrafo, Encabezado, etc.).
3. En la barra lateral derecha, despliega el panel **Avanzado**.
4. En la sección **Atributos HTML Personalizados**:
   - Pulsa cualquier botón de **Preset rápido** (ej. `+data-*` o `+download`) o escribe el nombre del atributo.
   - Si se trata de un atributo booleano sin valor (como `download` o `inert`), activa **Atributo simple / booleano (sin valor)**.
   - Pulsa **Añadir**.
   - Si no es booleano, introduce el valor deseado en el campo de texto.
5. Guarda o publica tu contenido.

---

## 🛡️ Seguridad y Buenas Prácticas

1. **Lista de Exclusión:** Deniega todos los manejadores de eventos que empiezan por `on*` (`onclick`, `onerror`, etc.) y atributos de ejecución (`srcdoc`, `formaction`, `xmlns`).
2. **Sanitización y Escape:** Nombres validados con regex y valores saneados con `WP_HTML_Tag_Processor` o `esc_attr()`.
3. **Control de Protocolos:** Bloqueo exhaustivo de `javascript:`, `vbscript:` y `data:text/html`.
4. **Comprobación de Capacidades:** Los usuarios que no dispongan del permiso configurado (por defecto `unfiltered_html`) no pueden inyectar atributos y el contenido se limpia de forma segura mediante el filtro `pre_kses`.

---

## 🔌 API de Desarrolladores y Filtros

### Personalizar la Capacidad Requerida
```php
add_filter('mtwdev_custom_attributes_capability', function (string $cap): string {
    // Permitir a cualquier usuario con permisos de edición de entradas
    return 'edit_posts';
});
```

### Ampliar los Atributos Permitidos

Los atributos se validan contra una lista blanca. Añade aquí tus propios nombres o prefijos; seguirán pasando por la lista negra y por la comprobación de pseudo-protocolos.

```php
add_filter('mtwdev_custom_attributes_allowed', function (array $allowed): array {
    $allowed['exact'][]    = 'data-qa-id';
    $allowed['prefixes'][] = 'x-company-';
    return $allowed;
});
```

### Personalizar Atributos Prohibidos
```php
add_filter('mtwdev_custom_attributes_disallowed', function (array $disallowed): array {
    $disallowed[] = 'custom-restricted-attr';
    return $disallowed;
});
```

### Personalizar Bloques Incompatibles
```php
add_filter('mtwdev_custom_attributes_unsupported_blocks', function (array $blocks): array {
    $blocks[] = 'mi-plugin/bloque-no-soportado';
    return $blocks;
});
```

---

## 📄 Licencia

Este proyecto es software de código abierto bajo la licencia [GNU General Public License v2.0 or later (GPL-2.0-or-later)](LICENSE).
