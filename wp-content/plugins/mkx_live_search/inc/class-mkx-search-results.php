<?php
/**
 * Search Results Handler with Enhanced Search
 *
 * @package MKX_Live_Search
 * @version 1.0.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKX_Search_Results {

    private static $instance = null;
    private $search_combinations = array();

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_search_combinations();
        add_action('woocommerce_before_shop_loop', array($this, 'display_search_categories'), 5);
        add_filter('pre_get_posts', array($this, 'modify_search_query'));
        add_action('save_post_product', array($this, 'clear_cache_on_product_update'));
        add_action('edited_product_cat', array($this, 'clear_cache_on_category_update'));
    }

    /**
     * Initialize search combinations
     */
    private function init_search_combinations() {
        $this->search_combinations = array(
            'brands' => array(
                'apple' => array('apple', 'аппле', 'апл', 'эпл', 'эппл', 'апель', 'фззду'),
                'iphone' => array('iphone', 'айфон', 'ифон', 'іфон', 'b`jyt', 'b`jy', 'айфoн'),
                'ipad' => array('ipad', 'айпад', 'айпэд', 'ипад', 'іпад', 'b`fl'),
                'samsung' => array('samsung', 'самсунг', 'самсунк', 'сансунг', 'самсун', 'самс', 'cfvceyu', 'cfvcey'),
                'xiaomi' => array('xiaomi', 'сяоми', 'ксяоми', 'шаоми', 'ксиаоми', '[bfjvb', '[bfv'),
                'redmi' => array('redmi', 'редми', 'htlvb'),
                'huawei' => array('huawei', 'хуавей', 'хуавэй', 'хавей', 'хуавай', 'uefdtb', 'uefdt'),
                'honor' => array('honor', 'хонор', 'хоннор', 'ujyjh'),
                'nokia' => array('nokia', 'нокиа', 'нокия', 'yjrbf'),
                'oppo' => array('oppo', 'оппо', 'опо', 'jggj'),
                'vivo' => array('vivo', 'виво', 'віво', 'dbdj'),
                'realme' => array('realme', 'реалме', 'риалми', 'риалме', 'реалми', 'htfkvt'),
                'infinix' => array('infinix', 'инфиникс', 'инфініх', 'byabyb['),
                'tecno' => array('tecno', 'текно', 'тэкно', 'ntryj'),
            ),
            'parts' => array(
                'display' => array('дисплей', 'диспей', 'дисплэй', 'диспл', 'дисп', 'lbcgktq', 'lbcgk', 'экран', 'єкран', '\'rhfy', 'lcd', 'лсд', 'лцд', 'ktl', 'модуль', 'vjlekm'),
                'battery' => array('акб', 'fr,', 'аккумулятор', 'аккум', 'батарея', 'батарейка', 'акум', 'frrevekznjh', 'frreve', ',fnfhtz'),
                'back_cover' => array('задняя крышка', 'крышка', 'задняя', 'зад крышка', 'pflyzz rhsirn', 'rhsirn', 'pflyzz', 'корпус', 'корп', 'rjhgec', 'rjhg', 'рамка', 'рама', 'hfvrf', 'hfvf'),
                'flex' => array('шлейф', 'шлеф', 'iktqa', 'ikta', 'межплатный', 'межплат', 'vt;gkfnysq', 'vt;gkfn', 'флекс', 'aktrc'),
                'charging' => array('шлейф зарядки', 'зарядка', 'порт зарядки', 'iktqa pfhzlrb', 'pfhzlrf', 'плата зарядки', 'charging port', 'gkfnf pfhzlrb', 'разъем', 'разьем', 'hfp]tv'),
                'glass' => array('стекло', 'стекл', 'cntrkj', 'тачскрин', 'тачскрін', 'тач', 'nfxcrhby', 'nfx', 'переклейка', 'gthtrktrrf'),
                'speaker' => array('динамик', 'динамік', 'дин', 'lbyfvbr', 'lby', 'динамики', 'спикер', 'speaker', 'lbyfvbrb', 'cgbrhh'),
            ),
        );
    }

    /**
     * Expand search term
     */
    private function expand_search_term($search_term) {
        $search_term = mb_strtolower(trim($search_term), 'UTF-8');
        $words = preg_split('/\s+/', $search_term);
        $expanded_terms = array();
        
        foreach ($words as $word) {
            if (empty($word)) continue;
            
            $word_length = mb_strlen($word, 'UTF-8');
            $found = false;
            
            // Check exact match first
            foreach ($this->search_combinations as $category => $subcategories) {
                foreach ($subcategories as $canonical => $variants) {
                    if (in_array($word, $variants)) {
                        $expanded_terms = array_merge($expanded_terms, $variants);
                        $found = true;
                        break 2;
                    }
                }
            }
            
            // If no exact match and word is 2-4 characters, check partial matches
            if (!$found && $word_length >= 2 && $word_length <= 4) {
                foreach ($this->search_combinations as $category => $subcategories) {
                    foreach ($subcategories as $canonical => $variants) {
                        foreach ($variants as $variant) {
                            // Check if variant starts with the word (first 2-4 chars)
                            if (mb_strlen($variant, 'UTF-8') >= $word_length) {
                                $variant_prefix = mb_substr($variant, 0, $word_length, 'UTF-8');
                                if ($variant_prefix === $word) {
                                    $expanded_terms = array_merge($expanded_terms, $variants);
                                    $found = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
            
            if (!$found) {
                $expanded_terms[] = $word;
            }
        }
        
        return array_unique($expanded_terms);
    }

    public function clear_cache_on_product_update($post_id) {
        $query_handler = MKX_Search_Query::instance();
        $query_handler->clear_search_cache();
    }

    public function clear_cache_on_category_update($term_id) {
        $query_handler = MKX_Search_Query::instance();
        $query_handler->clear_search_cache();
    }

    public function display_search_categories() {
        if (!is_search() || !isset($_GET['s'])) {
            return;
        }

        $search_term = sanitize_text_field(wp_unslash($_GET['s']));

        if (empty($search_term)) {
            return;
        }

        $query_handler = MKX_Search_Query::instance();
        $categories = $query_handler->get_search_categories($search_term);

        if (empty($categories)) {
            return;
        }

        $active_category = isset($_GET['product_cat']) ? sanitize_text_field(wp_unslash($_GET['product_cat'])) : '';

        $this->render_search_categories($categories, $search_term, $active_category);
    }

    private function render_search_categories($categories, $search_term, $active_category) {
        ?>
        <div class="mkx-search-categories">
            <div class="mkx-search-categories__header">
                <h4 class="mkx-search-categories__title">
                    <?php 
                    printf(
                        esc_html__('Найдено по запросу: %s', 'mkx-live-search'),
                        '<strong>' . esc_html($search_term) . '</strong>'
                    );
                    ?>
                </h4>
            </div>

            <div class="mkx-search-categories__tags">
                <button 
                    class="mkx-search-tag <?php echo empty($active_category) ? 'mkx-search-tag--active' : ''; ?>"
                    data-category=""
                    type="button"
                    aria-pressed="<?php echo empty($active_category) ? 'true' : 'false'; ?>">
                    <?php echo esc_html__('Показать все', 'mkx-live-search'); ?>
                </button>

                <?php foreach ($categories as $category) : 
                    $is_active = $active_category === $category['slug'];
                ?>
                    <button 
                        class="mkx-search-tag <?php echo $is_active ? 'mkx-search-tag--active' : ''; ?>"
                        data-category="<?php echo esc_attr($category['slug']); ?>"
                        type="button"
                        aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
                        <?php echo esc_html($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function modify_search_query($query) {
        if (!is_admin() && $query->is_main_query() && $query->is_search() && isset($query->query_vars['s'])) {
            $search_term = $query->query_vars['s'];

            if (empty($search_term) || strlen($search_term) < 2) {
                return $query;
            }

            $query->set('post_type', 'product');
            $query->set('post_status', 'publish');

            if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
                $category = sanitize_text_field(wp_unslash($_GET['product_cat']));
                
                $tax_query = $query->get('tax_query');
                if (!is_array($tax_query)) {
                    $tax_query = array();
                }

                $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category,
                );

                $query->set('tax_query', $tax_query);
            }

            add_filter('posts_search', array($this, 'extend_search_query'), 10, 2);
        }

        return $query;
    }

    public function extend_search_query($search, $query) {
        global $wpdb;

        if (empty($search) || !$query->is_search() || !$query->is_main_query()) {
            return $search;
        }

        $search_term = $query->query_vars['s'];
        $expanded_terms = $this->expand_search_term($search_term);
        
        $search_conditions = array();
        $prepare_values = array();

        foreach ($expanded_terms as $term) {
            $like = '%' . $wpdb->esc_like($term) . '%';
            
            $search_conditions[] = "
                {$wpdb->posts}.post_title LIKE %s
                OR {$wpdb->posts}.post_content LIKE %s
            ";
            
            $prepare_values[] = $like;
            $prepare_values[] = $like;
        }

        $conditions_sql = implode(' OR ', $search_conditions);

        // Add original search for SKU, categories, and attributes
        $original_like = '%' . $wpdb->esc_like($search_term) . '%';

        $search = " AND (
            ({$conditions_sql})
            OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta}
                WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
                AND {$wpdb->postmeta}.meta_key = '_sku'
                AND {$wpdb->postmeta}.meta_value LIKE %s
            )
            OR EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tr.object_id = {$wpdb->posts}.ID
                AND tt.taxonomy IN ('product_cat', 'product_brand', 'pa_brand')
                AND t.name LIKE %s
            )
            OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm_attr
                WHERE pm_attr.post_id = {$wpdb->posts}.ID
                AND pm_attr.meta_key LIKE 'attribute_%%'
                AND pm_attr.meta_value LIKE %s
            )
        )";

        $prepare_values[] = $original_like;
        $prepare_values[] = $original_like;
        $prepare_values[] = $original_like;

        $search = $wpdb->prepare($search, $prepare_values);

        remove_filter('posts_search', array($this, 'extend_search_query'), 10);

        return $search;
    }
}