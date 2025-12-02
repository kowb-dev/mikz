<?php
/**
 * AJAX Handler
 *
 * @package MKX_Live_Search
 * @version 1.0.0
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
 * Live search AJAX handler - добавляем сортировку категорий
 * Заменить метод в inc/class-mkx-search-ajax.php
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
    
    // СОРТИРОВКА КАТЕГОРИЙ по бренду и типу детали
    $categories = $this->sort_categories_by_relevance($categories, $search_term);

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
 * Сортировка категорий по релевантности
 */
private function sort_categories_by_relevance($categories, $search_term) {
    if (empty($categories)) {
        return $categories;
    }
    
    $search_lower = mb_strtolower($search_term, 'UTF-8');
    
    // Определяем тип детали
    $part_type_keywords = array(
        'battery' => array('акб', 'fr,', 'аккум', 'батарея', 'battery', 'аккумулятор'),
        'display' => array('дисплей', 'диспл', 'дисп', 'экран', 'lcd', 'модуль', 'display', 'screen'),
        'back_cover' => array('крышка', 'корпус', 'рамка', 'задняя', 'back', 'cover'),
        'glass' => array('стекло', 'тачскрин', 'тач', 'glass', 'touch'),
        'flex' => array('шлейф', 'флекс', 'flex'),
        'charging' => array('зарядка', 'порт', 'разъем', 'charging', 'port'),
        'speaker' => array('динамик', 'спикер', 'speaker'),
    );

    $detected_part_type = null;
    foreach ($part_type_keywords as $type => $keywords) {
        foreach ($keywords as $keyword) {
            if (mb_strpos($search_lower, $keyword) !== false) {
                $detected_part_type = $type;
                break 2;
            }
        }
    }
    
    // Определяем бренд
    $brand_keywords = array(
        'iphone' => array('iphone', 'айфон', 'ифон', 'іфон', 'айфoн', 'apple', 'аппле'),
        'samsung' => array('samsung', 'самсунг', 'самсунк', 'сансунг', 'самс'),
        'xiaomi' => array('xiaomi', 'сяоми', 'ксяоми', 'шаоми', 'redmi', 'редми'),
        'huawei' => array('huawei', 'хуавей', 'хуавэй', 'honor', 'хонор'),
        'nokia' => array('nokia', 'нокиа', 'нокия'),
        'oppo' => array('oppo', 'оппо'),
        'vivo' => array('vivo', 'виво'),
        'realme' => array('realme', 'реалме', 'риалми'),
        'infinix' => array('infinix', 'инфиникс'),
        'tecno' => array('tecno', 'текно'),
    );

    $detected_brand = null;
    foreach ($brand_keywords as $brand => $keywords) {
        foreach ($keywords as $keyword) {
            if (mb_strpos($search_lower, $keyword) !== false) {
                $detected_brand = $brand;
                break 2;
            }
        }
    }
    
    // Маппинг категорий
    $category_mapping = array(
        'displei-iphone' => array('brand' => 'iphone', 'type' => 'display'),
        'displei-huawei-honor' => array('brand' => 'huawei', 'type' => 'display'),
        'displei-dlya-infinix' => array('brand' => 'infinix', 'type' => 'display'),
        'displei-oppo' => array('brand' => 'oppo', 'type' => 'display'),
        'displei-realme' => array('brand' => 'realme', 'type' => 'display'),
        'displei-dlya-samsung' => array('brand' => 'samsung', 'type' => 'display'),
        'displei-tecno' => array('brand' => 'tecno', 'type' => 'display'),
        'displei-vivo' => array('brand' => 'vivo', 'type' => 'display'),
        'displei-xiaomi-redmi' => array('brand' => 'xiaomi', 'type' => 'display'),
        'displei-ekrany-lcd' => array('brand' => null, 'type' => 'display'),
        'akb-iphone' => array('brand' => 'iphone', 'type' => 'battery'),
        'akb-huawei-honor' => array('brand' => 'huawei', 'type' => 'battery'),
        'akb-dlya-nokia' => array('brand' => 'nokia', 'type' => 'battery'),
        'akb-dlya-samsung' => array('brand' => 'samsung', 'type' => 'battery'),
        'akb-dlya-xiaomi-redmi' => array('brand' => 'xiaomi', 'type' => 'battery'),
        'zadnyaya-kryshka-ramka-korpus-dlya-iphone' => array('brand' => 'iphone', 'type' => 'back_cover'),
        'zadnyaya-kryshka-ramka-korpus' => array('brand' => null, 'type' => 'back_cover'),
        'steklo' => array('brand' => null, 'type' => 'glass'),
        'tachskrin' => array('brand' => null, 'type' => 'glass'),
        'akkumulyatory' => array('brand' => null, 'type' => 'battery'),
    );
    
    // Вычисляем приоритет для каждой категории
    foreach ($categories as &$category) {
        $priority = 0;
        
        if (isset($category_mapping[$category['slug']])) {
            $cat_brand = $category_mapping[$category['slug']]['brand'];
            $cat_type = $category_mapping[$category['slug']]['type'];
            
            // Полное совпадение: бренд + тип
            if ($detected_brand && $detected_part_type && 
                $cat_brand === $detected_brand && $cat_type === $detected_part_type) {
                $priority = 100;
            }
            // Только тип детали
            elseif ($detected_part_type && $cat_type === $detected_part_type) {
                $priority = 50;
            }
            // Только бренд
            elseif ($detected_brand && $cat_brand === $detected_brand) {
                $priority = 30;
            }
            // Общая категория
            elseif ($cat_brand === null && $detected_part_type && $cat_type === $detected_part_type) {
                $priority = 20;
            }
        }
        
        $category['priority'] = $priority;
    }
    unset($category); // Разрываем ссылку
    
    // Сортируем по приоритету (от большего к меньшему)
    usort($categories, function($a, $b) {
        if ($a['priority'] !== $b['priority']) {
            return $b['priority'] - $a['priority'];
        }
        return strcmp($a['name'], $b['name']);
    });
    
    // Убираем поле priority из ответа (не нужно на фронте)
    foreach ($categories as &$category) {
        unset($category['priority']);
    }
    
    return $categories;
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
