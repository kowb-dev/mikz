<?php
/**
 * Custom Wishlist System
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MKX_Wishlist {
    
    private static $instance = null;
    
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_mkx_wishlist_add', array( $this, 'ajax_add' ) );
        add_action( 'wp_ajax_nopriv_mkx_wishlist_add', array( $this, 'ajax_add' ) );
        add_action( 'wp_ajax_mkx_wishlist_remove', array( $this, 'ajax_remove' ) );
        add_action( 'wp_ajax_nopriv_mkx_wishlist_remove', array( $this, 'ajax_remove' ) );
        add_action( 'wp_ajax_mkx_wishlist_get_count', array( $this, 'ajax_get_count' ) );
        add_action( 'wp_ajax_nopriv_mkx_wishlist_get_count', array( $this, 'ajax_get_count' ) );
        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_button_loop' ), 15 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'add_button_single' ), 35 );
        add_shortcode( 'mkx_wishlist', array( $this, 'wishlist_page_shortcode' ) );
    }
    
    public function enqueue_assets() {
        wp_enqueue_style(
            'mkx-wishlist',
            get_stylesheet_directory_uri() . '/assets/css/custom-wishlist.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'mkx-wishlist',
            get_stylesheet_directory_uri() . '/assets/js/custom-wishlist.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
        
        wp_localize_script( 'mkx-wishlist', 'mkxWishlist', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'mkx_wishlist_nonce' )
        ) );
    }
    
    public function get_wishlist() {
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $wishlist = get_user_meta( $user_id, '_mkx_wishlist', true );
            return is_array( $wishlist ) ? $wishlist : array();
        } else {
            if ( WC()->session ) {
                $wishlist = WC()->session->get( 'mkx_wishlist', array() );
                return is_array( $wishlist ) ? $wishlist : array();
            }
            if ( isset( $_COOKIE['mkx_wishlist'] ) ) {
                $wishlist = json_decode( stripslashes( $_COOKIE['mkx_wishlist'] ), true );
                return is_array( $wishlist ) ? $wishlist : array();
            }
        }
        return array();
    }
    
    public function save_wishlist( $wishlist ) {
        $wishlist = array_values( array_unique( array_filter( array_map( 'absint', $wishlist ) ) ) );
        
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            update_user_meta( $user_id, '_mkx_wishlist', $wishlist );
        } else {
            if ( WC()->session ) {
                WC()->session->set( 'mkx_wishlist', $wishlist );
            }
            wc_setcookie( 'mkx_wishlist', json_encode( $wishlist ), time() + YEAR_IN_SECONDS );
        }
    }
    
    public function add_product( $product_id ) {
        $product_id = absint( $product_id );
        if ( ! $product_id || ! wc_get_product( $product_id ) ) {
            return false;
        }
        
        $wishlist = $this->get_wishlist();
        if ( ! in_array( $product_id, $wishlist ) ) {
            $wishlist[] = $product_id;
            $this->save_wishlist( $wishlist );
        }
        return true;
    }
    
    public function remove_product( $product_id ) {
        $product_id = absint( $product_id );
        $wishlist = $this->get_wishlist();
        
        $key = array_search( $product_id, $wishlist );
        if ( false !== $key ) {
            unset( $wishlist[ $key ] );
            $this->save_wishlist( $wishlist );
            return true;
        }
        return false;
    }
    
    public function is_in_wishlist( $product_id ) {
        $wishlist = $this->get_wishlist();
        return in_array( absint( $product_id ), $wishlist );
    }
    
    public function get_count() {
        return count( $this->get_wishlist() );
    }
    
    public function ajax_add() {
        check_ajax_referer( 'mkx_wishlist_nonce', 'nonce' );
        
        if ( ! isset( $_POST['product_id'] ) ) {
            wp_send_json_error( array( 'message' => 'Не указан ID товара' ) );
        }
        
        $product_id = absint( $_POST['product_id'] );
        
        if ( $this->add_product( $product_id ) ) {
            wp_send_json_success( array(
                'message' => 'Товар добавлен в избранное',
                'count' => $this->get_count()
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Не удалось добавить товар' ) );
        }
    }
    
    public function ajax_remove() {
        check_ajax_referer( 'mkx_wishlist_nonce', 'nonce' );
        
        if ( ! isset( $_POST['product_id'] ) ) {
            wp_send_json_error( array( 'message' => 'Не указан ID товара' ) );
        }
        
        $product_id = absint( $_POST['product_id'] );
        
        if ( $this->remove_product( $product_id ) ) {
            wp_send_json_success( array(
                'message' => 'Товар удален из избранного',
                'count' => $this->get_count()
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Не удалось удалить товар' ) );
        }
    }
    
    public function ajax_get_count() {
        check_ajax_referer( 'mkx_wishlist_nonce', 'nonce' );
        wp_send_json_success( array( 'count' => $this->get_count() ) );
    }
    
    public function add_button_loop() {
        global $product;
        if ( ! $product ) {
            return;
        }
        
        $product_id = $product->get_id();
        $in_wishlist = $this->is_in_wishlist( $product_id );
        $class = $in_wishlist ? 'mkx-wishlist-btn added' : 'mkx-wishlist-btn';
        $title = $in_wishlist ? 'Удалить из избранного' : 'Добавить в избранное';
        
        echo '<a href="#" class="' . esc_attr( $class ) . '" data-product-id="' . esc_attr( $product_id ) . '" title="' . esc_attr( $title ) . '">';
        echo '<i class="ph ph-heart" aria-hidden="true"></i>';
        echo '</a>';
    }
    
    public function add_button_single() {
        global $product;
        if ( ! $product ) {
            return;
        }
        
        $product_id = $product->get_id();
        $in_wishlist = $this->is_in_wishlist( $product_id );
        $class = $in_wishlist ? 'mkx-wishlist-btn mkx-wishlist-btn-single added' : 'mkx-wishlist-btn mkx-wishlist-btn-single';
        $text = $in_wishlist ? 'Удалить из избранного' : 'Добавить в избранное';
        
        echo '<div class="mkx-wishlist-wrapper">';
        echo '<a href="#" class="' . esc_attr( $class ) . '" data-product-id="' . esc_attr( $product_id ) . '">';
        echo '<i class="ph ph-heart" aria-hidden="true"></i>';
        echo '<span>' . esc_html( $text ) . '</span>';
        echo '</a>';
        echo '</div>';
    }
    
    public function wishlist_page_shortcode() {
        $wishlist = $this->get_wishlist();
        
        ob_start();
        
        if ( empty( $wishlist ) ) {
            echo '<div class="mkx-wishlist-empty">';
            echo '<i class="ph ph-heart" aria-hidden="true"></i>';
            echo '<h2>Ваш список избранного пуст</h2>';
            echo '<p>Добавьте товары в избранное, чтобы не потерять их</p>';
            echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="button">Перейти в каталог</a>';
            echo '</div>';
            return ob_get_clean();
        }
        
        echo '<div class="mkx-wishlist-page">';
        echo '<table class="mkx-wishlist-table shop_table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="product-remove"></th>';
        echo '<th class="product-thumbnail">Изображение</th>';
        echo '<th class="product-name">Товар</th>';
        echo '<th class="product-price">Цена</th>';
        echo '<th class="product-stock">Наличие</th>';
        echo '<th class="product-add-to-cart">Действия</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ( $wishlist as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                continue;
            }
            
            echo '<tr data-product-id="' . esc_attr( $product_id ) . '">';
            
            echo '<td class="product-remove">';
            echo '<a href="#" class="mkx-wishlist-remove" data-product-id="' . esc_attr( $product_id ) . '" title="Удалить">';
            echo '<i class="ph ph-x" aria-hidden="true"></i>';
            echo '</a>';
            echo '</td>';
            
            echo '<td class="product-thumbnail">';
            echo '<a href="' . esc_url( $product->get_permalink() ) . '">';
            echo $product->get_image( 'thumbnail' );
            echo '</a>';
            echo '</td>';
            
            echo '<td class="product-name">';
            echo '<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a>';
            echo '</td>';
            
            echo '<td class="product-price">';
            echo $product->get_price_html();
            echo '</td>';
            
            echo '<td class="product-stock">';
            if ( $product->is_in_stock() ) {
                echo '<span class="in-stock">В наличии</span>';
            } else {
                echo '<span class="out-of-stock">Нет в наличии</span>';
            }
            echo '</td>';
            
            echo '<td class="product-add-to-cart">';
            if ( $product->is_purchasable() && $product->is_in_stock() ) {
                woocommerce_template_loop_add_to_cart();
            }
            echo '</td>';
            
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        return ob_get_clean();
    }
}

function MKX_Wishlist() {
    return MKX_Wishlist::instance();
}

MKX_Wishlist();
