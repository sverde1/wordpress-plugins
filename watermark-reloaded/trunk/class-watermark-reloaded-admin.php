<?php
/**
 * Watermark Reloaded admin UI and settings.
 *
 * @package   watermark_reloaded
 * @author    Sandi Verdev <wordpress@eappz.eu>
 * @copyright 2025 eAppz
 */

if ( ! defined( 'WR_SETTINGS_PAGE' ) ) {
	define( 'WR_SETTINGS_PAGE', 'watermark-reloaded' );
}

/**
 * Admin UI and settings handler for Watermark RELOADED.
 */
class Watermark_Reloaded_Admin extends Watermark_Reloaded {
	/**
	 * Error messages to diplay
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * Available fonts for watermark
	 *
	 * @var array
	 */
	private $fonts = array();

	/**
	 * List of missing dependencies
	 *
	 * @var array
	 */
	private $missing_dependencies = array();

	/**
	 * List of available watermark positions
	 *
	 * @var array
	 */
	private $watermark_positions = array(
		'x' => array( 'left', 'center', 'right' ),
		'y' => array( 'top', 'middle', 'bottom' ),
	);

	/**
	 * Initialize hooks for admin screens and watermark processing.
	 *
	 * @return void
	 */
	public function __construct() {
		// Register installer function.
		register_activation_hook( WR_LOADER, array( $this, 'activate_watermark' ) );

		// Add plugin "Settings" action on plugin list.
		add_action( 'plugin_action_links_' . plugin_basename( WR_LOADER ), array( $this, 'add_plugin_actions' ) );

		// Add links for plugin help, donations,...
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_links' ), 10, 2 );

		// Push options page link, when generating admin menu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Dashboard widget with some statistics.
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widget_setup' ) );

		// Add action for WP ajax: admin-ajax.php?action=watermark_preview .
		add_action( 'wp_ajax_watermark_preview', array( $this, 'watermark_preview' ) );

		// Add filter for watermarking images.
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'apply_watermark' ), 10, 2 );
	}

	/**
	 * Plugin installation method that seeds default options.
	 *
	 * @return void
	 */
	public function activate_watermark() {
		// Record install time.
		add_option( 'watermark_installed', time() );

		// Loop through default options and add them into DB.
		foreach ( $this->options as $option => $value ) {
			add_option( $option, $value );
		}
	}

	/**
	 * Add "Settings" link to plugin action links.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public function add_plugin_actions( $links ) {
		array_unshift( $links, '<a href="' . esc_url( menu_page_url( WR_SETTINGS_PAGE, false ) ) . '">' . esc_html__( 'Settings', 'watermark-reloaded' ) . '</a>' );

		return $links;
	}

	/**
	 * Add meta links (e.g., donate) to the plugin row.
	 *
	 * @param array  $links Existing meta links.
	 * @param string $file  Plugin file basename.
	 * @return array
	 */
	public function add_plugin_links( $links, $file ) {
		if ( plugin_basename( WR_LOADER ) === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'https://coindrop.to/sverde1' ),
				esc_html__( 'Donate', 'watermark-reloaded' )
			);
		}

		return $links;
	}

	/**
	 * Add menu entry for Watermark RELOADED settings and attach style/script includes.
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Add option in admin menu, for setting details on watermarking.
		$plugin_page = add_options_page(
			esc_html__( 'Watermark Reloaded Settings', 'watermark-reloaded' ),
			esc_html__( 'Watermark Reloaded', 'watermark-reloaded' ),
			'manage_options',
			WR_SETTINGS_PAGE,
			array( $this, 'options_page' )
		);

		add_action( 'admin_print_styles-' . $plugin_page, array( $this, 'install_styles' ) );
		add_action( 'admin_print_scripts-' . $plugin_page, array( $this, 'install_scripts' ) );
		// Also add JS to media upload popup.
		add_action( 'admin_print_scripts-media-upload-popup', array( $this, 'install_scripts' ) );
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @return void
	 */
	public function dashboard_widget_setup() {
		// Add widget to dashboard.
		wp_add_dashboard_widget(
			'watermark_dashboard_widget',
			esc_html__( 'Watermark RELOADED', 'watermark-reloaded' ),
			array( $this, 'dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget with current settings and preview link.
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		?>
		<?php $watermark_on = $this->get_option( 'watermark_on' ); ?>
		<?php if ( ! empty( $watermark_on ) ) : ?>

			<table width="100%">
				<tr>
					<td colspan="2">
						<strong><?php esc_html_e( 'Settings', 'watermark-reloaded' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td width="90"><?php esc_html_e( 'Enabled for', 'watermark-reloaded' ); ?>:</td>
					<td width="100">
						<strong>
							<?php echo esc_html( implode( ', ', array_keys( $watermark_on ) ) ); ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Positioned at', 'watermark-reloaded' ); ?>:</td>
					<td>
						<strong>
							<?php echo esc_html( str_replace( '_', ' ', $this->get_option( 'watermark_position' ) ) ); ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Offset', 'watermark-reloaded' ); ?>:</td>
					<td>
						<strong>
							<?php echo esc_html( implode( ' x ', $this->get_option( 'watermark_offset' ) ) ); ?> px
						</strong>
					</td>
				</tr>
				<?php // Only admin user can change settings. ?>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<tr>
					<td>&nbsp;</td>
					<td><a href="<?php echo esc_url( menu_page_url( WR_SETTINGS_PAGE, false ) ); ?>"><?php esc_html_e( 'Change settings', 'watermark-reloaded' ); ?></a></td>
				</tr>
				<?php endif; ?>
				<tr>
					<td colspan="2">
						<strong><?php esc_html_e( 'Preview', 'watermark-reloaded' ); ?></strong>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top" colspan="2">
						<?php $watermark_text = $this->get_option( 'watermark_text' ); ?>
							<?php
							$params = array(
								'watermark_text' => array(
									'value' => $watermark_text['value'],
									'font'  => $watermark_text['font'],
									'size'  => $watermark_text['size'],
									'color' => $watermark_text['color'],
								),
							);

							$preview_url = $this->preview_url( $params );
							?>
						<img src="<?php echo esc_url( $preview_url ); ?>" alt="<?php esc_html_e( 'Watermark preview', 'watermark-reloaded' ); ?>" />
					</td>
				</tr>
			</table>

		<?php else : ?>
			<p>
				<?php
					/* translators: %s: link to the Watermark Reloaded settings page. */
					$message = __(
						'Watermark RELOADED is <strong>not enabled</strong>. Go to <a href="%s">settings</a> and enable it now!',
						'watermark-reloaded'
					);
					$message = sprintf( $message, esc_url( menu_page_url( WR_SETTINGS_PAGE, false ) ) );

					echo wp_kses(
						$message,
						array(
							'strong' => array(),
							'a'      => array(
								'href' => array(),
							),
						)
					);
				?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * AJAX callback for generating a live text watermark preview.
	 *
	 * @return void
	 */
	public function watermark_preview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You need a higher level of permission.', 'watermark-reloaded' ) );
		}

		check_admin_referer( 'wr_preview', 'wr_preview_nonce' );

		$watermark_text = filter_input( INPUT_GET, 'watermark_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY );
		if ( ! empty( $watermark_text ) && is_array( $watermark_text ) ) {
			$watermark_text = $this->filter_option_value( 'watermark_text', $watermark_text );
		}

		$this->create_preview( array( 'watermark_text' => $watermark_text ) );
	}

	/**
	 * Include styles used by Watermark RELOADED.
	 *
	 * @return void
	 */
	public function install_styles() {
		// WP built-in color picker CSS.
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style( 'watermark-reloaded', plugins_url( 'style.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Include scripts used by Watermark RELOADED.
	 *
	 * @return void
	 */
	public function install_scripts() {
		wp_enqueue_script(
			'watermark-reloaded',
			plugins_url( 'script.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			$this->version,
			array( 'in_footer' => true )
		);
	}

	/**
	 * Populate the fonts list and collect unreadable font notices.
	 *
	 * @return void
	 */
	private function get_fonts() {
		$all_readable = true;
		$fonts_dir    = plugin_dir_path( __FILE__ ) . $this->fonts_dir;

		try {
			$dir = new DirectoryIterator( $fonts_dir );

			foreach ( $dir as $file ) {
				if ( $file->isFile() ) {
					$font = pathinfo( $file->getFilename() );

					if ( strtolower( $font['extension'] ) === 'ttf' ) {
						if ( ! $file->isReadable() ) {
							$all_readable = false;
						}

						$this->fonts[ $font['basename'] ] = str_replace( '_', ' ', $font['filename'] );
					}
				}
			}

			ksort( $this->fonts );
		} catch ( Exception $e ) {
			$this->messages['error'][] = esc_html( $e->getMessage() );

			return;
		}

		// Check if there are any unreadable fonts and notify user.
		if ( ! $all_readable ) {
			/* translators: %s: path to the fonts directory. */
			$message = __(
				'Some fonts might be unreadable, try chmoding contents of the folder <strong>%s</strong> to writable and refresh this page.',
				'watermark-reloaded'
			);
			$message = sprintf( $message, $fonts_dir );

			$this->messages['notice'][] = wp_kses(
				$message,
				array(
					'strong' => array(),
				)
			);
		}
	}

	/**
	 * Display options page container and error messages.
	 *
	 * @return void
	 */
	public function options_page() {
		// Check for functional dependencies.
		$this->dependency_checks();
		$this->options_page_submit();

		$this->get_fonts();
		?>

		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php esc_html_e( 'Watermark Reloaded Settings', 'watermark-reloaded' ); ?></h2>

			<?php foreach ( $this->messages as $namespace => $messages ) : ?>
				<?php foreach ( $messages as $message ) : ?>
					<div class="<?php echo esc_attr( $namespace ); ?>">
						<p>
							<strong><?php echo esc_html( $message ); ?></strong>
						</p>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>


			<?php
			if ( empty( $this->missing_dependencies ) ) {
				$this->options_page_content();
			}
			?>
		</div>

		<?php
	}

	/**
	 * Handle settings form submission.
	 *
	 * @return void
	 */
	private function options_page_submit() {
		// Require general capability for any settings handling.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You need a higher level of permission.', 'watermark-reloaded' ) );
		}

		// Collect and sanitize POST payload.
		$post = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		// Check if our form submit button was clicked.
		$is_save   = isset( $post['submit'] );
		$is_delete = isset( $post['delete_data'] );

		if ( ! $is_save && ! $is_delete ) {
			return;
		}

		check_admin_referer( 'wr_options_save', 'wr_options_nonce' );

		if ( $is_delete ) {
			// Harden destructive action behind delete_plugins.
			if ( ! current_user_can( 'delete_plugins' ) ) {
				$this->messages['error'][] = esc_html__( 'You need a higher level of permission to delete plugin data.', 'watermark-reloaded' );
				return;
			}

			foreach ( array_keys( $this->options ) as $option ) {
				delete_option( $option );
			}

			$this->messages['updated'][] = esc_html__( 'All plugin data has been deleted.', 'watermark-reloaded' );
			return;
		}

		// In case there is no watermark_on set, we turn off watermarking on all image sizes.
		if ( empty( $post['watermark_on'] ) ) {
			update_option( 'watermark_on', array() );
		}

		// Process user input, filter it and update options.
		foreach ( $this->options as $option => $value ) {
			if ( isset( $post[ $option ] ) ) {
				update_option( $option, $this->filter_option_value( $option, $post[ $option ] ) );
			}
		}

		$this->messages['updated'][] = esc_html__( 'Settings saved.', 'watermark-reloaded' );
	}

	/**
	 * Render and process the settings page contents.
	 *
	 * @return void
	 */
	private function options_page_content() {
		?>

		<form id="wr_form" method="post" action="" data-preview_base="<?php echo esc_url( $this->preview_url() ); ?>">
			<?php wp_nonce_field( 'wr_options_save', 'wr_options_nonce' ); ?>
			<table class="form-table">

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Enable watermark for', 'watermark-reloaded' ); ?></th>
					<td>
						<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Enable watermark for', 'watermark-reloaded' ); ?></span></legend>

						<?php $watermark_on = array_keys( $this->get_option( 'watermark_on' ) ); ?>
							<?php foreach ( $this->image_size_list() as $image_size => $size_name ) : ?>
								<?php
									$name    = 'watermark_on[' . $image_size . ']';
									$checked = in_array( $image_size, $watermark_on, true );
								?>
								<label>
									<input name="<?php echo esc_attr( $name ); ?>" type="checkbox" value="1"<?php checked( $checked ); ?> />
									<?php echo esc_html( $size_name ); ?>
								</label>
								<br />
							<?php endforeach; ?>

							<span class="description"><?php esc_html_e( 'Check image sizes on which watermark should appear.', 'watermark-reloaded' ); ?></span>
						</fieldset>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Watermark alignment', 'watermark-reloaded' ); ?></th>
					<td>
						<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Watermark alignment', 'watermark-reloaded' ); ?></span></legend>

							<table class="watermark_position" border="1">

								<?php $watermark_position = $this->get_option( 'watermark_position' ); ?>
								<?php foreach ( $this->watermark_positions['y'] as $y ) : ?>
								<tr>
									<?php foreach ( $this->watermark_positions['x'] as $x ) : ?>
										<?php
											$value = $y . '_' . $x;
										?>
										<td title="<?php echo esc_attr( ucfirst( $y . ' ' . $x ) ); ?>">
											<input name="watermark_position" type="radio" value="<?php echo esc_attr( $value ); ?>"<?php checked( $watermark_position, $value ); ?> />
										</td>
									<?php endforeach; ?>
								</tr>
								<?php endforeach; ?>
							</table>

							<span class="description"><?php esc_html_e( 'Chose where on image watermark should be positioned.', 'watermark-reloaded' ); ?></span>

						</fieldset>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Watermark offset', 'watermark-reloaded' ); ?></th>
					<td>
						<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Watermark offset', 'watermark-reloaded' ); ?></span></legend>

							<?php $watermark_offset = $this->get_option( 'watermark_offset' ); ?>

							<?php foreach ( array( 'x', 'y' ) as $direction ) : ?>
								<?php
									$name  = 'watermark_offset[' . $direction . ']';
									$value = $watermark_offset[ $direction ];
								?>
								<div>
									<?php echo esc_html( $direction ); ?>:
									<input class="wr_right" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" size="2" />px
								</div>
							<?php endforeach; ?>

						</fieldset>
					</td>
				</tr>

			</table>

			<a name="watermark_text"></a>
			<div class="watermark_type watermark_text">
				<h3><?php esc_html_e( 'Text watermark', 'watermark-reloaded' ); ?></h3>
				<p><?php esc_html_e( 'If you chose watermark should be text define how it should look like.', 'watermark-reloaded' ); ?></p>

				<table class="form-table">
					<?php $watermark_text = $this->get_option( 'watermark_text' ); ?>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Text color', 'watermark-reloaded' ); ?></th>
						<td class="wr_width">
							<fieldset class="wr_width">
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Text color', 'watermark-reloaded' ); ?></span></legend>

							<input name="watermark_text[color]" type="text" maxlength="6" size="6" value="<?php echo esc_attr( '#' . $watermark_text['color'] ); ?>" />

							</fieldset>
						</td>
						<th scope="row" class="nowidth"><?php esc_html_e( 'Preview', 'watermark-reloaded' ); ?></th>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Text size', 'watermark-reloaded' ); ?></th>
						<td class="wr_width">
							<fieldset class="wr_width">
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Text size', 'watermark-reloaded' ); ?></span></legend>

							<input class="wr_right" name="watermark_text[size]" type="text" value="<?php echo esc_attr( $watermark_text['size'] ); ?>" size="3" />
							<?php
							// http://php.net/imagettftext - $size attribute uses "px" in GD v1 and "pt" in GD v2.
							echo esc_html( version_compare( GD_VERSION, 2, '>=' ) ? 'pt' : 'px' );
							?>

							</fieldset>
						</td>
						<td rowspan="3">
							<img class="wr_preview_image" src="" alt="<?php esc_html_e( 'Watermark RELOADED', 'watermark-reloaded' ); ?>" />
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Font', 'watermark-reloaded' ); ?></th>
						<td class="wr_width">
							<fieldset class="wr_width">
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Font', 'watermark-reloaded' ); ?></span></legend>

								<select class="wr_width" name="watermark_text[font]">
									<?php foreach ( $this->fonts as $key => $value ) : ?>
										<?php
											$style = 'font-family: ' . $value . ';';
										?>

									<option value="<?php echo esc_attr( $key ); ?>" style="<?php echo esc_attr( $style ); ?>"<?php selected( $watermark_text['font'], $key ); ?>>
										<?php echo esc_html( $value ); ?>
									</option>
									<?php endforeach; ?>
								</select>

							</fieldset>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Watermark text', 'watermark-reloaded' ); ?></th>
						<td class="wr_width">
							<fieldset class="wr_width">
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Watermark text', 'watermark-reloaded' ); ?></span></legend>

								<input name="watermark_text[value]" type="text" value="<?php echo esc_attr( $watermark_text['value'] ); ?>" />

							</fieldset>
						</td>
						
					</tr>
				</table>
			</div>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'watermark-reloaded' ); ?>" />
			</p>

			<?php if ( current_user_can( 'delete_plugins' ) ) : ?>
				<div class="wr_delete_data">
					<h2><?php esc_html_e( 'Delete plugin data', 'watermark-reloaded' ); ?></h2>
					<p><?php esc_html_e( 'Remove all Watermark RELOADED settings from this site. This cannot be undone.', 'watermark-reloaded' ); ?></p>
					<p class="submit">
						<button type="submit" name="delete_data" class="button button-secondary">
							<?php esc_html_e( 'Delete all data', 'watermark-reloaded' ); ?>
						</button>
					</p>
				</div>
			<?php endif; ?>

		</form>

		<?php
	}

	/**
	 * Build a nonce-protected preview URL for the AJAX handler.
	 *
	 * @param array $params Query parameters to append to the preview request.
	 * @return string
	 */
	private function preview_url( array $params = array() ) {
		$merge_params = array( 'action' => 'watermark_preview' );
		$url          = admin_url( 'admin-ajax.php' ) . '?' . http_build_query(
			array_merge_recursive( $merge_params, $params )
		);

		return wp_nonce_url( $url, 'wr_preview', 'wr_preview_nonce' );
	}

	/**
	 * Sanitize and normalize option values.
	 *
	 * @param string $option Option key.
	 * @param mixed  $value  Raw value.
	 * @return mixed
	 */
	private function filter_option_value( $option, $value ) {
		switch ( $option ) {
			case 'watermark_on':
				if ( ! is_array( $value ) ) {
					return $this->get_option( $option );
				}

				// Filter out any intruding image sizes and make sure value is 1.
				$filtered = array_intersect_key( $value, $this->image_size_list() );
				return array_fill_keys( array_keys( $filtered ), 1 );

			case 'watermark_position':
				$positions = explode( '_', $value );

				return count( $positions ) === 2 &&
					in_array( $positions[1], $this->watermark_positions['x'], true ) &&
					in_array( $positions[0], $this->watermark_positions['y'], true )
						? implode( '_', $positions )
						: $this->get_option( $option );

			case 'watermark_offset':
				if ( ! is_array( $value ) ) {
					return $this->get_option( $option );
				}

				// Allow only x and y axis.
				$filtered = array_intersect_key( $value, array_flip( array_keys( $this->watermark_positions ) ) );

				return array_map( 'intval', $filtered );

			case 'watermark_text':
				$defaults = $this->get_option( $option );

				return array(
					'value' => isset( $value['value'] ) ? sanitize_text_field( $value['value'] ) : $defaults['value'],
					'font'  => isset( $value['font'] ) ? sanitize_file_name( $value['font'] ) : $defaults['font'],
					'size'  => isset( $value['size'] ) ? intval( $value['size'] ) : $defaults['size'],
					'color' => isset( $value['color'] ) && preg_match( '/^\#[a-f0-9]{6}$/si', $value['color'] )
						? str_replace( '#', '', $value['color'] )
						: $defaults['color'],
				);

			default:
				return null;
		}
	}

	/**
	 * Validate required PHP extensions and register error messages.
	 *
	 * @return bool
	 */
	private function dependency_checks() {
		if ( ! $this->gd_exists() ) {
			$this->missing_dependencies[] = 'gd';
			$this->messages['error'][]    = esc_html__(
				'Watermark RELOADED requires the PHP extension GD to process images. Please enable GD to configure the plugin.',
				'watermark-reloaded'
			);

			return false;
		}

		$gd_info = gd_info();
		if ( ! $gd_info['FreeType Support'] ) {
			$this->missing_dependencies[] = 'freetype';
			$this->messages['error'][]    = esc_html__( 'Text watermarking requires FreeType Library.', 'watermark-reloaded' );

			return false;
		}

		return true;
	}

	/**
	 * Check if GD extension is available.
	 *
	 * @return bool
	 */
	private function gd_exists() {
		return extension_loaded( 'gd' ) || function_exists( 'gd_info' );
	}

	/**
	 * Get a formatted list of registered image sizes including fullsize.
	 *
	 * @return array
	 */
	private function image_size_list() {
		$fullsize    = array( 'fullsize' => '' );
		$image_sizes = array_merge( $fullsize, array_flip( get_intermediate_image_sizes() ) );

		foreach ( $image_sizes as $image_size => &$size_name ) {
			$size_name = str_replace( array( '_', '-' ), ' ', $image_size );
			$size_name = ucfirst( $size_name );
		}

		return $image_sizes;
	}
}
