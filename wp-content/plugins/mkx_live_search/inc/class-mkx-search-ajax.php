<?php
/**
 * AJAX Handler
 *
 * @package MKX_Live_Search
 * @version 1.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * MKX Search AJAX Class
 *
 * @class MKX_Search_AJAX
 */
class MKX_Search_AJAX {

    /**
     * Instance
     *
     * @var MKX_Search_AJAX
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return MKX_Search_AJAX
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('wp_ajax_mkx_live_search', array($this, 'live_search'));
        add_action('wp_ajax_nopriv_mkx_live_search', array($this, 'live_search'));
        add_action('wp_ajax_mkx_filter_by_category', array($this, 'filter_by_category'));
        add_action('wp_ajax_nopriv_mkx_filter_by_category', array($this, 'filter_by_category'));
    }

/**
 * Live search AJAX handler
 */
public function live_search() {
    check_ajax_referer('mkx_live_search_nonce', 'nonce');

    $search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';

    if (empty($search_term) || strlen($search_term) < 2) {
        wp_send_json_error(array(
            'message' => __('Введите минимум 2 символа', 'mkx-live-search'),
        ));
    }

    $query_handler = MKX_Search_Query::instance();
    
    $products = $query_handler->search_products($search_term, array(
        'limit' => 10,
    ));

    $categories = $query_handler->get_search_categories($search_term);

    $formatted_products = array();
    foreach ($products as $product) {
        $formatted = $query_handler->format_product_response($product);
        if ($formatted) {
            $formatted_products[] = $formatted;
        }
    }

    wp_send_json_success(array(
        'products' => $formatted_products,
        'categories' => $categories,
        'search_term' => $search_term,
    ));
}

    /**
     * Filter by category AJAX handler
     *
     * @return void
     */
    public function filter_by_category() {
        check_ajax_referer('mkx_live_search_nonce', 'nonce');

        $search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';

        if (empty($search_term)) {
            wp_send_json_error(array(
                'message' => __('Поисковый запрос не указан', 'mkx-live-search'),
            ));
        }

        $query_handler = MKX_Search_Query::instance();

        $args = array(
            'limit' => -1,
        );

        if (!empty($category)) {
            $args['category'] = $category;
        }

        $products = $query_handler->search_products($search_term, $args);

        $formatted_products = array();
        foreach ($products as $product) {
            $formatted = $query_handler->format_product_response($product);
            if ($formatted) {
                $formatted_products[] = $formatted;
            }
        }

        ob_start();
        if (!empty($formatted_products)) {
            foreach ($formatted_products as $product_data) {
                wc_get_template_part('content', 'product');
                $this->render_product_card($product_data);
            }
        } else {
            echo '<div class="mkx-no-results">' . esc_html__('Товары не найдены', 'mkx-live-search') . '</div>';
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'count' => count($formatted_products),
        ));
    }

    /**
     * Render product card
     *
     * @param array $product_data Product data
     * @return void
     */
    private function render_product_card($product_data) {
        ?>
        <div class="mkx-product-card" data-product-id="<?php echo esc_attr($product_data['id']); ?>">
            <a href="<?php echo esc_url($product_data['url']); ?>" class="mkx-product-card__link">
                <div class="mkx-product-card__image">
                    <img src="<?php echo esc_url($product_data['image']); ?>" 
                         alt="<?php echo esc_attr($product_data['title']); ?>"
                         loading="lazy"
                         width="300"
                         height="300">
                    <?php if (!$product_data['in_stock']) : ?>
                        <span class="mkx-product-card__badge mkx-product-card__badge--out-of-stock">
                            <?php esc_html_e('Нет в наличии', 'mkx-live-search'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="mkx-product-card__content">
                    <h3 class="mkx-product-card__title"><?php echo esc_html($product_data['title']); ?></h3>
                    <?php if (!empty($product_data['sku'])) : ?>
                        <div class="mkx-product-card__sku">
                            <?php echo esc_html__('Артикул:', 'mkx-live-search') . ' ' . esc_html($product_data['sku']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="mkx-product-card__price">
                        <?php echo wp_kses_post($product_data['price']); ?>
                    </div>
                </div>
            </a>
        </div>
        <?php
    }
}