<?php
/**
 * Dashboard: Available Disk Space logic.
 *
 * @package   dashboard_available_disk_space
 * @author    Sandi Verdev <wordpress@eappz.eu>
 * @copyright 2025 eAppz
 */

/**
 * Core Dashboard: Available Disk Space functionality shared between admin UI and runtime.
 */
class Dashboard_Available_Disk_Space {
	/**
	 * Dashboard: Available Disk Space version
	 *
	 * @var string
	 */
	public $version = '1.1.1';

	/**
	 * Class constructor
	 */
	public function __construct() {
		// Register installer function.
		register_activation_hook( __FILE__, array( $this, 'activate_dads' ) );

		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_links' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'rightnow_end', array( $this, 'render_glance_line' ), 20 );
	}

	/**
	 * Plugin installation method
	 */
	public function activate_dads() {
		// Record install time.
		add_option( 'DADS_installed', time() );
	}

	/**
	 * Add extra plugin links to the plugins list.
	 *
	 * @param array  $links Existing plugin row links.
	 * @param string $file  Plugin basename from the plugins table.
	 * @return array Filtered plugin row links.
	 */
	public function add_plugin_links( $links, $file ) {
		if ( plugin_basename( DADS_LOADER ) === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'https://coindrop.to/sverde1' ),
				esc_html__( 'Donate', 'dashboard-available-disk-space' )
			);
		}

		return $links;
	}

	/**
	 * Enqueue lightweight styles for the dashboard widget.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_styles( $hook ) {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'dashboard-available-disk-space',
			plugins_url( 'style.css', __FILE__ ),
			array( 'dashboard' ),
			$this->version
		);
	}

	/**
	 * Render a compact storage line in the "At a Glance" widget.
	 *
	 * @return void
	 */
	public function render_glance_line() {
		if ( ! current_user_can( 'upload_files' ) ) {
			return;
		}

		?>
		<div class="dads-glance-storage">
			<span class="dads-meta">
				<span class="dashicons dashicons-database" aria-hidden="true"></span>
				<strong><?php esc_html_e( 'Available Storage Space', 'dashboard-available-disk-space' ); ?></strong>
				<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
					<?php esc_html_e( 'Manage Uploads', 'dashboard-available-disk-space' ); ?>
				</a>
			</span>
		<?php

		$stats = $this->get_disk_stats();

		false !== $stats
			? $this->render_disk_stats( $stats )
			: $this->render_disk_stats_unavailable();

		?>
		</div>
		<?php
	}

	/**
	 * Output the storage bar and stats.
	 *
	 * @param array $stats Disk metrics from get_disk_stats().
	 * @return void
	 */
	private function render_disk_stats( $stats ) {
		$percent_label     = number_format_i18n( $stats['percent_used'], 1 );
		$percent_for_width = number_format( max( 0, min( 100, $stats['percent_used'] ) ), 2, '.', '' );
		$fill_color        = $this->get_fill_color( $stats['percent_used'] );

		?>
			<div class="dads-bar">
				<div class="dads-bar__fill" style="background: <?php echo esc_attr( $fill_color ); ?>; width: <?php echo esc_attr( $percent_for_width ); ?>%;"></div>
			</div>
			<div class="dads-meta">
				<div title="<?php esc_attr_e( '(used / free) total space available', 'dashboard-available-disk-space' ); ?>">
					<strong><?php echo esc_html( $stats['used_readable'] ); ?></strong>
					<?php esc_html_e( 'used', 'dashboard-available-disk-space' ); ?> /
					<strong><?php echo esc_html( $stats['free_readable'] ); ?></strong>
					<?php esc_html_e( 'free', 'dashboard-available-disk-space' ); ?>
				</div>
				<div class="total">
					(<?php echo esc_html( $percent_label ); ?>% of <?php echo esc_html( $stats['total_readable'] ); ?> <?php esc_html_e( 'total', 'dashboard-available-disk-space' ); ?>)
				</div>
			</div>
		<?php
	}

	/**
	 * Output a fallback when disk stats are unavailable.
	 *
	 * @return void
	 */
	private function render_disk_stats_unavailable() {
		?>
		<div class="dads-meta-unavailable">
			<?php esc_html_e( 'Disk space information is unavailable.', 'dashboard-available-disk-space' ); ?>
		</div>
		<?php
	}

	/**
	 * Get disk statistics and formatted values.
	 *
	 * @return array|false
	 */
	private function get_disk_stats() {
		$dir = __DIR__;

		// Check that the dir we are checking is available.
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		$disk_free_space  = disk_free_space( $dir );
		$disk_total_space = disk_total_space( $dir );

		// Check if disk info is available.
		if ( false === $disk_free_space || false === $disk_total_space || $disk_total_space <= 0 ) {
			return false;
		}

		$disk_used_space = $disk_total_space - $disk_free_space;
		$percent_used    = ( $disk_used_space * 100 ) / $disk_total_space;
		$percent_used    = max( 0, min( 100, $percent_used ) );

		return array(
			'used'           => $disk_used_space,
			'free'           => $disk_free_space,
			'total'          => $disk_total_space,
			'percent_used'   => $percent_used,
			'used_readable'  => size_format( $disk_used_space, 2 ),
			'free_readable'  => size_format( $disk_free_space, 2 ),
			'total_readable' => size_format( $disk_total_space, 2 ),
		);
	}

	/**
	 * Pick a bar color based on usage percentage.
	 *
	 * @param float|int $percent_used Percent of disk used.
	 * @return string Hex color.
	 */
	private function get_fill_color( $percent_used ) {
		if ( $percent_used > 95 ) {
			return '#d63638';
		} elseif ( $percent_used > 90 ) {
			return '#f0b849';
		} else {
			return '#00a32a';
		}
	}
}
