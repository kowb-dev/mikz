<?php
/**
 * Registers and configures custom navigation menus and walkers.
 *
 * @package Shoptimizer Child
 * @version 1.0.2
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register custom navigation menu locations.
 */
function mkx_register_nav_menus() {
    register_nav_menus(
        [
            'mkx_action_links_menu' => __( 'Action Links Menu', 'shoptimizer-child' ),
            'mkx_top_header_menu'   => __( 'Top Header Menu', 'shoptimizer-child' ),
        ]
    );
}
add_action( 'after_setup_theme', 'mkx_register_nav_menus' );

/**
 * Add custom attributes to the top header menu links.
 *
 * @param array    $atts  The HTML attributes applied to the menu item's `<a>` element.
 * @param WP_Post  $item  The current menu item.
 * @param stdClass $args  An object of `wp_nav_menu()` arguments.
 * @return array
 */
function mkx_add_top_menu_link_class( $atts, $item, $args ) {
    if ( isset( $args->theme_location ) && 'mkx_top_header_menu' === $args->theme_location ) {
        $atts['class'] = 'mkx-top-menu-link';
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'mkx_add_top_menu_link_class', 10, 3 );


/**
 * Custom Walker class for the Action Links Menu.
 *
 * This walker generates a flat list of <a> tags without ul/li wrappers,
 * and includes an icon and a text span inside each link.
 */
class Mkx_Action_Links_Walker extends Walker_Nav_Menu {

    /**
     * Starts the element output.
     *
     * @param string   $output            Used to append additional content (passed by reference).
     * @param WP_Post  $item              Menu item data object.
     * @param int      $depth             Depth of menu item. Used for padding.
     * @param stdClass $args              An object of wp_nav_menu() arguments.
     * @param int      $id                Current item ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $icon_class = get_post_meta( $item->ID, '_menu_item_icon_class', true );
        $icon_html  = $icon_class ? '<i class="ph ' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>' : '';

        $output .= '<a href="' . esc_url( $item->url ) . '" class="mkx-action-link">';
        $output .= $icon_html;
        $output .= '<span class="mkx-action-text">' . esc_html( $item->title ) . '</span>';
        
        $item_url = strtolower( $item->url );
        $badge_html = '';
        
        if ( strpos( $item_url, 'wishlist' ) !== false && function_exists( 'mkx_get_wishlist_count' ) ) {
            $wishlist_count = mkx_get_wishlist_count();
            if ( $wishlist_count > 0 ) {
                $badge_html = '<span class="mkx-badge-count mkx-wishlist-count mkx-badge-visible">' . $wishlist_count . '</span>';
            }
        } elseif ( strpos( $item_url, 'compare' ) !== false && function_exists( 'mkx_get_compare_count' ) ) {
            $compare_count = mkx_get_compare_count();
            if ( $compare_count > 0 ) {
                $badge_html = '<span class="mkx-badge-count mkx-compare-count mkx-badge-visible">' . $compare_count . '</span>';
            }
        }
        
        $output .= $badge_html;
        $output .= '</a>';
    }

    /**
     * Ends the element output, but we don't need a closing tag.
     */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        // No closing tag needed for this custom structure.
    }

    /**
     * Starts the list before the elements are added. We don't want a <ul>.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        // Do nothing.
    }

    /**
     * Ends the list of after the elements are added. We don't want a </ul>.
     */
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        // Do nothing.
    }
}

/**
 * Adds a custom field to the menu item editor for the Action Links menu.
 *
 * @param int      $item_id Menu item ID.
 * @param WP_Post  $item    Menu item data object.
 */
function mkx_add_icon_class_field_to_menu_items( $item_id, $item ) {
    // This field will appear on all menu items, which is acceptable.
    // We could add a check here to only show it for specific menus if needed.
    $icon_class = get_post_meta( $item_id, '_menu_item_icon_class', true );
    ?>
    <p class="description description-wide">
        <label for="edit-menu-item-icon-class-<?php echo esc_attr( $item_id ); ?>">
            <?php esc_html_e( 'Icon Class (e.g., ph-package)', 'shoptimizer-child' ); ?><br />
            <input type="text" id="edit-menu-item-icon-class-<?php echo esc_attr( $item_id ); ?>" class="widefat" name="menu-item-icon-class[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $icon_class ); ?>" />
        </label>
    </p>
    <?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'mkx_add_icon_class_field_to_menu_items', 10, 2 );

/**
 * Saves the custom menu item field (icon class).
 *
 * @param int $menu_id         ID of the menu being updated.
 * @param int $menu_item_db_id ID of the menu item being updated.
 */
function mkx_save_icon_class_field( $menu_id, $menu_item_db_id ) {
    if ( isset( $_POST['menu-item-icon-class'][ $menu_item_db_id ] ) ) {
        $sanitized_data = sanitize_text_field( $_POST['menu-item-icon-class'][ $menu_item_db_id ] );
        update_post_meta( $menu_item_db_id, '_menu_item_icon_class', $sanitized_data );
    }
}
add_action( 'wp_update_nav_menu_item', 'mkx_save_icon_class_field', 10, 2 );