<?php
/**
 * Watermark Reloaded watermarking logic.
 *
 * @package   watermark_reloaded
 * @author    Sandi Verdev <wordpress@eappz.eu>
 * @copyright 2025 eAppz
 */

/**
 * Core Watermark RELOADED functionality shared between admin UI and runtime.
 */
class Watermark_Reloaded {
	/**
	 * Watermark RELOADED version
	 *
	 * @var string
	 */
	public $version = '1.4.0';

	/**
	 * Path to dir containing fonts
	 *
	 * @var string
	 */
	protected $fonts_dir = 'fonts/';

	/**
	 * Array with default options
	 *
	 * @var array
	 */
	protected $options = array(
		'watermark_installed' => 0,
		'watermark_on'        => array(),
		'watermark_position'  => 'bottom_right',
		'watermark_offset'    => array(
			'x' => 5,
			'y' => 5,
		),
		'watermark_text'      => array(
			'value' => null,
			'font'  => 'Arial.ttf',
			'size'  => 20,
			'color' => '000000',
		),
	);

	/**
	 * Get option value merged with defaults.
	 *
	 * @param string $setting Option key.
	 * @return mixed
	 */
	protected function get_option( $setting ) {
		$stored = get_option( $setting, $this->options[ $setting ] );

		if ( is_array( $this->options[ $setting ] ) ) {
			$stored = is_array( $stored ) ? $stored : array();
			return array_merge( $this->options[ $setting ], $stored );
		}

		return $stored;
	}

	/**
	 * Get all options merged with defaults.
	 *
	 * @return array
	 */
	protected function get_options() {
		$options = array();

		// Loop through default options and get user defined options.
		foreach ( $this->options as $option => $value ) {
			$options[ $option ] = $this->get_option( $option );
		}

		return $options;
	}

	/**
	 * Merge configuration array with the default one while keeping only known keys.
	 *
	 * @param array $def Defaults array.
	 * @param array $opt Overrides.
	 * @return array
	 */
	private function merge_conf_array( $def, $opt ) {
		foreach ( $def as $option => $values ) {
			if ( ! empty( $opt[ $option ] ) ) {
				$def[ $option ] = is_array( $values ) ? array_merge( $values, $opt[ $option ] ) : $opt[ $option ];
				$def[ $option ] = is_array( $values ) ? array_intersect_key( $def[ $option ], $values ) : $opt[ $option ];
			}
		}

		return $def;
	}

	/**
	 * Output a PNG preview for admin requests.
	 *
	 * @param array $opt Watermark configuration overrides.
	 * @return void
	 */
	public function create_preview( array $opt ) {
		// Merge custom settings with default settings.
		$opt = $this->merge_conf_array( $this->options, $opt );

		// Calculate required size of image.
		$bbox = $this->calculate_bbox( $opt );
		$size = array(
			'width'  => $bbox['width'] + $this->options['watermark_offset']['x'] * 2,
			'height' => $bbox['height'] + $this->options['watermark_offset']['y'] * 2,
		);

		// Create the image.
		$image = imagecreatetruecolor( $size['width'], $size['height'] );

		// Add some background to image (#CCCCCC).
		$color = imagecolorallocate( $image, 204, 204, 204 );
		imagefilledrectangle( $image, 0, 0, $size['width'], $size['height'], $color );

		// And finaly write text to image.
		$this->image_add_text( $image, $opt );

		// Set the content-type.
		header( 'Content-type: image/png' );

		// Output the image using imagepng().
		imagepng( $image );
		imagedestroy( $image );
	}

	/**
	 * Apply watermark to selected image sizes during upload.
	 *
	 * @param array $data          Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 * @return array
	 */
	public function apply_watermark( $data, $attachment_id ) {
		// Apply watermark only when attachement is image.
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return $data;
		}

		// Check if input data is provided, thus it is image and needs to be watermarked.
		if ( ! empty( $data ) ) {
			// Get settings for watermarking.
			$upload_dir   = wp_upload_dir();
			$watermark_on = array_keys( $this->get_option( 'watermark_on' ) );

			// Handle fullsize watermarking seperately.
			if ( in_array( 'fullsize', $watermark_on, true ) ) {
				$file = get_attached_file( $attachment_id );

				$this->do_watermark( $file );
			}

			// Then handle additional sizes watermarking.
			if ( ! empty( $data['sizes'] ) ) {
				foreach ( $data['sizes'] as $image_size => $image_details ) {
					// Check if user enabled watermark on this size.
					if ( in_array( $image_size, $watermark_on, true ) ) {
						$this->do_watermark(
							$upload_dir['path'] . DIRECTORY_SEPARATOR . $image_details['file']
						);
					}
				}
			}
		}

		// Pass forward attachment metadata.
		return $data;
	}

	/**
	 * Apply watermark to a specific image file.
	 *
	 * @param string $filepath Absolute path to the image file.
	 * @return boolean
	 */
	public function do_watermark( $filepath ) {
		// Get image mime type.
		$mime_type = wp_check_filetype( $filepath );
		$mime_type = isset( $mime_type['type'] ) ? $mime_type['type'] : 'application/octet-stream';

		// Get watermark settings.
		$options = $this->get_options();

		// Get image resource.
		$image = $this->get_image_resource( $filepath, $mime_type );

		// If file mime_type is not an image, then we cannot continue.
		if ( false === $image ) {
			return false;
		}

		// Add text watermark to image.
		$this->image_add_text( $image, $options );

		// Save watermarked image.
		return $this->save_image_file( $image, $mime_type, $filepath );
	}

	/**
	 * Add watermark text to an image resource.
	 *
	 * @param resource $image Image resource.
	 * @param array    $opt   Watermark options.
	 * @return resource
	 */
	private function image_add_text( $image, array $opt ) {
		// Allocate text color.
		$color = $this->image_color_allocate_hex( $image, $opt['watermark_text']['color'] );

		// Calculate watermark position and get full path to font file.
		$offset = $this->calculate_offset( $image, $opt );
		$opt    = $this->get_font_fullpath( $opt );

		// Add the text to image.
		imagettftext(
			$image,
			$opt['watermark_text']['size'],
			0,
			$offset['x'],
			$offset['y'],
			$color,
			$opt['watermark_text']['font'],
			$opt['watermark_text']['value']
		);

		return $image;
	}

	/**
	 * Allocate a color for an image from HEX code.
	 *
	 * @param resource $image Image resource.
	 * @param string   $hexstr Hex color string without leading #.
	 * @return int
	 */
	private function image_color_allocate_hex( $image, $hexstr ) {
		$int = hexdec( $hexstr );

		return imagecolorallocate(
			$image,
			0xFF & ( $int >> 0x10 ),
			0xFF & ( $int >> 0x8 ),
			0xFF & $int
		);
	}

	/**
	 * Calculate offset according to watermark alignment.
	 *
	 * @param resource $image Image resource.
	 * @param array    $opt   Watermark options.
	 * @return array
	 */
	private function calculate_offset( $image, array $opt ) {
		$offset = array(
			'x' => 0,
			'y' => 0,
		);

		// Get image size and calculate bounding box.
		$isize = $this->get_image_size( $image );
		$bbox  = $this->calculate_bbox( $opt );

		list( $ypos, $xpos ) = explode( '_', $opt['watermark_position'] );

		// Calculate offset according to equations bellow.
		switch ( $xpos ) {
			default:
			case 'left':
				$offset['x'] = 0 - $bbox['top_left']['x'] + $opt['watermark_offset']['x'];
				break;
			case 'center':
				$offset['x'] = ( $isize['x'] / 2 ) - ( $bbox['top_right']['x'] / 2 ) + $opt['watermark_offset']['x'];
				break;
			case 'right':
				$offset['x'] = $isize['x'] - $bbox['bottom_right']['x'] - $opt['watermark_offset']['x'];
				break;
		}

		switch ( $ypos ) {
			default:
			case 'top':
				$offset['y'] = 0 - $bbox['top_left']['y'] + $opt['watermark_offset']['y'];
				break;
			case 'middle':
				$offset['y'] = ( $isize['y'] / 2 ) - ( $bbox['top_right']['y'] / 2 ) + $opt['watermark_offset']['y'];
				break;
			case 'bottom':
				$offset['y'] = $isize['y'] - $bbox['bottom_right']['y'] - $opt['watermark_offset']['y'];
				break;
		}

		return $offset;
	}

	/**
	 * Get image dimensions from a resource.
	 *
	 * @param resource $image Image resource.
	 * @return array
	 */
	private function get_image_size( $image ) {
		return array(
			'x' => imagesx( $image ),
			'y' => imagesy( $image ),
		);
	}

	/**
	 * Calculate bounding box of watermark text.
	 *
	 * @param array $opt Watermark options.
	 * @return array
	 */
	private function calculate_bbox( array $opt ) {
		$opt = $this->get_font_fullpath( $opt );

		$bbox = imagettfbbox(
			$opt['watermark_text']['size'],
			0,
			$opt['watermark_text']['font'],
			$opt['watermark_text']['value']
		);

		$bbox = array(
			'bottom_left'  => array(
				'x' => $bbox[0],
				'y' => $bbox[1],
			),
			'bottom_right' => array(
				'x' => $bbox[2],
				'y' => $bbox[3],
			),
			'top_right'    => array(
				'x' => $bbox[4],
				'y' => $bbox[5],
			),
			'top_left'     => array(
				'x' => $bbox[6],
				'y' => $bbox[7],
			),
		);

		// Calculate width & height of text.
		$bbox['width']  = $bbox['top_right']['x'] - $bbox['top_left']['x'];
		$bbox['height'] = $bbox['bottom_left']['y'] - $bbox['top_left']['y'];

		return $bbox;
	}

	/**
	 * Get full path of the selected font file.
	 *
	 * @param array $opt Watermark options.
	 * @return array
	 */
	private function get_font_fullpath( array $opt ) {
		$opt['watermark_text']['font'] = plugin_dir_path( __FILE__ ) . $this->fonts_dir . $opt['watermark_text']['font'];

		return $opt;
	}

	/**
	 * Get image resource according to MIME type.
	 *
	 * @param string $filepath  Absolute path to image.
	 * @param string $mime_type MIME type.
	 * @return resource
	 */
	private function get_image_resource( $filepath, $mime_type ) {
		switch ( $mime_type ) {
			case 'image/jpeg':
				return imagecreatefromjpeg( $filepath );
			case 'image/png':
				return imagecreatefrompng( $filepath );
			case 'image/gif':
				return imagecreatefromgif( $filepath );
			default:
				return false;
		}
	}

	/**
	 * Save image from image resource.
	 *
	 * @param resource $image     Image resource.
	 * @param string   $mime_type MIME type.
	 * @param string   $filepath  Destination path.
	 * @return boolean
	 */
	private function save_image_file( $image, $mime_type, $filepath ) {
		switch ( $mime_type ) {
			case 'image/jpeg':
				// Use the same JPEG quality as WordPress core.
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				$quality = apply_filters( 'jpeg_quality', 90 );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				$quality = apply_filters( 'wr_jpeg_quality', $quality );
				return imagejpeg( $image, $filepath, $quality );
			case 'image/png':
				return imagepng( $image, $filepath );
			case 'image/gif':
				return imagegif( $image, $filepath );
			default:
				return false;
		}
	}
}
