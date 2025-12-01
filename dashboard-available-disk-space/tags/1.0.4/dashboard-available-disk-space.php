<?php
/*
Plugin Name: Dashboard: Available Disk Space
Plugin URI: http://eappz.eu/en/products/dashboard-available-disk-space/
Description: Display available server disk space on the Dashboard
Version: 1.0.4
Author: Sandi Verdev
Author URI: http://eAppz.eu/
*/

class Dashboard_Available_Disk_Space {
	/**
	 * Class constructor
	 */
	function Dashboard_Available_Disk_Space() {
		// register installer function
		register_activation_hook(__FILE__, array(&$this, 'activateDADS'));

		add_action('activity_box_end', array(&$this, 'dashboard_indicator'));
		add_filter('plugin_row_meta',  array(&$this, 'add_plugin_links'), 10, 2);
	}

	/**
	 * Plugin installation method
	 */
	function activateDADS() {
		// record install time
		add_option('DADS_installed', time(), null, 'no');
	}

	/**
	 * Display Available Disk Space indicator on Dashboard
	 */
	function dashboard_indicator() {
		$dir = dirname(__FILE__);
		$disk_free_space  = disk_free_space($dir);
		$disk_total_space = disk_total_space($dir);
		$disk_used_space  = $disk_total_space - $disk_free_space;



		$percentused = $disk_used_space * 100 / $disk_total_space;
		$percentused = number_format($percentused);

		if ($percentused > 95) {
			$color = '#FF0000';
		} elseif ($percentused > 90) {
			$color = '#FFFF00';
		} else {
			$color = '#00FF00';
		}
		?>
		<div class="table table_disk_space">
			<p class="sub" style="top: 2px; position: relative;"><?php _e('Available Storage Space <a href="upload.php" title="Manage Uploads...">&raquo;</a>'); ?></p>
			<div class="table">
				<table>
					<tr class="first">
						<td style="width: 100%;">
							<div style="border: 1px solid #000000; height: 10px; margin-right: 5px;">
								<div style="background: <?php echo $color; ?>; width: <?php echo $percentused; ?>%; height: 100%;"></div>
							</div>
						</td>
						<td title="(used / free) total space available">(<?php echo $this->disk_units($disk_used_space); ?> / <?php echo $this->disk_units($disk_free_space); ?>) <?php echo $this->disk_units($disk_total_space); ?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Convert to bigest unit possible and add unit
	 *
	 * @param integer $size
	 * @return string
	 */
	function disk_units($size) {
		// byte, kilobyte, megabyte, gigabyte, terabyte, petabyte, exabyte, zettabyte, yottabyte
		$format = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		for($i = 0; ($size / 1024) > 1 && $i < 8; $i++) {
			$size = $size / 1024;
		}

		return number_format($size, 2, '.', '') . " " . $format[$i];
	}

	/**
	 * Add links on installed plugin list
	 */
	function add_plugin_links($links, $file) {
		if($file == plugin_basename(__FILE__)) {
			$links[] = '<a href="http://eappz.eu/en/donate/">Donate</a>';
		}

		return $links;
	}
}

new Dashboard_Available_Disk_Space();

?>