<?php
/**
 * Custom Compare System
 *
 * @package Shoptimizer Child
 * @version 1.6.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MKX_Compare {
    
    private static $instance = null;
    private $limit = 4;
    
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_mkx_compare_add', array( $this, 'ajax_add' ) );
        add_action( 'wp_ajax_nopriv_mkx_compare_add', array( $this, 'ajax_add' ) );
        add_action( 'wp_ajax_mkx_compare_remove', array( $this, 'ajax_remove' ) );
        add_action( 'wp_ajax_nopriv_mkx_compare_remove', array( $this, 'ajax_remove' ) );
        add_action( 'wp_ajax_mkx_compare_clear', array( $this, 'ajax_clear' ) );
        add_action( 'wp_ajax_nopriv_mkx_compare_clear', array( $this, 'ajax_clear' ) );
        add_action( 'wp_ajax_mkx_compare_get_count', array( $this, 'ajax_get_count' ) );
        add_action( 'wp_ajax_nopriv_mkx_compare_get_count', array( $this, 'ajax_get_count' ) );
        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_button_loop' ), 20 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'add_button_single' ), 36 );
        add_shortcode( 'mkx_compare', array( $this, 'compare_page_shortcode' ) );
    }
    
    public function enqueue_assets() {
        wp_enqueue_style(
            'mkx-compare',
            get_stylesheet_directory_uri() . '/assets/css/custom-compare.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'mkx-compare',
            get_stylesheet_directory_uri() . '/assets/js/custom-compare.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
        
        wp_localize_script( 'mkx-compare', 'mkxCompare', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'mkx_compare_nonce' ),
            'limit' => $this->limit,
            'limitMessage' => sprintf( 'Можно сравнить не более %d товаров', $this->limit )
        ) );
    }
    
    public function get_compare_list() {
        if ( WC()->session ) {
            $compare = WC()->session->get( 'mkx_compare', array() );
            return is_array( $compare ) ? $compare : array();
        }
        
        if ( isset( $_COOKIE['mkx_compare'] ) ) {
            $compare = json_decode( stripslashes( $_COOKIE['mkx_compare'] ), true );
            return is_array( $compare ) ? $compare : array();
        }
        
        return array();
    }
    
    public function save_compare_list( $compare ) {
        $compare = array_values( array_unique( array_filter( array_map( 'absint', $compare ) ) ) );
        
        if ( count( $compare ) > $this->limit ) {
            $compare = array_slice( $compare, 0, $this->limit );
        }
        
        if ( WC()->session ) {
            WC()->session->set( 'mkx_compare', $compare );
        }
        
        wc_setcookie( 'mkx_compare', json_encode( $compare ), time() + ( 30 * DAY_IN_SECONDS ) );
    }
    
    public function add_product( $product_id ) {
        $product_id = absint( $product_id );
        if ( ! $product_id || ! wc_get_product( $product_id ) ) {
            return false;
        }
        
        $compare = $this->get_compare_list();
        
        if ( count( $compare ) >= $this->limit && ! in_array( $product_id, $compare ) ) {
            return 'limit';
        }
        
        if ( ! in_array( $product_id, $compare ) ) {
            $compare[] = $product_id;
            $this->save_compare_list( $compare );
        }
        return true;
    }
    
    public function remove_product( $product_id ) {
        $product_id = absint( $product_id );
        $compare = $this->get_compare_list();
        
        $key = array_search( $product_id, $compare );
        if ( false !== $key ) {
            unset( $compare[ $key ] );
            $this->save_compare_list( $compare );
            return true;
        }
        return false;
    }
    
    public function clear_all() {
        $this->save_compare_list( array() );
        return true;
    }
    
    public function is_in_compare( $product_id ) {
        $compare = $this->get_compare_list();
        return in_array( absint( $product_id ), $compare );
    }
    
    public function get_count() {
        return count( $this->get_compare_list() );
    }
    
    public function ajax_add() {
        check_ajax_referer( 'mkx_compare_nonce', 'nonce' );
        
        if ( ! isset( $_POST['product_id'] ) ) {
            wp_send_json_error( array( 'message' => 'Не указан ID товара' ) );
        }
        
        $product_id = absint( $_POST['product_id'] );
        $result = $this->add_product( $product_id );
        
        if ( $result === 'limit' ) {
            wp_send_json_error( array( 
                'message' => sprintf( 'Можно сравнить не более %d товаров', $this->limit ),
                'limit_reached' => true
            ) );
        } elseif ( $result ) {
            wp_send_json_success( array(
                'message' => 'Товар добавлен к сравнению',
                'count' => $this->get_count()
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Не удалось добавить товар' ) );
        }
    }
    
    public function ajax_remove() {
        check_ajax_referer( 'mkx_compare_nonce', 'nonce' );
        
        if ( ! isset( $_POST['product_id'] ) ) {
            wp_send_json_error( array( 'message' => 'Не указан ID товара' ) );
        }
        
        $product_id = absint( $_POST['product_id'] );
        
        if ( $this->remove_product( $product_id ) ) {
            wp_send_json_success( array(
                'message' => 'Товар удален из сравнения',
                'count' => $this->get_count()
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Не удалось удалить товар' ) );
        }
    }
    
    public function ajax_clear() {
        check_ajax_referer( 'mkx_compare_nonce', 'nonce' );
        
        if ( $this->clear_all() ) {
            wp_send_json_success( array(
                'message' => 'Список сравнения очищен',
                'count' => 0
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Не удалось очистить список' ) );
        }
    }
    
    public function ajax_get_count() {
        check_ajax_referer( 'mkx_compare_nonce', 'nonce' );
        wp_send_json_success( array( 'count' => $this->get_count() ) );
    }
    
    public function add_button_loop() {
        global $product;
        if ( ! $product ) {
            return;
        }
        
        $product_id = $product->get_id();
        $in_compare = $this->is_in_compare( $product_id );
        $class = $in_compare ? 'mkx-compare-btn added' : 'mkx-compare-btn';
        $title = $in_compare ? 'Удалить из сравнения' : 'Добавить к сравнению';
        
        echo '<a href="#" class="' . esc_attr( $class ) . '" data-product-id="' . esc_attr( $product_id ) . '" title="' . esc_attr( $title ) . '">';
        echo '<i class="ph ph-chart-bar" aria-hidden="true"></i>';
        echo '</a>';
    }
    
    public function add_button_single() {
        global $product;
        if ( ! $product ) {
            return;
        }
        
        $product_id = $product->get_id();
        $in_compare = $this->is_in_compare( $product_id );
        $class = $in_compare ? 'mkx-compare-btn mkx-compare-btn-single added' : 'mkx-compare-btn mkx-compare-btn-single';
        $text = $in_compare ? 'Удалить из сравнения' : 'Добавить к сравнению';
        
        echo '<div class="mkx-compare-wrapper">';
        echo '<a href="#" class="' . esc_attr( $class ) . '" data-product-id="' . esc_attr( $product_id ) . '">';
        echo '<i class="ph ph-chart-bar" aria-hidden="true"></i>';
        echo '<span>' . esc_html( $text ) . '</span>';
        echo '</a>';
        echo '</div>';
    }
    
    public function compare_page_shortcode() {
        $compare = $this->get_compare_list();
        
        ob_start();
        
        if ( empty( $compare ) ) {
            echo '<div class="mkx-compare-empty">';
            echo '<i class="ph ph-chart-bar" aria-hidden="true"></i>';
            echo '<h2>Список сравнения пуст</h2>';
            echo '<p>Добавьте товары для сравнения характеристик</p>';
            echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="button">Перейти в каталог</a>';
            echo '</div>';
            return ob_get_clean();
        }
        
        $products = array();
        foreach ( $compare as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( $product ) {
                $products[] = $product;
            }
        }
        
        if ( empty( $products ) ) {
            echo '<div class="mkx-compare-empty">';
            echo '<p>Товары не найдены</p>';
            echo '</div>';
            return ob_get_clean();
        }
        
        echo '<div class="mkx-compare-page">';
        echo '<div class="mkx-compare-header">';
        echo '<h2>Сравнение товаров</h2>';
        echo '<a href="#" class="mkx-compare-clear-all button" title="Очистить список">Очистить всё</a>';
        echo '</div>';
        
        echo '<div class="mkx-compare-table-wrapper">';
        echo '<div class="mkx-compare-table-scroll">';
        echo '<table class="mkx-compare-table">';
        
        echo '<thead>';
        echo '<tr>';
        echo '<th class="mkx-compare-field-label">Товар</th>';
        foreach ( $products as $product ) {
            echo '<td class="mkx-compare-product-cell">';
            echo '<div class="mkx-compare-product-image">';
            echo '<a href="' . esc_url( $product->get_permalink() ) . '">';
            echo $product->get_image( 'woocommerce_thumbnail' );
            echo '</a>';
            echo '</div>';
            echo '<div class="mkx-compare-product-details">';
            echo '<h3 class="mkx-compare-product-name">';
            echo '<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a>';
            echo '</h3>';
            echo '<a href="#" class="mkx-compare-remove" data-product-id="' . esc_attr( $product->get_id() ) . '" title="Удалить">';
            echo '<i class="ph ph-x" aria-hidden="true"></i> Удалить';
            echo '</a>';
            echo '</div>';
            echo '</td>';
        }
        echo '</tr>';
        echo '</thead>';
        
        echo '<tbody>';
        
        echo '<tr>';
        echo '<th class="mkx-compare-field-label">Цена</th>';
        foreach ( $products as $product ) {
            echo '<td class="mkx-compare-field-value">';
            echo $product->get_price_html();
            echo '</td>';
        }
        echo '</tr>';
        
        echo '<tr>';
        echo '<th class="mkx-compare-field-label">Наличие</th>';
        foreach ( $products as $product ) {
            echo '<td class="mkx-compare-field-value">';
            if ( $product->is_in_stock() ) {
                echo '<span class="in-stock">В наличии</span>';
            } else {
                echo '<span class="out-of-stock">Нет в наличии</span>';
            }
            echo '</td>';
        }
        echo '</tr>';
        
        echo '<tr>';
        echo '<th class="mkx-compare-field-label">Действия</th>';
        foreach ( $products as $product ) {
            echo '<td class="mkx-compare-field-value mkx-compare-actions">';
            if ( $product->is_purchasable() && $product->is_in_stock() ) {
                $cart_url = $product->add_to_cart_url();
                echo '<a href="' . esc_url( $cart_url ) . '" class="mkx-compare-add-to-cart ajax_add_to_cart" data-product_id="' . esc_attr( $product->get_id() ) . '" data-quantity="1" title="' . esc_attr__( 'В корзину', 'shoptimizer-child' ) . '">';
                echo '<i class="ph ph-shopping-cart-simple" aria-hidden="true"></i>';
                echo '</a>';
            }
            echo '</td>';
        }
        echo '</tr>';
        
        $attributes = array();
        foreach ( $products as $product ) {
            if ( $product->is_type( 'variable' ) ) {
                $product_attributes = $product->get_variation_attributes();
            } else {
                $product_attributes = $product->get_attributes();
            }
            
            foreach ( $product_attributes as $attribute_name => $attribute ) {
                if ( ! isset( $attributes[ $attribute_name ] ) ) {
                    $attributes[ $attribute_name ] = array();
                }
            }
        }
        
        foreach ( $attributes as $attribute_name => $values ) {
            $taxonomy = str_replace( 'pa_', '', $attribute_name );
            $attr_label = wc_attribute_label( $attribute_name );
            
            echo '<tr>';
            echo '<th class="mkx-compare-field-label">' . esc_html( $attr_label ) . '</th>';
            
            foreach ( $products as $product ) {
                echo '<td class="mkx-compare-field-value">';
                
                if ( $product->is_type( 'variable' ) ) {
                    $product_attributes = $product->get_variation_attributes();
                    if ( isset( $product_attributes[ $attribute_name ] ) ) {
                        $terms = $product_attributes[ $attribute_name ];
                        if ( is_array( $terms ) ) {
                            echo esc_html( implode( ', ', $terms ) );
                        } else {
                            echo esc_html( $terms );
                        }
                    } else {
                        echo '—';
                    }
                } else {
                    $attribute = $product->get_attribute( $attribute_name );
                    echo $attribute ? esc_html( $attribute ) : '—';
                }
                
                echo '</td>';
            }
            
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        return ob_get_clean();
    }
}

function MKX_Compare() {
    return MKX_Compare::instance();
}

MKX_Compare();
