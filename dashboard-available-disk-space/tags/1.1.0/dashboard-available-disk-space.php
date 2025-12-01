<?php
/*
Plugin Name:       Dashboard: Available Disk Space
Description:       Show remaining server disk space directly in the “At a Glance” dashboard widget with a progress bar and warning when storage is low.
Version:           1.1.0
Author:            Sandi Verdev
Author URI:        http://eAppz.eu/
Text Domain:       dashboard-available-disk-space
Domain Path:       /languages
Requires PHP:      5.0
Requires at least: 3.3
Tested up to:      6.8.3
License:           GPLv2 or later
*/

class Dashboard_Available_Disk_Space {
	/**
	 * Class constructor
	 */
	public function __construct() {
		// register installer function
		register_activation_hook(__FILE__, array($this, 'activateDADS'));

		add_filter('plugin_row_meta',  array(&$this, 'add_plugin_links'), 10, 2);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
		add_action('rightnow_end', array($this, 'render_glance_line'), 20);
	}

	/**
	 * Plugin installation method
	 */
	public function activateDADS() {
		// record install time
		add_option('DADS_installed', time(), null, 'no');
	}

	/**
	 * Add extra plugin links to the plugins list.
	 *
	 * @param array  $links Existing plugin row links.
	 * @param string $file  Plugin basename from the plugins table.
	 * @return array Filtered plugin row links.
	 */
	public function add_plugin_links($links, $file) {
		if($file == plugin_basename(__FILE__)) {
			$links[] = '<a href="https://coindrop.to/sverde1">Donate</a>';
		}

		return $links;
	}

	/**
	 * Enqueue lightweight styles for the dashboard widget.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_admin_styles($hook) {
		if ('index.php' !== $hook) {
			return;
		}

		$css = "
		.dads-glance-storage {
			color: #2c3338;
			display: flex;
			flex-direction: column;
			gap: 4px;
		}
		.dads-glance-storage .dads-meta {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			line-height: 1.4;
			flex-wrap: wrap;
		}
		.dads-glance-storage .dads-meta a {
			margin-left: auto;
		}
		.dads-glance-storage .dads-meta .total {
			color: #646970;
			margin-top: 2px;
		}
		.dads-glance-storage .dads-meta-unavailable {
			margin: 5px 0;
		}
		.dads-glance-storage .dashicons {
			vertical-align: middle;
			margin: 0;
		}
		.dads-glance-storage .dads-bar {
			margin-top: 5px;
			height: 14px;
			background: #f0f0f1;
			border: 1px solid #c3c4c7;
			border-radius: 6px;
			display: block;
		}
		.dads-glance-storage .dads-bar__fill {
			height: 100%;
			border-radius: 6px;
		}
		";

		wp_add_inline_style('dashboard', $css);
	}

	/**
	 * Render a compact storage line in the "At a Glance" widget.
	 *
	 * @return void
	 */
	public function render_glance_line() {
		if (! current_user_can('upload_files')) {
			return;
		}

		?>
		<div class="dads-glance-storage">
			<span class="dads-meta">
				<span class="dashicons dashicons-database" aria-hidden="true"></span>
				<strong><?php _e('Available Storage Space', 'dashboard-available-disk-space'); ?></strong>
				<a href="<?php echo esc_url(admin_url('upload.php')); ?>">
					<?php esc_attr_e('Manage Uploads', 'dashboard-available-disk-space'); ?>
				</a>
			</span>
		<?php

		$stats = $this->_get_disk_stats();

		$stats !== false
			? $this->_render_disk_stats($stats)
			: $this->_render_disk_stats_unavailable();

		?>
		</div>
		<?php
	}

	/**
	 * Output the storage bar and stats.
	 *
	 * @param array $stats Disk metrics from _get_disk_stats().
	 * @return void
	 */
	private function _render_disk_stats($stats) {
		$percent_label     = number_format_i18n($stats['percent_used'], 1);
		$percent_for_width = number_format(max(0, min(100, $stats['percent_used'])), 2, '.', '');
		$fill_color        = $this->_get_fill_color($stats['percent_used']);

		?>
			<div class="dads-bar">
				<div class="dads-bar__fill" style="background: <?php echo esc_attr($fill_color); ?>; width: <?php echo esc_attr($percent_for_width); ?>%;"></div>
			</div>
			<div class="dads-meta">
				<div title="<?php esc_attr_e('(used / free) total space available', 'dashboard-available-disk-space'); ?>">
					<strong><?php echo esc_html($stats['used_readable']); ?></strong>
					<?php _e('used', 'dashboard-available-disk-space'); ?> /
					<strong><?php echo esc_html($stats['free_readable']); ?></strong>
					<?php _e('free', 'dashboard-available-disk-space'); ?>
				</div>
				<div class="total">
					(<?php echo esc_html($percent_label); ?>% of <?php echo esc_html($stats['total_readable']); ?> <?php _e('total', 'dashboard-available-disk-space'); ?>)
				</div>
			</div>
		<?php
	}

	/**
	 * Output a fallback when disk stats are unavailable.
	 *
	 * @return void
	 */
	private function _render_disk_stats_unavailable() {
		?>
		<div class="dads-meta-unavailable">
			<?php _e('Disk space information is unavailable.', 'dashboard-available-disk-space'); ?>
		</div>
		<?php
	}

	/**
	 * Get disk statistics and formatted values.
	 *
	 * @return array|false
	 */
	private function _get_disk_stats() {
		$dir = dirname(__FILE__);
		$disk_free_space  = @disk_free_space($dir);
		$disk_total_space = @disk_total_space($dir);

		if (false === $disk_free_space || false === $disk_total_space || $disk_total_space <= 0) {
			return false;
		}

		$disk_used_space = $disk_total_space - $disk_free_space;
		$percent_used    = ($disk_used_space * 100) / $disk_total_space;
		$percent_used    = max(0, min(100, $percent_used));

		return array(
			'used'           => $disk_used_space,
			'free'           => $disk_free_space,
			'total'          => $disk_total_space,
			'percent_used'   => $percent_used,
			'used_readable'  => size_format($disk_used_space, 2),
			'free_readable'  => size_format($disk_free_space, 2),
			'total_readable' => size_format($disk_total_space, 2),
		);
	}

	/**
	 * Pick a bar color based on usage percentage.
	 *
	 * @param float|int $percent_used Percent of disk used.
	 * @return string Hex color.
	 */
	private function _get_fill_color($percent_used)	{
		if ($percent_used > 95) {
			return '#d63638';
		} elseif ($percent_used > 90) {
			return '#f0b849';
		} else {
			return '#00a32a';
		}
	}
}

new Dashboard_Available_Disk_Space();

?>
