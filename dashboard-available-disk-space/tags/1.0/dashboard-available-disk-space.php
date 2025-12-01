<?php
/*
Plugin Name: Dashboard: Available Disk Space
Plugin URI: http://randomplac.es/wordpress-plugins/dashboard-available-disk-space/
Description: Display available server disk space on the Dashboard
Version: 1.0
Author: Sandi Verdev
Author URI: http://randomplac.es/
*/

class Dashboard_Available_Disk_Space {
	function Dashboard_Available_Disk_Space() {
		add_action('activity_box_end', array(&$this, 'dashboard_indicator'));
		add_filter('plugin_row_meta',  array(&$this, 'add_plugin_links'), 10, 2);
	}

	function dashboard_indicator() {	
		$disk_free_space  = disk_free_space('/');
		$disk_total_space = disk_total_space('/');
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
		<p class="sub musub"><?php _e("Storage Space <a href='upload.php' title='Manage Uploads...'>&raquo;</a>"); ?></p>
		<div class="table">
			<table>
				<tr class="first">
					<td style="width: 100%;">
						<div style="border: 1px solid #000000; height: 10px; margin-right: 5px;">
							<div style="background: <?php echo $color; ?>; width: <?php echo $percentused; ?>%; height: 100%;"></div>
						</div>
					</td>
					<td>(<?php echo $this->disk_units($disk_used_space); ?> / <?php echo $this->disk_units($disk_free_space); ?>) <?php echo $this->disk_units($disk_total_space); ?></td>
				</tr>
			</table>
			<!--
			<table>
				<tr class="first">
					<td class="first b b-posts"><?php printf( __( '<a href="upload.php" title="Manage Uploads..." class="musublink">%sMB</a>' ), $quota ); ?></td>
					<td class="t posts"><?php _e('Space Allowed'); ?></td>
					<td class="b b-comments"><?php printf( __( '<a href="upload.php" title="Manage Uploads..." class="musublink">%1sMB (%2s%%)</a>' ), $used, $percentused ); ?>
					<td class="last t comments <?php echo $used_color;?>"><?php _e('Space Used');?></td>
				</tr>
			</table>
			-->
		</div>
		<?php
	}
	
	function disk_units($size) {
		// byte, kilobyte, megabyte, gigabyte, terabyte, petabyte, exabyte, zettabyte, yottabyte
		$format = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
		for($i = 0; ($size / 1024) > 1 && $i < 8; $i++) {
			$size = $size / 1024;
		}
	
		return number_format($size,2,'.','')." " . $format[$i];
	}
	
	function add_plugin_links($links, $file) {
		if($file == plugin_basename(__FILE__)) {
			$links[] = '<a href="http://randomplac.es/wordpress-plugins/donate/">Donate</a>';
		}
		
		return $links;
	}
}

new Dashboard_Available_Disk_Space();

?>