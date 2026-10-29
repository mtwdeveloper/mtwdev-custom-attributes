# Guía de Contribución a Custom Block Attributes

_Leer en otros idiomas:_ [English](CONTRIBUTING.md) | [Español](CONTRIBUTING.es.md)

¡Gracias por tu interés en contribuir a **Custom Block Attributes**! Este proyecto es de código abierto y agradecemos enormemente las sugerencias, reportes de errores y aportaciones de código de la comunidad.

Para asegurar un desarrollo ordenado, consistente y de alta calidad, te pedimos que leas y sigas las siguientes directrices antes de colaborar.

---

## 🛠️ Entorno de Desarrollo y Flujo de Trabajo

### 1. Requisitos Previos

- **Node.js:** v20.x o superior.
- **pnpm:** Recomendado como gestor de paquetes (`npm install -g pnpm`).
- **PHP:** 7.4 o superior (preferiblemente PHP 8.2+).
- **WordPress:** Entorno local con WordPress 6.2+ y Laravel Herd, LocalWP u otro servidor local.

### 2. Flujo de Trabajo para Pull Requests

1. **Haz un Fork** del repositorio en tu cuenta de GitHub.
2. **Clona tu fork** dentro de la carpeta `wp-content/plugins/` de tu instalación local de WordPress:
   ```bash
   git clone https://github.com/TU-USUARIO/mtwdev-custom-attributes.git mtwdev-custom-attributes
   cd mtwdev-custom-attributes
   ```
3. **Crea una rama descriptiva** para tu aportación:
   ```bash
   git checkout -b feature/nombre-de-la-mejora
   # o bien para un bugfix:
   git checkout -b fix/descripcion-del-error
   ```
4. **Instala las dependencias** con `pnpm`:
   ```bash
   pnpm install
   ```
5. **Inicia el modo de desarrollo**:
   ```bash
   pnpm run start
   ```
6. **Realiza tus cambios** siguiendo los estándares de código (ver sección siguiente).
7. **Compila la versión para producción**:
   ```bash
   pnpm run build
   ```
   > ⚠️ **Importante:** Asegúrate de ejecutar `pnpm run build` y comprobar que no hay errores de sintaxis antes de enviar el commit.
8. **Envía tus cambios a tu fork y abre un Pull Request (PR)** hacia la rama `main` del repositorio original, explicando con claridad qué problema resuelve y cómo probarlo.

---

## 📏 Estándares de Código y Directrices

Para mantener la integridad y calidad del proyecto, solicitamos cumplir con las siguientes pautas:

### PHP y WordPress

- Sigue los **WordPress Coding Standards (WPCS)**.
- Aplica tipado estricto de parámetros y retornos cuando sea posible.
- **Seguridad Absoluta:**
  - Sanitiza siempre las entradas con funciones nativas (`sanitize_text_field`, `sanitize_key`, etc.).
  - Valida los nombres de atributos antes de persistir o renderizar.
  - Escapa siempre las salidas con `esc_attr()` o utiliza `WP_HTML_Tag_Processor`.
  - Nunca permitas la inyección de manejadores de eventos JavaScript inline (`on*`) ni pseudo-protocolos `javascript:`.
- **Rendimiento:**
  - No inyectes scripts ni estilos en el frontend salvo que sea estrictamente indispensable. La inyección en el DOM debe resolverse en tiempo de renderizado con $O(1)$.

### JavaScript y Gutenberg

- Utiliza sintaxis estándar moderna de **React / JSX**.
- Emplea siempre los paquetes oficiales de WordPress (`@wordpress/block-editor`, `@wordpress/components`, `@wordpress/compose`, `@wordpress/element`, `@wordpress/hooks`, `@wordpress/i18n`).
- **Prohibido** usar `wp.element.createElement` manual en el código fuente de `src/`.
- Aplica internacionalización en todos los textos de la interfaz mediante `__('...', 'mtwdev-custom-attributes')`.

---

## 🌍 Traducciones

El plugin **no incluye traducciones compiladas**. Las cadenas base están en inglés y cada idioma se sirve como language pack que WordPress.org genera a partir de las traducciones de la comunidad.

1. Toda cadena visible debe usar el text domain del plugin: `__('...', 'mtwdev-custom-attributes')` en PHP y JavaScript.
2. Si añades o cambias una cadena, regenera la plantilla y haz commit:
   ```bash
   pnpm run i18n:pot
   ```
3. Una vez publicado, las traducciones se gestionan en [translate.wordpress.org](https://translate.wordpress.org/). **No** vuelvas a añadir archivos `.po`, `.mo` ni `.json` de scripts a `languages/`.
4. No edites el `.pot` a mano: se genera desde el código.

---

## 🚀 Publicación de Versiones

WordPress.org no compila nada, así que `build/` debe estar commiteado y viajar en el paquete.

1. Sube la versión en `mtwdev-custom-attributes.php` (cabecera y `MTWDEV_CUSTOM_ATTRIBUTES_VERSION`), `readme.txt` (`Stable tag`) y `package.json`.
2. Añade una entrada `= x.y.z =` en `== Changelog ==` de `readme.txt`.
3. Ejecuta `pnpm run build` y `pnpm test`.
4. Pasa Plugin Check (Herramientas → Plugin Check) con **todas** las categorías, incluida Accessibility.
5. Genera el paquete de distribución:
   ```bash
   git archive --worktree-attributes -o mtwdev-custom-attributes.zip HEAD
   ```
   `.gitattributes` (`export-ignore`) deja fuera `tests/`, `.git*`, los lockfiles y todos los `.md` de la raíz. `.distignore` replica esa lista para `10up/action-wordpress-plugin-deploy`, que usa `.distignore` y no lee `.gitattributes`.
6. La publicación sube a SVN `trunk/` (`build/`, `readme.txt`, `LICENSE`, `languages/`, `src/`) y crea `tags/x.y.z/`. Las capturas, el banner y el icono van a `/assets/` de SVN, nunca dentro de la carpeta del plugin.
7. `Contributors:` en `readme.txt` debe ser tu usuario real de wordpress.org; si no, el plugin no aparece en tu perfil y no recibirás los avisos de soporte.

---

## 🐛 Reportar Errores (Bugs)

Si encuentras un error o comportamiento inesperado:

1. Comprueba en la pestaña [Issues](https://github.com/mtwdeveloper/wp-custom-attributes/issues) que no haya sido reportado previamente.
2. Abre un nuevo issue utilizando un título claro y conciso.
3. Incluye:
   - **Descripción del problema:** Qué esperabas que sucediera y qué sucedió en realidad.
   - **Pasos detallados para reproducirlo.**
   - **Entorno técnico:** Versión de WordPress, versión de PHP, navegador y tema activo.
   - **Capturas de pantalla o logs de consola** si procede.

---

## 💡 Sugerir Nuevas Funcionalidades

Si tienes una idea para mejorar el plugin:

- Te sugerimos abrir primero una discusión o un Issue describiendo el caso de uso antes de invertir horas escribiendo código en un PR.
- Esto nos permite alinearnos en la arquitectura y asegurarnos de que la propuesta encaja con los principios de ligereza y seguridad del plugin.

---

## 🤝 Código de Conducta

Esperamos que todas las interacciones en este proyecto sean:

- **Respetuosas y profesionales:** Trata a todos los participantes con cortesía y empatía.
- **Constructivas:** Céntrate en ofrecer retroalimentación útil y fundamentada técnicamente.
- **Inclusivas:** Da la bienvenida a colaboradores de todos los niveles de experiencia.
