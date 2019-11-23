<?php
/**
 * Compatibility for plugins and themes that strip javascript from lazily loaded content (infinite scroll).
 */
class Advanced_Ads_Slider_Compatibility {

	/**
	 * AJAX actions registered by plugins and themes.
	 *
	 * @var array
	 */
	private static $inf_scroll_items = array(
		'thb_infinite_ajax', // "GoodLife" theme.
	);

	public function __construct() {
		add_action( 'wp_footer', array( $this, 'add_footer_js' ) );
	}

	/**
	 * Add js to the footer if corresponding plugins and themes are active.
	 */
	public function add_footer_js() {
		foreach ( self::$inf_scroll_items as $ajax_action ) {
			$method = 'footer_js_' . $ajax_action;
			if ( method_exists( $this, $method ) ) {
				call_user_func( array( $this, $method ) );
			}
		}
	}


	/**
	 * GoodLife theme: initialize slider.
	 */
	public function footer_js_thb_infinite_ajax() {
		// Check if the theme is active.
		if ( ! function_exists( 'thb_infinite_ajax' ) ) {
			return;
		}
		?>
		<script>
		(function() {
			if ( ! window.jQuery ) {
				console.log( 'jQuery not found' );
				return;
			}

			// Wait until the infinite scroll feature injects new content.
			jQuery( document.body ).on( 'thb_after_infinite_load', function() {
				jQuery( '.custom-slider' ).each( function() {
					var $el = jQuery( this );
					var options = $el.data( 'options' );
					// Prevent double initialization.
					$el.data( 'options', false );

					if ( options ) {
						<?php echo Advanced_Ads_Slider::get_init_script() ; ?>
						init_slider( options );
					}
				} );
			} );
		})();
		</script>
		<?php
	}

	/**
	 * Check if the current request if an infinite scroll request.
	 */
	public static function doing_infinite_scroll() {
		if ( ! defined( 'DOING_AJAX' ) ||  ! DOING_AJAX || ! isset( $_REQUEST['action'] ) ) {
			return false;
		}

		foreach ( self::$inf_scroll_items as $ajax_action ) {
			if ( $_REQUEST['action'] === $ajax_action ) {
				return true;
			}
		}
		return false;
	}

}
