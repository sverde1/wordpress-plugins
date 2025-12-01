# WordPress 6.9 Compatibility Checklist (for Codex)

This document is a checklist Codex (and you) can use to review plugin code for compatibility with WordPress 6.9, based on the upcoming changes announced by the WordPress team.

Applies to at least:

- Plugin: **Dashboard – Available Disk Space**
- Current “Tested up to”: **6.8.3**
- Target “Tested up to”: **6.9**

---

## 1. General readme & metadata

### 1.1 `readme.txt` “Tested up to” header

**Static check**

- **Search for:**  
  - `Tested up to:` in `readme.txt` (stable tag directory).
- **Verify that:**  
  - The value is updated to:  
    `Tested up to: 6.9`
- **Action if not:**  
  - After all compatibility checks pass, update this header and commit.

---

## 2. Post Editor: iframe integration & block API version

WordPress 6.9 treats blocks using `apiVersion: 2` as **legacy** and shows a browser warning. The post editor is in transition to run inside an iframe.

### 2.1 Custom blocks using `block.json`

**Static check**

- **Files to scan:**  
  - `**/block.json`
- **Search for patterns:**
  - `"apiVersion": 1`
  - `"apiVersion": 2`
- **Flag as potential issues if:**
  - Any block uses `apiVersion` 1 or 2.
- **Recommended action:**
  - Plan to migrate each block to `"apiVersion": 3`.
  - Ensure any editor-side logic is compatible with iframe context (no assumptions about direct access to the top window).

### 2.2 Block registration without `block.json`

**Static check**

- **Files to scan:**  
  - All JS/TS: `assets/**/*.js`, `src/**/*.js`, `src/**/*.ts`, etc.
- **Search for patterns (case-sensitive):**
  - `registerBlockType(`
  - `wp.blocks.registerBlockType(`
- **Flag for manual review:**
  - Any block registration that does **not** specify `apiVersion: 3` in settings.
- **Recommended action:**
  - Confirm blocks are declared with `apiVersion: 3` where applicable.
  - Confirm no code breaks when the editor runs inside an iframe.

### 2.3 Editor iframe assumptions in JS

**Static check**

- **Files to scan:**  
  - All JS/TS used in the editor (enqueue handles that are `editor_script`, `enqueue_block_editor_assets`, etc.).
- **Search for patterns:**
  - `window.parent`
  - `window.top`
  - `parent.postMessage`
  - Direct DOM references that may assume top-level context, for example:
    - `document.body`  
    - `document.querySelector('body.editor-styles-wrapper')`
- **Flag as potential issues if:**
  - Code assumes the editor is not inside an iframe or relies on manipulating the top window.
- **Manual follow-up:**
  - Ensure any cross-frame interaction is robust if the editor is fully iframed.
  - Prefer standard editor APIs over direct DOM manipulation where possible.

---

## 3. Abilities API & MCP Adapter (optional adoption)

WordPress 6.9 introduces the **Abilities API** and an **MCP Adapter** to expose abilities to AI assistants. These are optional for compatibility but useful for future-proofing.

### 3.1 Abilities API usage

**Static check**

- **Files to scan:** PHP + JS.
- **Search for patterns:**
  - `Abilities` (case-insensitive)
  - `Abilities API`
  - Any custom integration you may have added (project-specific namespaces).
- **Action:**
  - If the plugin does **not** use the Abilities API, this is **not** a compatibility blocker.
  - If you decide to adopt it, ensure abilities are registered in a stable, machine-readable way and avoid breaking back-compat for users without AI tools enabled.

### 3.2 MCP Adapter assumptions

**Static check**

- **Files to scan:** PHP + JS.
- **Search for patterns:**
  - `MCP`
  - `Model Context Protocol`
  - `mcp-adapter`
- **Action:**
  - If the plugin integrates with the MCP adapter, ensure:
    - All abilities exposed are valid and up to date.
    - No hard-coded assumptions about MCP schema versions that could break on update.

---

## 4. UTF-8 support modernization

WordPress 6.9 overhauls UTF-8 support, deprecating `seems_utf8()`. PHP 8.2 also deprecated `utf8_encode()` and `utf8_decode()`.

### 4.1 Deprecated encoding helpers

**Static check**

- **Files to scan:** All PHP.
- **Search for patterns:**
  - `seems_utf8(`
  - `utf8_encode(`
  - `utf8_decode(`
- **Flag as issues if:**
  - Any of these functions are called.
- **Recommended action:**
  - Replace usage with:
    - Core’s new UTF-8 validation/scrubbing functions where available.
    - `mb_detect_encoding()`, `mb_convert_encoding()`, or other multibyte-safe utilities.
  - Avoid manual byte-level string operations where possible.

### 4.2 Custom UTF-8 handling

**Static check**

- **Files to scan:** All PHP.
- **Search for patterns (heuristic):**
  - `'UTF-8'` (case-sensitive) in combination with:
    - `preg_match`
    - `preg_replace`
    - manual loop over `strlen($string)` using `ord()` / `chr()`
- **Flag for manual review if:**
  - There is complex or fragile custom UTF-8 validation.
- **Manual follow-up:**
  - Verify custom logic still behaves correctly with the new UTF-8 pipeline in 6.9.
  - Prefer WordPress-provided helpers for UTF-8 checks where possible.

---

## 5. Frontend performance & asset loading

WordPress 6.9 brings performance changes like support for `fetchpriority` on scripts, script modules in the footer, omission of styles for hidden blocks, and a template enhancement output buffer.

### 5.1 Hidden blocks relying on core styles

**Static check**

- **Files to scan:**
  - Block PHP render callbacks.
  - Block JS/TS.
  - Plugin CSS.
- **Search for patterns:**
  - Blocks that:
    - Are always hidden via CSS classes.
    - Rely on core’s editor or front-end styles for hidden-state behavior.
- **Flag for manual review:**
  - Blocks that are “hidden but styled” and might break if styles are omitted for hidden blocks.
- **Manual follow-up:**
  - Ensure any visually hidden content still works when core omits styles for hidden blocks.
  - Provide plugin-specific styles if necessary.

### 5.2 Script loading & modules

**Static check**

- **Files to scan:**
  - PHP enqueue functions.
- **Search for patterns:**
  - `wp_enqueue_script(`
  - `wp_register_script(`
- **Flag for manual review if:**
  - Scripts are manually printed or manipulated in a way that might conflict with:
    - Script modules being printed in the footer.
    - New `fetchpriority` usage.
- **Manual follow-up:**
  - Ensure scripts that must execute early are correctly marked and not affected by footer/module changes.
  - Do not rely on legacy IE conditional loading (see §8.2).

---

## 6. `wp_mail()` and inline embedded images

WordPress 6.9 adds support for inline embedded images in HTML emails using `wp_mail()`.

### 6.1 `wp_mail()` usage

**Static check**

- **Files to scan:** All PHP.
- **Search for patterns:**
  - `wp_mail(`
- **Flag for manual review if:**
  - The plugin customizes headers, MIME boundaries, or attachments in non-standard ways.
- **Manual follow-up:**
  - Ensure emails still render correctly with the new inline image support.
  - Optionally adopt inline images instead of requiring remote image downloads.

---

## 7. Miscellaneous editor-related changes

WordPress 6.9 includes editor changes like deprecating the `media` entity in `@wordpress/core-data` and moving SelectControl classnames to the component root.

### 7.1 `@wordpress/core-data` media entity usage

**Static check**

- **Files to scan:** All JS/TS.
- **Search for patterns:**
  - `@wordpress/core-data`
  - `'core/media'`
  - `getMedia(`
- **Flag as potential issues if:**
  - The plugin relies on the deprecated `media` entity.
- **Recommended action:**
  - Update to the recommended replacement API from the 6.9 docs for media handling.

### 7.2 `SelectControl` CSS/class dependencies

**Static check**

- **Files to scan:**
  - JS/TS using `SelectControl`.
  - Plugin CSS that styles Gutenberg components.
- **Search for patterns:**
  - `SelectControl` imports from `@wordpress/components`.
  - CSS selectors containing:
    - `.components-select-control`
    - `.components-base-control`
    - or other `components-` prefixed classes.
- **Flag for manual review if:**
  - CSS assumes a specific internal DOM structure or nested class hierarchy.
- **Manual follow-up:**
  - Confirm styles still apply correctly now that classnames have moved to the component root.
  - Simplify selectors to be less dependent on internal markup.

---

## 8. Miscellaneous developer changes & deprecations

WordPress 6.9 adds PHP 8.5 polyfills, new hooks, and removes the last remnants of Flash support.

### 8.1 Flash-related assets and logic

**Static check**

- **Files to scan:** All PHP, JS, and assets.
- **Search for patterns:**
  - `.swf`
  - `.flv`
  - `ShockwaveFlash`
  - `application/x-shockwave-flash`
- **Flag as issues if:**
  - Any Flash-related asset names, MIME types, or feature checks are present.
- **Recommended action:**
  - Remove all remaining Flash dependencies and references.
  - Replace any Flash-based functionality with modern equivalents (HTML5, JS, etc.).

### 8.2 Internet Explorer–specific handling

**Static check**

- **Files to scan:** PHP, JS, templates.
- **Search for patterns:**
  - `<!--[if IE]>`
  - `<!--[if lt IE`
  - `<!--[if lte IE`
  - Any code branching explicitly on legacy IE.
- **Flag for cleanup if:**
  - Legacy IE handling is still present.
- **Recommended action:**
  - Remove obsolete IE-specific logic and conditional comments.

### 8.3 PHP 8.5 compatibility (high level)

**Static check**

- **Files to scan:** All PHP.
- **Search for patterns (heuristics):**
  - Dynamic property usage: `$object->{"$variable"}` or property access without definition.
  - Use of deprecated features known to be removed by PHP 8.x (project-specific).
- **Manual follow-up:**
  - Run automated tests under PHP 8.5 if possible.
  - Fix any warnings/notices that show up.

---

## 9. Plugin Check & WordPress Beta Tester

These are external tools, but include them as part of the compatibility workflow.

### 9.1 Plugin Check

**Manual action (outside Codex)**

- Install **Plugin Check** on a test site.
- Run it against the plugin.
- Resolve:
  - Compliance issues.
  - Security issues.
  - License issues.
  - Any notices related specifically to WordPress 6.9.

### 9.2 WordPress 6.9 Release Candidate testing

**Manual action (outside Codex)**

- Install the **WordPress Beta Tester** plugin.
- Switch the site to the **6.9 Release Candidate** channel.
- Test:
  - Activation/deactivation.
  - All admin screens provided by the plugin.
  - Block editor integration (if any).
  - Frontend output (shortcodes, blocks, widgets, REST endpoints).
- Confirm there are **no errors, warnings, or deprecations** in the debug log related to this plugin.

---

## 10. Final steps

**After all checks above pass:**

1. Ensure `readme.txt` has:  
   `Tested up to: 6.9`
2. Tag a new stable version (if needed).
3. Deploy to WordPress.org.
4. Re-run Plugin Check periodically as WordPress.org starts scanning plugin updates automatically.

---
