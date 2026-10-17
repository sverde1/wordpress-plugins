# Custom WordPress Plugins

This repository contains a collection of WordPress plugins that I’ve developed for real-world projects.  
They focus on practicality, performance, and seamless integration with WordPress.

## Included Plugins

### Watermark RELOADED
A WordPress plugin for automatically adding watermarks to images.  
Useful for protecting visual content and branding media uploaded to your site.

Published on WordPress.org since 2010: https://wordpress.org/plugins/watermark-reloaded/  
**170k+ downloads.** The December 2025 update fixed CSRF/XSS issues (nonce validation and input escaping),
modernized the settings UI, added localization and brought the code up to WordPress Coding Standards
(see `ruleset.xml`).

### Dashboard: Available Disk Space
A small utility plugin that adds a widget to the WordPress Dashboard showing available disk space.  
Helps administrators monitor server storage directly from the WordPress admin area.

Published on WordPress.org since 2010: https://wordpress.org/plugins/dashboard-available-disk-space/  
**7,900+ downloads.** The December 2025 update moved the storage info into the core *At a Glance* widget
with a progress bar, limited it to users with the `upload_files` capability, added escaping, translatable
labels and open_basedir-safe disk checks, and brought the code up to WordPress Coding Standards.

## Goals

- Keep plugins lightweight and focused
- Follow WordPress coding standards
- Make setup and configuration as simple as possible

## Installation

1. Download or clone this repository.
2. Copy the desired plugin folder(s) into your `wp-content/plugins/` directory.
3. Activate each plugin in **WordPress Admin → Plugins**.
4. Configure plugin settings as needed (where applicable).

## License

All plugins in this repository are licensed under the **GPL-2.0-or-later** license.

You should have received a copy of the GNU General Public License  
along with this program. If not, see <https://www.gnu.org/licenses/>.