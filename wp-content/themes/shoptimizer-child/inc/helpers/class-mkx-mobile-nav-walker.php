<?php
/**
 * Custom Walker class for the Mobile Bottom Navigation.
 *
 * This class is designed to output a custom structure for the mobile bottom navigation,
 * allowing for buttons, icons, and specific classes to be controlled from the WP Admin Menu Editor.
 *
 * @package Shoptimizer Child
 * @version 1.1.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MKX_Mobile_Nav_Walker extends Walker_Nav_Menu {
    /**
     * Starts the element output.
     *
     * This method is the core of the walker. It generates the custom HTML for each menu item.
     * It checks for special CSS classes on the menu item to determine its type (button or link)
     * and what icon to use.
     *
     * To use:
     * - Add 'is-button' class to a menu item to render it as a <button>.
     * - Add 'icon-ICON_NAME' class to set an icon (e.g., 'icon-house' becomes 'ph-house').
     * - Add 'item-ITEM_NAME' class for a specific item hook (e.g., 'item-catalog' for the ID).
     *
     * @param string   $output            Used to append additional content (passed by reference).
     * @param WP_Post  $item              Menu item data object.
     * @param int      $depth             Depth of menu item. Not used here.
     * @param stdClass $args              An object of wp_nav_menu() arguments.
     * @param int      $id                Current item ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $is_button           = in_array( 'is-button', $item->classes );
        $icon_class          = '';
        $item_specific_class = '';

        if ( ! empty( $item->classes ) ) {
            foreach ( $item->classes as $class ) {
                if ( strpos( $class, 'icon-' ) === 0 ) {
                    $icon_class = 'ph ' . str_replace( 'icon-', 'ph-', $class );
                }
                if ( strpos( $class, 'item-' ) === 0 ) {
                    $item_specific_class = 'mkx-mobile-nav-item--' . str_replace( 'item-', '', $class );
                }
            }
        }

        $tag = $is_button ? 'button' : 'a';

        $atts = array();
        $atts['class'] = 'mkx-mobile-nav-item ' . $item_specific_class;
        if ( ! empty( $item->attr_title ) ) {
            $atts['aria-label'] = $item->attr_title;
        } else {
            $atts['aria-label'] = $item->title;
        }

        if ( ! $is_button ) {
            $atts['href'] = ! empty( $item->url ) ? $item->url : '#';
        } else {
            $atts['type'] = 'button';
            if ( in_array( 'item-catalog', $item->classes, true ) ) {
                $atts['id'] = 'mobileCatalogToggle';
            }
        }

        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $item_output = $args->before;
        $item_output .= '<' . $tag . $attributes . '>';
        $item_output .= $args->link_before;

        if ( ! empty( $icon_class ) ) {
            $item_output .= '<i class="mkx-mobile-nav-item__icon ' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>';
        }

        $item_output .= '<span class="mkx-mobile-nav-item__text">' . apply_filters( 'the_title', $item->title, $item->ID ) . '</span>';

        $item_url = strtolower( $item->url );
        $badge_html = '';
        
        if ( strpos( $item_url, 'cart' ) !== false && class_exists( 'WooCommerce' ) && WC()->cart ) {
            $cart_count = WC()->cart->get_cart_contents_count();
            if ( $cart_count > 0 ) {
                $badge_html = '<span class="mkx-mobile-nav-cart-count mkx-badge-visible">' . $cart_count . '</span>';
            }
        } elseif ( strpos( $item_url, 'wishlist' ) !== false && function_exists( 'mkx_get_wishlist_count' ) ) {
            $wishlist_count = mkx_get_wishlist_count();
            if ( $wishlist_count > 0 ) {
                $badge_html = '<span class="mkx-mobile-nav-wishlist-count mkx-badge-visible">' . $wishlist_count . '</span>';
            }
        } elseif ( strpos( $item_url, 'compare' ) !== false && function_exists( 'mkx_get_compare_count' ) ) {
            $compare_count = mkx_get_compare_count();
            if ( $compare_count > 0 ) {
                $badge_html = '<span class="mkx-mobile-nav-compare-count mkx-badge-visible">' . $compare_count . '</span>';
            }
        }
        
        $item_output .= $badge_html;

        $item_output .= $args->link_after;
        $item_output .= '</' . $tag . '>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    /**
     * We don't want to output </li> tags.
     */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        // Intentionally empty.
    }

    /**
     * We don't want to output <ul> tags.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        // Intentionally empty.
    }

    /**
     * We don't want to output </ul> tags.
     */
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        // Intentionally empty.
    }
}
