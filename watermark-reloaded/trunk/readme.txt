=== Watermark RELOADED ===
Contributors: sverde1
Donate link: https://coindrop.to/sverde1
Tags: watermark, image watermark, text watermark, image protection, copyright protection
Requires PHP: 5.3
Requires at least: 3.5
Tested up to: 6.9
Stable tag: 1.4.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically add customizable text watermarks to new images on upload to protect your WordPress media library.

== Description ==

**Watermark RELOADED** is a lightweight WordPress image watermark plugin that automatically adds a customizable text watermark to images when you upload them to your Media Library.

Use it to:

* Protect your work from **unauthorized reuse**
* Brand images with your **site name**, **URL**, or **copyright notice**
* Keep control of your visual content with a simple, set-and-forget solution

You can:

* Choose which image sizes to watermark: **thumbnail**, **medium**, **large**, **full size**
* Customize text watermark **font** and **color**
* Precisely control watermark **position** (corners, edges or centered) on the image
* Preview your watermark before saving settings so you know exactly how it will look

The plugin adds:

* A dedicated **Watermark RELOADED settings page** where you configure your watermark and see a live preview
* A **WordPress Dashboard widget** that shows your current watermark settings and a preview of the watermark directly on the main Dashboard screen

### Key Features

* **Automatic watermark on upload** – newly uploaded images are watermarked immediately
* **Text watermarking** – add copyright text, site name or any custom message
* **Custom fonts** – upload your own TTF fonts to use for the watermark
* **Color picker integration** – choose any text color for your watermark
* **Per-size control** – enable watermarking only on selected image sizes
* **Live settings preview** – see how your watermark text, color and position look in the plugin settings before they are applied to new uploads
* **Dashboard widget** – quickly review enabled sizes, position, offset and a preview from the WordPress Dashboard
* **Simple, focused UI** – no unnecessary settings, just what you need to protect your images

### How it works

1. You configure your watermark text, font, color, and position in **Settings → Watermark Reloaded**.
2. You choose which image sizes should receive the watermark.
3. When you upload a new image through the **Media Library** or post editor, Watermark RELOADED automatically applies the text watermark to the selected sizes.

Existing images in your Media Library are **not modified** – only new uploads are processed.

### Requirements

To generate watermarks, your server must support:

* **PHP 5.3+**
* **GD extension for PHP**
* **FreeType Library** (for rendering text)

If any of these are missing, the plugin will show an error message so you can contact your hosting provider.

### Planned improvements

The following ideas are on the to-do list and may be implemented in future updates:

* Additional bug fixes and compatibility adjustments
* Smarter handling of small images where the watermark might overflow
* Support for watermarking additional custom image sizes added by other plugins/themes
* Image-size aware watermarks and more flexible positioning options

== Installation ==

1. Upload the `watermark-reloaded` folder to the `/wp-content/plugins/` directory,  
   or install the plugin via **Plugins → Add New** and search for “Watermark RELOADED”.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → Watermark Reloaded**.
4. Configure your watermark text, font, color and position.
5. Select which image sizes should receive the watermark and save your changes.

You can upload additional fonts to the `/wp-content/plugins/watermark-reloaded/fonts/` folder (TTF format).

== Frequently Asked Questions ==

= The plugin doesn’t seem to work. What information do you need to help debug it? =

When reporting a problem, please include:

* A screenshot of your **Watermark RELOADED** settings page
* Any error messages shown on screen
* Relevant entries from your `php_error.log` or server error log
* The output of your `phpinfo()` so we can see your PHP/GD/FreeType configuration

= I’m getting an error that the GD extension is not installed. =

The plugin needs the **GD extension** to process images.  
Contact your hosting provider and ask them to enable GD for your hosting account. Without it, images cannot be watermarked.

= I’m getting an error that the FreeType Library is not installed. =

The plugin uses **FreeType** to render text as a watermark.  
Contact your hosting provider and ask them to install or enable the FreeType library on your server.

= Can I watermark images that were uploaded before I installed this plugin? =

Not at the moment. Watermark RELOADED only processes **new uploads**.  
Existing images already in your Media Library are not modified.

= Does Watermark RELOADED change my original full-size image? =

You can choose whether the **full-size** image should be watermarked in the plugin settings.  
If you prefer to keep the original full-size image untouched, simply uncheck the option for full-size images and only watermark the generated sizes (thumbnail, medium, large).

== Screenshots ==

1. Watermark RELOADED settings page with text, color and positioning options.
2. Dashboard widget showing enabled sizes, position, offset and a live watermark preview.
3. Example of an image watermarked with the configured settings.

== Changelog ==

= 1.0 =
* Initial release

= 1.0.1 =
* Fixed plugin URI
* Added some more fonts

= 1.0.2 =
* Added PHP 5 dependency check
* Added GD extension dependency check
* Added FreeType Library dependency check
* Rewritten error messages output
* Added donations link

= 1.2 =
* Added color picker for changing text watermark color
* Added watermark preview
* Added a little bit of a nagging for donation :)

= 1.2.1 =
* Fixed unicode chars bug

= 1.2.2 =
* Fixed "Enable watermark for" checkboxes save bug

= 1.2.3 = 
* Fixed "Could not find font" bug
* Updated donation nagging functionality

= 1.2.4 =
* Added auto-patch for font bug fixed in previous version

= 1.2.5 =
* Bugfix on upgrade to Wordpress 3.0

= 1.3 =
* Bugfixes and compatibility fixes

= 1.3.1 =
* Some bugfixes
* Plugin options page credentials update
* Added dashboard with current watermark settings and preview display

= 1.3.2 =
* Added file type check, because we need to process only image files

= 1.3.3 =
* Testing with new Wordpress versions
* Some bugfixes

= 1.3.4 =
* New Wordpress compatibility issues fixed
* Some bugfixes

= 1.3.5 =
* Fixed version issues

= 1.4.0 =
* Hardened preview AJAX handler with nonce validation and escaped settings output to prevent injected attributes.
* Replaced legacy ColorPicker with WordPress color picker and updated preview URL handling.
* Documented plugin classes/methods with PHPDoc and refreshed plugin headers/metadata.
* Adjusted watermark application flow to handle fullsize separately and simplify size iteration.
* Added localization support across admin UI strings.
* Improved escaping of admin outputs and preview text.
