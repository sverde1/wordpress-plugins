<?php
/**
 * Plugin Name: Dashboard: Available Disk Space
 * Description: Show remaining server disk space directly in the “At a Glance” dashboard widget with a progress bar and warning when storage is low.
 * Version: 1.1.1
 * Author: Sandi Verdev
 * Author URI: http://eAppz.eu/
 * Text Domain: dashboard-available-disk-space
 * Domain Path: /languages
 * Requires PHP: 5.3
 * Requires at least: 3.3
 * Tested up to: 6.9
 * License: GPLv2 or later
 *
 * @package   dashboard_available_disk_space
 * @author    Sandi Verdev <wordpress@eappz.eu>
 * @copyright 2025 eAppz
 */

if ( is_admin() ) {
	define( 'DADS_LOADER', __FILE__ );

	require_once __DIR__ . '/class-dashboard-available-disk-space.php';

	new Dashboard_Available_Disk_Space();
}
