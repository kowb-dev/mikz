/**
 * Live preview for footer customizer settings.
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

( function( $ ) {

	// Update the footer newsletter shortcode in real-time.
	wp.customize( 'mkx_footer_newsletter_shortcode', function( value ) {
		value.bind( function( to ) {
			// Note: Shortcode rendering requires a refresh. This is a placeholder.
			// A full live preview of a shortcode is complex and often requires a full page refresh.
			// This will just show the shortcode text for now.
			var formContainer = $( '.mkx-newsletter-form' );
			if ( to ) {
				formContainer.text( to ); // Display the shortcode text for reference.
			} else {
				formContainer.empty();
			}
		} );
	} );

} )( jQuery );
