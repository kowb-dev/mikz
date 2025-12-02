<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin Premium class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Compare
 * @version 2.0.0
 */

defined( 'YITH_WOOCOMPARE' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Woocompare_Admin_Premium' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_Woocompare_Admin_Premium extends YITH_Woocompare_Admin {

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __construct() {

			parent::__construct();



			// Add admin tabs.
			add_filter( 'yith_woocompare_admin_tabs', array( $this, 'add_admin_tabs' ), 10, 1 );
			add_action( 'yith_woocompare_shortcode_tab', array( $this, 'shortcode_tab' ) );
		}

		/**
		 * Add premium admin tabs
		 *
		 * @since 2.0.0
		 * @access public
		 * @param array $tabs An array of admin settings tabs.
		 * @return mixed
		 */
		public function add_admin_tabs( $tabs ) {

			$tabs['table']     = __( 'Comparison Table', 'yith-woocommerce-compare' );
			$tabs['share']     = __( 'Social Network Sites Sharing', 'yith-woocommerce-compare' );
			$tabs['related']   = __( 'Related Products', 'yith-woocommerce-compare' );
			$tabs['style']     = __( 'Style', 'yith-woocommerce-compare' );
			$tabs['shortcode'] = __( 'Build Shortcode', 'yith-woocommerce-compare' );

			return $tabs;
		}

		/**
		 * Content of build shortcode tab in plugin setting
		 *
		 * @access public
		 * @since 2.0.3
		 */
		public function shortcode_tab() {
			$shortcode_tab_template = YITH_WOOCOMPARE_TEMPLATE_PATH . '/admin/yith-woocompare-shortcode-tab.php';
			if ( file_exists( $shortcode_tab_template ) ) {
				include_once $shortcode_tab_template;
			}
		}
	}
}
