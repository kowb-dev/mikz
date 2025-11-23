<?php
/**
 * Mobile Bottom Navigation
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display the mobile bottom navigation.
 * Hooks into `wp_footer`.
 */
function kb_mobile_bottom_nav() {
	?>
	<!-- Mobile Bottom Navigation -->
	<nav class="mkx-mobile-bottom-nav" role="navigation" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'shoptimizer-child' ); ?>">
		<div class="mkx-mobile-bottom-nav__content">
			<?php
			if ( has_nav_menu( 'mkx_mobile_bottom_nav' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'mkx_mobile_bottom_nav',
						'container'      => false,
						'items_wrap'     => '%3$s', // No wrapper.
						'depth'          => 1,
						'walker'         => new MKX_Mobile_Nav_Walker(),
						'fallback_cb'    => false,
					)
				);
			} else {
				// Fallback with a message in case the menu is not assigned.
				echo '<p class="mkx-mobile-nav-item">Please assign a menu to the \'Нижняя мобильная навигация (MKX)\' location.</p>';
			}
			?>
		</div>
	</nav>
	<?php
}
add_action( 'wp_footer', 'kb_mobile_bottom_nav' );
