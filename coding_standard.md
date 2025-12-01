# Envato WordPress Plugin Coding Rules (For Code Analysis / Auto-Fixing)

Use these rules to **analyze, review, or modify WordPress plugin code intended for Envato Market (CodeCanyon)**.

When fixing code, **do not break functionality** if possible. Prefer small, focused changes that bring the code in line with these rules.

---

## 0. Overall Principles

1. Follow the **official Envato “WordPress Plugin Requirements”**.
2. Follow **WordPress.org coding standards** for PHP, JavaScript, CSS, HTML, and internationalization.
3. Prefer **WordPress core APIs** over custom implementations.
4. Prefer **security and data integrity** over performance and style, in this order:
   1. Security
   2. Data integrity / compatibility
   3. Performance
   4. Code style / formatting

---

## 1. WordPress Core Rules

When analyzing or fixing code, ensure that:

1. **No deprecated WordPress functions** are used anywhere.
2. **Admin code is separated from public code** using `is_admin()` checks.
3. Everything is **properly prefixed** with a unique plugin prefix:
   - Functions
   - Classes
   - Hooks (actions/filters)
   - Global/public variables
   - Options / meta keys / custom DB entries
4. Prefer **WordPress core functions/APIs** instead of reinventing:
   - HTTP API (`wp_remote_get()`, etc.)
   - Options API (`get_option()`, `update_option()`)
   - Filesystem API
   - Transients, etc.
5. Any **third-party API calls** are **cached aggressively** (transients or similar).
6. The plugin should be compatible with the **latest WordPress version** and any other advertised integrations (e.g. WooCommerce).

When fixing code:
- Replace deprecated functions with their recommended alternatives.
- Add consistent prefixes to unprefixed functions/classes/hooks/options.

---

## 2. Database Rules

When reviewing or modifying DB-related code:

1. **No direct DB access** via `mysqli_*`, `PDO`, or manual connections.
   - Always use `$wpdb` and the **WordPress Database API**.
2. **Minimize custom tables**:
   - Prefer custom post types and taxonomies where possible.
3. **All custom queries must use `$wpdb->prepare()`** to avoid SQL injection.
4. **Never use `SELECT *`**; explicitly list columns.
5. Cache results of **large or mostly static queries**.
6. Follow **sensible DB design** for any custom tables (indexes, primary keys, etc.).

When fixing code:
- Replace raw SQL building with `$wpdb->prepare()`.
- Switch from direct `mysqli_*`/`PDO` to `$wpdb`.

---

## 3. Security Rules

When analyzing or fixing anything related to input/output:

1. **Validate, sanitize, and escape everything**:
   - Validate → Sanitize (with `sanitize_*()` functions) → Escape on output (`esc_*()` functions).
2. Prefer **whitelisting** allowed values instead of blacklisting disallowed ones.
3. Use **nonces** for any sensitive or state-changing actions to prevent CSRF.
4. Use **capability checks** like `current_user_can()` for all admin actions and pages.
5. All custom DB queries must be run through `$wpdb->prepare()`.

When fixing code:
- Add missing sanitization on `$_POST`, `$_GET`, `$_REQUEST`, `$_COOKIE`, etc.
- Add missing escaping to `echo`/HTML output.
- Add nonce checks and capability checks to admin actions/forms.

---

## 4. Asset Loading (JS & CSS)

For JavaScript and CSS:

1. **Do not deregister or replace WordPress’s default jQuery**.
2. **Do not load JS/CSS directly** via hardcoded `<script>` / `<link>` tags in PHP templates.
3. **Do not load duplicate/alternate versions** of core libraries (especially jQuery).
4. Always load plugin assets via:
   - `wp_enqueue_script()`
   - `wp_enqueue_style()`

When fixing code:
- Remove direct `<script src>` and `<link>` injections from PHP and move them into enqueue hooks.
- Avoid bundling another jQuery file.

---

## 5. Installation & Uninstallation

1. **Deactivation must not delete data.**
   - Deactivation should only disable the plugin’s functionality.
2. **Uninstallation should:**
   - Prompt the user that data will be deleted.
   - Obtain explicit confirmation.
   - Provide an option to **keep the data**.
   - Optionally provide **export/import settings** before deletion.
3. Implement uninstall logic via:
   - `uninstall.php`, or
   - `register_uninstall_hook()`.

When fixing code:
- Ensure `register_deactivation_hook()` does **not** delete options/tables.
- Move destructive logic into uninstall flow with confirmation.

---

## 6. Translation / Internationalization (i18n)

1. **All user-facing strings must be translatable** and properly escaped:
   - Use `__()`, `_e()`, `_n()`, `_x()`, etc., combined with `esc_html__`, `esc_attr__`, etc. as needed.
2. **Do not embed variables directly into translation strings**:
   - Use `printf()`, `sprintf()`, or similar with placeholders.
   - Example: `printf( esc_html__( 'Hello, %s!', 'my-text-domain' ), esc_html( $username ) );`
3. **Text domain rules:**
   - Must be **lowercase**, **dash-separated**, and plain text.
   - Do **not** use constants or variables as text domains.
   - Remove unused / irrelevant domains.
4. Provide an up-to-date **`.pot` file** for the plugin.
   - `.po` / `.mo` files are allowed, but **not** `en_US.po` / `en_US.mo`.

When fixing code:
- Wrap bare strings in translation functions.
- Split dynamic strings into static translatable strings + placeholders.
- Normalize the text domain usage.

---

## 7. General Envato Guidelines

1. Documentation must be included (locally or online).
2. The plugin must **not transmit data to third-party servers** (including author’s servers) without:
   - Informing the user what is sent, and
   - Getting explicit opt-in, and
   - Allowing opt-out at any time.
3. **No upselling / advertising** of premium services inside WP Admin.
4. Custom update systems must **not interfere** with:
   - WordPress core updates
   - Other plugins/themes update detection and notifications.
5. Admin notices:
   - Review/activation nags must be **dismissible** and must not reappear once dismissed.
   - Non-dismissible messages are only allowed **inside the plugin’s own admin pages**.
   - A “verify purchase to get updates” message may be shown inline where update notifications usually appear.

When fixing code:
- Remove or limit admin ads/nags.
- Ensure dismissible notices store a permanent flag (e.g. option/user-meta).

---

## 8. PHP Coding Standards

1. Code must run with:
   - `WP_DEBUG` enabled
   - `error_reporting(E_ALL)`
   - **No notices, warnings, or errors**.
2. Use only full `<?php` tags (no `<?` or `<?=`).
3. Files must be UTF-8 **without BOM**.
4. Do **not** use POSIX regex functions `ereg_*`.
5. Prefer **strict equality** `===` over `==` when possible.
6. All blocks must use braces:
   - Opening brace on the **same line** as the control structure or function definition.
7. **`eval()` is not allowed.**
8. For larger projects, **dependency management** (e.g., Composer) is recommended.

When fixing code:
- Replace `eval()` and deprecated features.
- Normalize indentation (tabs preferred) and brace style.
- Fix any patterns that would cause PHP notices/warnings.

---

## 9. JavaScript Standards

1. **No inline JavaScript**; use external .js files and enqueue them.
2. Avoid globals. If a global is needed:
   - Explicitly attach it to `window`.
   - Prefix with a unique plugin prefix.
3. Naming conventions:
   - `camelCase` for variables & regular functions.
   - `PascalCase` for constructor functions.
4. Always use braces and semicolons.
5. Prefer `===` over `==`.
6. **No `eval()` in JS.**
7. Minimize DOM reflows and group DOM updates.
8. Unbind event handlers before rebinding.
9. Always provide a **radix to `parseInt()`**.
10. Code must:
    - Generate **no console errors or warnings**.
    - **Pass JSHint** with default options and **strict mode** enabled.

When fixing code:
- Move inline scripts into external files.
- Add `'use strict';` at the top of script files (if compatible).
- Replace unsafe patterns and add missing semicolons/braces.

---

## 10. CSS Standards

1. Prefer **classes instead of IDs** for styling.
2. Main stylesheet must have a **table of contents at the top**.
3. Avoid excessively specific or over-qualified selectors:
   - `div.container` → `.container`.
4. Use human-readable, consistent class names.
5. Keep **media queries grouped by media type** at the bottom of the stylesheet.
6. Use an **external CSS file**; no inline styles in markup.
7. Use comments to mark start/end of sections in the stylesheet.
8. Avoid `@import` in CSS.
9. Avoid `!important` except when absolutely necessary.
10. When styling JS/UI components, ensure that **only the plugin’s elements** are affected, not global elements from other items.

When fixing code:
- Move inline styles into stylesheets.
- Simplify selectors and introduce section comments / TOC if missing.

---

## 11. HTML Standards

1. Run HTML through a validator and avoid:
   - Unclosed tags
   - Nesting errors
   - Duplicate IDs
2. Indentation should reflect logical structure.
3. Tabs are recommended for indentation.

When fixing code:
- Clean up markup to avoid validation errors that could cause soft rejects.

---

## 12. How the Code Model Should Use These Rules

When you (the code model) receive plugin code:

1. **Identify violations** of any rules above.
2. For each violation:
   - Explain which rule is broken.
   - Show a **before/after code snippet** when possible.
3. Apply fixes that:
   - Preserve behavior where possible.
   - Increase compliance with Envato’s plugin requirements.
4. When in doubt, **choose the safer option** (more secure, more compatible, more standards-compliant).
5. Keep changes **minimal and localized**, unless a larger refactor is clearly justified.

---
