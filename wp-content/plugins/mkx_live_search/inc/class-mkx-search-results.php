<?php
/**
 * Search Results Handler with Enhanced Search
 *
 * @package MKX_Live_Search
 * @version 1.0.3
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
                'apple' => array('apple', 'аппле', 'апл', 'епл', 'еппл', 'апель', 'фззду', 'эпл', 'эппл'),
                'iphone' => array('iphone', 'айфон', 'ифон', 'іфон', 'b`jyt', 'b`jy', 'айфoн', 'айфoны', 'айфоны'),
                'ipad' => array('ipad', 'айпад', 'айпед', 'ипад', 'іпад', 'b`fl'),
                'samsung' => array('samsung', 'самсунг', 'самсунк', 'сансунг', 'самсун', 'самс', 'cfvceyu', 'cfvcey', 'самса'),
                'xiaomi' => array('xiaomi', 'сяоми', 'ксяоми', 'шаоми', 'ксиаоми', '[bfjvb', '[bfv', 'сяоми', 'ксиоми', 'сяо'),
                'redmi' => array('redmi', 'редми', 'htlvb', 'редми', 'реадми'),
                'huawei' => array('huawei', 'хуавей', 'хуавэй', 'хавей', 'хуавай', 'uefdtb', 'uefdt', 'хуавеи', 'хуаве'),
                'honor' => array('honor', 'хонор', 'хоннор', 'ujyjh', 'хонор', 'хоннор', 'хонр'),
                'nokia' => array('nokia', 'нокиа', 'нокия', 'yjrbf', 'нокія'),
                'oppo' => array('oppo', 'оппо', 'опо', 'jggj', 'опп'),
                'vivo' => array('vivo', 'виво', 'віво', 'dbdj', 'vivo'),
                'realme' => array('realme', 'реалме', 'риалми', 'риалме', 'реалми', 'htfkvt', 'риалм'),
                'infinix' => array('infinix', 'инфиникс', 'інфініх', 'byabyb[', 'инфиникс', 'инфиних'),
                'tecno' => array('tecno', 'текно', 'тскно', 'ntryj', 'текно'),
            ),
            'parts' => array(
                'display' => array('дисплей', 'диспей', 'дисплюй', 'диспл', 'дисп', 'lbcgktq', 'lbcgk', 'экран', 'єкран', '\'rhfy', 'lcd', 'лсд', 'лцд', 'ktl', 'модуль', 'vjlekm', 'дисплеи', 'экраны'),
                'battery' => array('акб', 'fr,', 'аккумулятор', 'аккум', 'батарея', 'батарейка', 'акум', 'frrevekznjh', 'frreve', ',fnfhtz', 'аккумуляторы', 'батареи'),
                'back_cover' => array('задняя крышка', 'крышка', 'задняя', 'зад крышка', 'pflyzz rhsirn', 'rhsirn', 'pflyzz', 'корпус', 'корп', 'rjhgec', 'rjhg', 'рамка', 'рама', 'hfvrf', 'hfvf', 'крышки', 'корпуса', 'рамки'),
                'flex' => array('шлейф', 'шлеф', 'iktqa', 'ikta', 'межплатный', 'межплат', 'vt;gkfnysq', 'vt;gkfn', 'флекс', 'aktrc', 'шлейфы', 'межплатные'),
                'charging' => array('шлейф зарядки', 'зарядка', 'порт зарядки', 'iktqa pfhzlrb', 'pfhzlrf', 'плата зарядки', 'charging port', 'gkfnf pfhzlrb', 'разъем', 'разьем', 'hfp]tv', 'платы', 'разъемы', 'порты'),
                'glass' => array('стекло', 'стекл', 'cntrkj', 'тачскрин', 'тачскрін', 'тач', 'nfxcrhby', 'nfx', 'переклейка', 'gthtrktrrf', 'стекла', 'тачскрины'),
                'speaker' => array('динамик', 'динамік', 'дин', 'lbyfvbr', 'lby', 'динамики', 'спикер', 'speaker', 'lbyfvbrb', 'cgbrhh', 'спикеры'),
            ),
        );
    }

    /**
     * Detect search intent (brand only vs brand + part)
     */
    private function detect_search_intent($search_term) {
        $search_term = mb_strtolower(trim($search_term), 'UTF-8');
        $words = preg_split('/\s+/', $search_term);
        
        $detected_brand = null;
        $detected_part = null;
        
        // Detect brand - проверяем каждое слово
        foreach ($words as $word) {
            if (empty($word) || mb_strlen($word, 'UTF-8') < 2) continue;
            
            foreach ($this->search_combinations['brands'] as $canonical_brand => $variants) {
                if (in_array($word, $variants)) {
                    $detected_brand = $canonical_brand;
                    break 2;
                }
                
                // Проверка начала слова для коротких запросов (2-4 символа)
                if (mb_strlen($word, 'UTF-8') >= 2 && mb_strlen($word, 'UTF-8') <= 4) {
                    foreach ($variants as $variant) {
                        if (mb_strlen($variant, 'UTF-8') >= mb_strlen($word, 'UTF-8')) {
                            $variant_prefix = mb_substr($variant, 0, mb_strlen($word, 'UTF-8'), 'UTF-8');
                            if ($variant_prefix === $word) {
                                $detected_brand = $canonical_brand;
                                break 3;
                            }
                        }
                    }
                }
            }
        }
        
        // Detect part type - проверяем каждое слово
        foreach ($words as $word) {
            if (empty($word) || mb_strlen($word, 'UTF-8') < 2) continue;
            
            foreach ($this->search_combinations['parts'] as $part_type => $variants) {
                if (in_array($word, $variants)) {
                    $detected_part = $part_type;
                    break 2;
                }
                
                // Проверка начала слова для коротких запросов (3-5 символов)
                if (mb_strlen($word, 'UTF-8') >= 3 && mb_strlen($word, 'UTF-8') <= 5) {
                    foreach ($variants as $variant) {
                        if (mb_strlen($variant, 'UTF-8') >= mb_strlen($word, 'UTF-8')) {
                            $variant_prefix = mb_substr($variant, 0, mb_strlen($word, 'UTF-8'), 'UTF-8');
                            if ($variant_prefix === $word) {
                                $detected_part = $part_type;
                                break 3;
                            }
                        }
                    }
                }
            }
        }
        
        return array(
            'brand' => $detected_brand,
            'part' => $detected_part,
            'is_brand_only' => !empty($detected_brand) && empty($detected_part),
            'is_brand_and_part' => !empty($detected_brand) && !empty($detected_part),
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
            
            foreach ($this->search_combinations as $category => $subcategories) {
                foreach ($subcategories as $canonical => $variants) {
                    if (in_array($word, $variants)) {
                        $expanded_terms = array_merge($expanded_terms, $variants);
                        $found = true;
                        break 2;
                    }
                }
            }
            
            if (!$found && $word_length >= 2 && $word_length <= 5) {
                foreach ($this->search_combinations as $category => $subcategories) {
                    foreach ($subcategories as $canonical => $variants) {
                        foreach ($variants as $variant) {
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

        // Если категория не выбрана, автоматически выбираем первую из списка
        if (empty($active_category) && !empty($categories)) {
            $first_category = $categories[0]['slug'];
            $active_category = $first_category;
            
            $redirect_url = add_query_arg(
                array(
                    's' => $search_term,
                    'post_type' => 'product',
                    'product_cat' => $active_category,
                ),
                home_url('/')
            );
            
            if (!isset($_GET['product_cat'])) {
                wp_safe_redirect($redirect_url);
                exit;
            }
        }

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