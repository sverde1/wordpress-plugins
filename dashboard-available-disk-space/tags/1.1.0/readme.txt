=== Dashboard: Available Disk Space ===
Contributors: sverde1
Donate link: https://coindrop.to/sverde1
Tags: disk space, disk usage, storage, at a glance, dashboard
Requires PHP: 5.0
Requires at least: 3.3
Tested up to: 6.8.3
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show remaining server disk space directly inside the “At a Glance” dashboard widget so you immediately see when storage is getting low.

== Description ==

**Dashboard: Available Disk Space** extends the built-in **At a Glance** widget on your WordPress Dashboard with a compact **“Available Storage Space”** row.

When you open **Dashboard → Home**, you’ll immediately see:

* How much disk space is **used** and **free**
* A clear progress bar that fills up as your server disk gets full
* A quick visual indication when you’re running low on storage

This is useful if:

* You’re on shared hosting and want to avoid “out of disk space” surprises
* You manage multiple client sites and need a quick disk space check right after login
* You want a lightweight alternative to full server monitoring or security suites

The plugin is intentionally **small and dependency-free**:

* No extra admin menus
* No tracking or data collection
* Just one clean line in **At a Glance** with a visual bar and readable numbers

== Installation ==

1. Upload the `dashboard-available-disk-space` folder to the `/wp-content/plugins/` directory, or install the plugin via **Plugins → Add New** and search for “Dashboard: Available Disk Space”.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Dashboard → Home**. In the **At a Glance** widget you’ll see a new **“Available Storage Space”** row with disk usage and a progress bar.

== Frequently Asked Questions ==

= How is disk space calculated? =

The plugin uses PHP’s built-in `disk_total_space()` and `disk_free_space()` functions on the WordPress root path. This shows disk usage for the partition that hosts your WordPress installation.

= Will this work on any hosting provider? =

It should work on most Linux-based shared hosting and VPS servers that allow PHP to read disk information. If `disk_free_space()` is disabled on your server, the widget will show a warning instead of breaking your dashboard.

= Does this slow down my Dashboard? =

No. The widget performs a single disk check when the Dashboard loads. There are no scheduled tasks or background processes.

= Is this multisite compatible? =

Yes. Network-activate the plugin and the disk space widget will be available on the Network Admin Dashboard.

= Can I move it outside of At a Glance? =

Not at the moment. The plugin is designed to keep things simple by integrating directly into the existing **At a Glance** widget instead of adding new Dashboard widgets.


== Screenshots ==

1. The **At a Glance** widget with the new **“Available Storage Space”** row showing a progress bar and human-readable disk usage (used, free, and total).

== Changelog ==

= 1.0 =
* Initial release

= 1.0.1 =
* open_basedir restriction error bug fixed

= 1.0.2 =
* Some visual improvements
* Added multilanguaginal support
* Added comments to methods

= 1.0.3 =
* Wordpress 3.0 compatibility fixes

= 1.0.4 =
* Bugfixes

= 1.1.0 =
* Updated Dashboard integration to show disk information inside the core **At a Glance** widget.
* Added capability check for `upload_files` to control who sees the storage information.
* Added safer escaping, i18n-friendly formatting, and an inline-styled progress bar consistent with current WordPress admin colors.
* Introduced lightweight admin CSS for the “Available Storage Space” row and cleaner markup with translatable labels.
