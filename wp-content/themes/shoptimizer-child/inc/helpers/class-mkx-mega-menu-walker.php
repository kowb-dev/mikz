<?php
/**
 * Custom Mega Menu Walker for the theme
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MKX_Mega_Menu_Walker extends Walker_Nav_Menu {
    /**
     * Starts the element output.
     *
     * @param string   &$output Used to append additional content (passed by reference).
     * @param WP_Post  $item    Menu item data object.
     * @param int      $depth   Depth of menu item. Used for padding.
     * @param stdClass $args    An object of wp_nav_menu() arguments.
     * @param int      $id      Current item ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        // Depth 0: Column Title
        if ( 0 === $depth ) {
            // Start a new column and add the title
            $output .= '<div class="mkx-catalog-megamenu__column">';
            $output .= '<h3 class="mkx-catalog-megamenu__title">' . esc_html( $item->title ) . '</h3>';
            // The <ul> for sub-items will be added by start_lvl
        }
        // Depth 1: Link item
        else if ( 1 === $depth ) {
            $output .= '<li>';
            $atts = array();
            $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
            $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
            $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
            $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
            $atts['class']  = 'mkx-catalog-megamenu__link';
            $atts['role']  = 'menuitem';

            $attributes = '';
            foreach ( $atts as $attr => $value ) {
                if ( ! empty( $value ) ) {
                    $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                    $attributes .= ' ' . $attr . '="' . $value . '"';
                }
            }

            $item_output = $args->before;
            $item_output .= '<a'. $attributes .' >';
            $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
            $item_output .= '</a>';
            $item_output .= $args->after;

            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
        }
    }

    /**
     * Ends the element output.
     *
     * @param string   &$output Used to append additional content (passed by reference).
     * @param WP_Post  $item    Menu item data object.
     * @param int      $depth   Depth of menu item. Used for padding.
     * @param stdClass $args    An object of wp_nav_menu() arguments.
     */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( 0 === $depth ) {
            // Close the column div
            $output .= "</div>\n";
        } else if ( 1 === $depth ) {
            $output .= "</li>\n";
        }
    }

    /**
     * Starts the list before the elements are added.
     *
     * @param string   &$output Used to append additional content (passed by reference).
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        // Only start a <ul> if we are at depth 0 (which is inside a column)
        if ( 0 === $depth ) {
            $output .= '<ul class="mkx-catalog-megamenu__list">';
        }
    }

    /**
     * Ends the list of after the elements are added.
     *
     * @param string   &$output Used to append additional content (passed by reference).
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth ) {
            $output .= "</ul>\n";
        }
    }
}
