# Repository Guidelines

## Project Structure & Module Organization
- Root contains two WordPress plugins, each with Subversion-style layout: `dashboard-available-disk-space/{trunk,tags,branches}` and `watermark-reloaded/{trunk,tags,branches}`.
- Active code lives under each `trunk/`. Example: `dashboard-available-disk-space/trunk/dashboard-available-disk-space.php` (dashboard widget) and `watermark-reloaded/trunk/watermark-reloaded.php` plus assets (`fonts/`, `style.css`, `script.js`; older tags also include `jQueryPlugins/`).
- `readme.txt` files hold plugin metadata; screenshots sit beside the main plugin files for WordPress.org assets.

## Build, Test, and Development Commands
- No build step is required; plugins are plain PHP/JS/CSS. Symlink or copy the `trunk/` folder into your WordPress `wp-content/plugins/` directory to run locally:
  - `ln -s $(pwd)/dashboard-available-disk-space/trunk /path/to/wp-content/plugins/dashboard-available-disk-space`
  - `ln -s $(pwd)/watermark-reloaded/trunk /path/to/wp-content/plugins/watermark-reloaded`
- Quick syntax check before committing: `find dashboard-available-disk-space/trunk watermark-reloaded/trunk -name '*.php' -print0 | xargs -0 -n1 php -l`.
- Coding standards: `phpcs --standard=ruleset.xml <plugin dir>/trunk/`.
- Optional packaging for distribution: `zip -r dist/dashboard-available-disk-space.zip dashboard-available-disk-space/trunk` (same for `watermark-reloaded`).

## Coding Style & Naming Conventions
- Follow WordPress PHP standards: tabs for indentation, braces on the same line, and snake_case for functions/hooks; keep class names in StudlyCaps (e.g., `Dashboard_Available_Disk_Space`, `Watermark_Reloaded`).
- Use PHPDoc blocks for classes, methods, and hooks; prefer descriptive action/filter names.
- Keep assets organized in their existing folders; new images belong beside current screenshots, and new JS/CSS files should mirror existing naming (`script.js`, `style.css`).

## Testing Guidelines
- No automated test suite exists. Validate changes by activating each plugin in a local WordPress site and exercising core flows:
  - Dashboard plugin: verify activation hook runs and the disk-space widget appears on the Dashboard with accurate values.
  - Watermark plugin: test settings save/restore, watermark rendering on new uploads, and toggling donation/nag options.
- Keep manual test notes in PR descriptions; add reproducible steps for any bugfix.

## Commit & Pull Request Guidelines
- This repo lacks a Git history; use clear, imperative commit subjects (e.g., `Add disk space threshold notice`, `Fix watermark opacity on PNGs`).
- For pull requests: include a short summary, linked issue (if any), manual test results, and before/after screenshots when UI changes affect the Dashboard widget or watermark output.
- Keep changes scoped to one plugin per PR where possible; note cross-plugin impacts explicitly.

## Security & Configuration Tips
- Avoid direct `$_GET/$_POST` use; sanitize with `sanitize_text_field`, `intval`, or `wp_kses` as appropriate and escape output with `esc_html`, `esc_attr`, or `esc_url`.
- Use nonces for settings and admin actions; respect WordPress capability checks (e.g., `current_user_can('manage_options')`).
- When handling files for watermarks, rely on WordPress upload APIs and validate MIME types to prevent unsafe file handling.
