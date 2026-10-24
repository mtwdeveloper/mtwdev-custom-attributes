# MTWDev Custom Block Attributes

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Updates](https://img.shields.io/badge/Updates-GitHub%20Direct-orange.svg)](https://github.com/GWKaplan/wp-custom-attributes)

**MTWDev Custom Block Attributes** es un plugin ligero, seguro y extensible para WordPress que permite añadir atributos HTML personalizados (`data-*`, `aria-*`, `title`, booleanos como `download` o `inert`, y cualquier atributo clave-valor) a **cualquier bloque de Gutenberg** directamente desde el editor visual.

---

## 🚀 Características Principales

- **Integración Nativa en Gutenberg:** Se ubica de forma limpia y discreta dentro del panel **Avanzado** oficial de la barra lateral del editor.
- **Soporte para Atributos Booleanos y Clave-Valor:**
  - Atributos estándar: `data-user-id="105"`, `aria-label="Cerrar modal"`, `title="Información"`.
  - Atributos simples / booleanos (sin valor asignado): `download`, `inert`, `novalidate`, `required`.
- **Presets de Inserción Rápida:** Acceso inmediato con un clic a los atributos más utilizados (`data-*`, `aria-label`, `title`, `download`, `role`, `tabindex`, `inert`).
- **Seguridad Robusta:**
  - Validación estricta de nombres según el estándar HTML5.
  - Bloqueo preventivo de inyección de código y eventos inline (`onclick`, `onload`, `on*`).
  - Mitigación de riesgos XSS con sanitización y escapado riguroso en backend (`esc_attr`).
- **Rendimiento Excepcional (Zero Frontend Bloat):**
  - No carga scripts ni hojas de estilo adicionales en el frontend.
  - Inyección en tiempo de renderizado con impacto despreciable en memoria y tiempo de respuesta ($O(1)$).
- **Arquitectura Oficial con WP_HTML_Tag_Processor:** Utiliza la API nativa de parsing de WordPress core (WP 6.2+) para una inyección de atributos infalible que no rompe la validación de bloques al desactivar el plugin.
- **Actualizaciones Automáticas desde GitHub:** Incorpora la librería _Plugin Update Checker_ (PUC) para recibir actualizaciones directamente en el panel de WordPress ante cada nueva versión publicada en GitHub.
- **Control de Capacidades (`unfiltered_html`):** Protección nativa en instalaciones multi-autor y Multisite.
- **Extensible para Desarrolladores:** Filtros PHP para personalizar la lista negra de atributos y los bloques no soportados (`mtwdev_custom_attributes_disallowed` y `mtwdev_custom_attributes_unsupported_blocks`).

---

## 📋 Requisitos

- **WordPress:** 6.0 o superior.
- **PHP:** 7.4 o superior (compatible con PHP 8.0, 8.1, 8.2 y 8.3).
- **Editor de bloques (Gutenberg):** Activo.

---

## 📦 Instalación

### Método 1: Descarga Directa (Usuarios finales)

1. Descarga el archivo ZIP de la última versión desde [Releases](https://github.com/GWKaplan/wp-custom-attributes/releases) o haz clic en el botón verde **Code > Download ZIP**.
2. En tu panel de administración de WordPress, dirígete a **Plugins > Añadir nuevo > Subir plugin**.
3. Selecciona el archivo ZIP descargado y haz clic en **Instalar ahora**.
4. Activa el plugin.
5. ¡Listo! Recibirás notificaciones de actualización automáticamente en tu panel de WordPress cuando haya nuevas versiones.

### Método 2: Clonado con Git (Para Desarrolladores)

1. Clona el repositorio dentro de tu directorio de plugins de WordPress:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/GWKaplan/wp-custom-attributes.git mtwdev-custom-attributes
   ```
2. Instala las dependencias y compila los assets del editor:
   ```bash
   cd mtwdev-custom-attributes
   pnpm install
   pnpm run build
   ```
3. Activa **MTWDev Custom Block Attributes** desde **Plugins > Plugins Instalados** (o con `wp plugin activate mtwdev-custom-attributes`).

---

## 🛠️ Cómo Utilizarlo

1. Abre cualquier entrada, página o plantilla en el **Editor de Bloques (Gutenberg)**.
2. Haz clic sobre cualquier bloque que desees personalizar (botón, grupo, imagen, párrafo, titular, etc.).
3. En la barra lateral derecha de ajustes del bloque, despliega la pestaña **Avanzado**.
4. Verás la sección **Atributos HTML Personalizados**:
   - Pulsa sobre cualquiera de los **Presets rápidos** (ej. `+data-*` o `+download`) o escribe manualmente el nombre del atributo.
   - Si se trata de un atributo booleano sin valor (como `download` o `inert`), marca la casilla **Atributo simple / booleano**.
   - Haz clic en **Añadir**.
   - Si no es booleano, introduce el valor deseado en el campo de texto correspondiente.
5. Guarda o publica tu contenido. El atributo se inyectará de forma limpia en la etiqueta raíz del bloque en el frontend.

---

## 🛡️ Seguridad y Buenas Prácticas

El plugin sigue escrupulosamente los estándares de código de WordPress (WPCS):

1. **Lista Negra de Atributos:**  
   Se deniegan automáticamente todos los atributos que comiencen por `on` (como `onclick`, `onerror`, `onload`) y atributos con riesgo de ejecución de código externo (como `srcdoc` o `formaction`).
2. **Sanitización y Escapado:**  
   Las claves se validan con regex `^[a-z0-9_\-:]+$` y los valores se filtran con `esc_attr()`, neutralizando intentos de salto de etiquetas o inyección HTML.
3. **Bloqueo de Pseudo-Protocolos:**  
   Se invalidan valores que contengan `javascript:`.
4. **Respeto a Capacidades Nativas:**  
   Si el usuario que edita no tiene la capability `unfiltered_html`, la interfaz deshabilita la edición y el filtro `pre_kses` sanea el contenido al guardar.

---

## 🔌 API y Extensibilidad

### Modificar o ampliar la lista de atributos restringidos

```php
add_filter('mtwdev_custom_attributes_disallowed', function (array $disallowed): array {
    // Bloquear atributos adicionales según la política de tu proyecto
    $disallowed[] = 'custom-dangerous-attr';
    return $disallowed;
});
```

### Excluir bloques adicionales

```php
add_filter('mtwdev_custom_attributes_unsupported_blocks', function (array $blocks): array {
    $blocks[] = 'mi-namespace/bloque-especial';
    return $blocks;
});
```

---

## 💻 Desarrollo y Compilación

Este plugin utiliza `@wordpress/scripts` para la compilación de JavaScript moderno (React/JSX) y SCSS:

```bash
# Instalar dependencias con pnpm
pnpm install

# Modo desarrollo con recarga automática en cambios (watch)
pnpm run start

# Compilación optimizada para producción
pnpm run build
```

---

## 🤝 Cómo Contribuir

¡Las contribuciones, sugerencias y mejoras son muy bienvenidas! Si deseas reportar un error o proponer una nueva funcionalidad, consulta nuestra [Guía de Contribución](CONTRIBUTING.md).

1. Haz un **Fork** de este repositorio.
2. Crea una rama para tu mejora (`git checkout -b feature/nueva-mejora`).
3. Realiza tus cambios asegurándote de compilar con `pnpm run build`.
4. Envía un **Pull Request** explicando detalladamente el cambio.

---

## 📄 Licencia

Este proyecto está bajo la licencia **GPL-2.0-or-later**. Consulta el archivo `LICENSE` o visita [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html) para más detalles.
