<?php
/**
 * Plugin Name: Watermark RELOADED
 * Description: Automatically add text watermarks to WordPress uploads with adjustable fonts, colors, and positions.
 * Version: 1.4.0
 * Author: Sandi Verdev
 * Author URI: http://eAppz.eu/
 * Text Domain: watermark-reloaded
 * Domain Path: /languages
 * Requires PHP: 5.3
 * Requires at least: 3.5
 * Tested up to: 6.9
 * License: GPLv2 or later
 *
 * @package   watermark_reloaded
 * @author    Sandi Verdev <sandi.verdev@eappz.eu>
 * @copyright 2025 eAppz
 */

define( 'WR_MIN_PHP_VERSION', '5.1' );

/**
 * Activation hook callback to validate PHP version before enabling the plugin.
 *
 * @return void
 */
function watermark_reloaded_activate() {
	if ( version_compare( PHP_VERSION, WR_MIN_PHP_VERSION, '<' ) ) {
		// Deactivate the plugin immediately.
		deactivate_plugins( plugin_basename( __FILE__ ) );

		$message = sprintf(
			/* translators: %s: required minimum PHP version number. */
			__( 'Watermark RELOADED requires PHP %s or higher. The plugin has been deactivated.', 'watermark-reloaded' ),
			WR_MIN_PHP_VERSION
		);

		wp_die(
			esc_html( $message ),
			esc_html__( 'Plugin Activation Error', 'watermark-reloaded' ),
			array(
				'back_link' => true,
			)
		);
	}
}

register_activation_hook( __FILE__, 'watermark_reloaded_activate' );

/**
 * Handle WordPress error scraping when PHP version is too low.
 *
 * WordPress calls plugins with ?action=error_scrape when checking for fatal errors.
 *
 * @return void
 */
function watermark_reloaded_error_scrape() {
	if ( version_compare( PHP_VERSION, WR_MIN_PHP_VERSION, '<' ) ) {
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( 'error_scrape' === $action ) {
			$message = sprintf(
				/* translators: %s: required minimum PHP version number. */
				__( 'Sorry, Watermark RELOADED requires PHP %s or higher. Please contact your hosting provider to upgrade PHP.', 'watermark-reloaded' ),
				WR_MIN_PHP_VERSION
			);

			wp_die(
				esc_html( $message ),
				esc_html__( 'Watermark RELOADED – PHP Version Error', 'watermark-reloaded' )
			);
		}

		// On unsupported PHP, do not load the rest of the plugin at all.
		return;
	}
}

watermark_reloaded_error_scrape();


// Load Watermark RELOADED plugin core only in admin.
if ( is_admin() ) {
	define( 'WR_LOADER', __FILE__ );

	require_once __DIR__ . '/class-watermark-reloaded.php';
	require_once __DIR__ . '/class-watermark-reloaded-admin.php';

	new Watermark_Reloaded_Admin();
}
