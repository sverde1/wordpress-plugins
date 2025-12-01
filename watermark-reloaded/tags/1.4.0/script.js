/**
 * Admin UI behaviors for Watermark Reloaded plugin settings.
 * Handles live preview updates and color picker interactions.
 *
 * @package   watermark_reloaded
 * @author    Sandi Verdev <sandi.verdev@eappz.eu>
 * @copyright 2025 eAppz
 */

(function ( $ ) {
	'use strict';

	$(
		function () {
			var form = $( '#wr_form' );
			// Preview update trigger.
			var updatePreview = function () {
				form.find( '.wr_preview_image' ).show();

				var previewBase = form.data( 'preview_base' );
				form.find( '.wr_preview_image' ).attr(
					'src',
					previewBase + '&' + form.find( '.watermark_text input, .watermark_text select' ).serialize()
				);
			};

			form.find( '.watermark_text input:text' ).on( 'keyup change', updatePreview );
			form.find( '.watermark_text select' ).on( 'change', updatePreview );

			// Color selector logic.
			$( 'input[name="watermark_text[color]"]' ).wpColorPicker(
				{
					change: function ( event, ui ) {
						updatePreview();
					},
					clear: function () {
						updatePreview();
					}
				}
			);

			updatePreview();
		}
	);

})( jQuery );
