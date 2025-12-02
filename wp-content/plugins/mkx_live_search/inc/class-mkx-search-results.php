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

    $search_lower = mb_strtolower($search_term, 'UTF-8');
    
    // Определяем ТИП детали
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

    // Определяем БРЕНД
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

    // Соответствие категорий брендам и типам деталей
    $category_mapping = array(
        // Дисплеи
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
        
        // АКБ
        'akb-iphone' => array('brand' => 'iphone', 'type' => 'battery'),
        'akb-huawei-honor' => array('brand' => 'huawei', 'type' => 'battery'),
        'akb-dlya-nokia' => array('brand' => 'nokia', 'type' => 'battery'),
        'akb-dlya-samsung' => array('brand' => 'samsung', 'type' => 'battery'),
        'akb-dlya-xiaomi-redmi' => array('brand' => 'xiaomi', 'type' => 'battery'),
        
        // Задние крышки
        'zadnyaya-kryshka-ramka-korpus-dlya-iphone' => array('brand' => 'iphone', 'type' => 'back_cover'),
        'zadnyaya-kryshka-ramka-korpus' => array('brand' => null, 'type' => 'back_cover'),
        
        // Стекла
        'steklo' => array('brand' => null, 'type' => 'glass'),
        'tachskrin' => array('brand' => null, 'type' => 'glass'),
        
        // Аккумуляторы (общая категория)
        'akkumulyatory' => array('brand' => null, 'type' => 'battery'),
    );

    $active_category = isset($_GET['product_cat']) ? sanitize_text_field(wp_unslash($_GET['product_cat'])) : '';

    // ОТЛАДКА
    if (current_user_can('manage_options')) {
        echo '<div style="background:#fff3cd;border:3px solid #ff9800;padding:20px;margin:20px 0;font-family:monospace;font-size:13px;">';
        echo '<h3 style="margin:0 0 15px 0;color:#ff9800;">🐛 ОТЛАДКА ПОИСКА</h3>';
        echo '<strong>Запрос:</strong> ' . esc_html($search_term) . '<br>';
        echo '<strong>Обнаружен бренд:</strong> ' . ($detected_brand ? '<code style="background:#007bff;color:white;padding:2px 8px;border-radius:3px;">' . $detected_brand . '</code>' : '<em>не определен</em>') . '<br>';
        echo '<strong>Обнаружен тип детали:</strong> ' . ($detected_part_type ? '<code style="background:#28a745;color:white;padding:2px 8px;border-radius:3px;">' . $detected_part_type . '</code>' : '<em>не определен</em>') . '<br>';
        echo '<strong>Всего категорий:</strong> ' . count($categories) . '<br>';
        echo '<strong>Активная категория из GET:</strong> ' . ($active_category ? esc_html($active_category) : '<em>не выбрана</em>') . '<br><br>';
        
        echo '<table style="border-collapse:collapse;width:100%;margin:10px 0;font-size:12px;">';
        echo '<tr style="background:#f0f0f0;"><th style="border:1px solid #ddd;padding:6px;">№</th><th style="border:1px solid #ddd;padding:6px;">Название</th><th style="border:1px solid #ddd;padding:6px;">Slug</th><th style="border:1px solid #ddd;padding:6px;">Приоритет</th></tr>';
    }

    if (empty($active_category)) {
        $best_category = null;
        $best_priority = -1;

        foreach ($categories as $i => $category) {
            $priority = 0;
            
            if (isset($category_mapping[$category['slug']])) {
                $cat_brand = $category_mapping[$category['slug']]['brand'];
                $cat_type = $category_mapping[$category['slug']]['type'];
                
                // Полное совпадение: и бренд, и тип детали
                if ($detected_brand && $detected_part_type && 
                    $cat_brand === $detected_brand && $cat_type === $detected_part_type) {
                    $priority = 100;
                }
                // Совпадает только тип детали
                elseif ($detected_part_type && $cat_type === $detected_part_type) {
                    $priority = 50;
                }
                // Совпадает только бренд
                elseif ($detected_brand && $cat_brand === $detected_brand) {
                    $priority = 30;
                }
                // Общая категория без бренда (например, "Аккумуляторы")
                elseif ($cat_brand === null && $detected_part_type && $cat_type === $detected_part_type) {
                    $priority = 20;
                }
            }

            // ОТЛАДКА - вывод строки таблицы
            if (current_user_can('manage_options')) {
                $bg = '';
                if ($priority >= 100) {
                    $bg = 'background:#d4edda;font-weight:bold;';
                } elseif ($priority >= 50) {
                    $bg = 'background:#fff3cd;';
                } elseif ($priority > 0) {
                    $bg = 'background:#f8f9fa;';
                }
                echo '<tr style="' . $bg . '"><td style="border:1px solid #ddd;padding:6px;text-align:center;">' . ($i + 1) . '</td><td style="border:1px solid #ddd;padding:6px;">' . esc_html($category['name']) . '</td><td style="border:1px solid #ddd;padding:6px;"><code style="font-size:11px;">' . esc_html($category['slug']) . '</code></td><td style="border:1px solid #ddd;padding:6px;text-align:center;font-weight:bold;' . ($priority >= 100 ? 'color:#28a745;' : '') . '">' . $priority . '</td></tr>';
            }

            if ($priority > $best_priority) {
                $best_priority = $priority;
                $best_category = $category['slug'];
            }
        }

        if (current_user_can('manage_options')) {
            echo '</table>';
            echo '<hr style="margin:15px 0;border:none;border-top:2px solid #ff9800;">';
            echo '<strong style="color:#28a745;font-size:14px;">✅ ВЫБРАНА КАТЕГОРИЯ:</strong> ';
            if ($best_category) {
                echo '<code style="background:#28a745;color:white;padding:4px 10px;border-radius:3px;font-size:13px;">' . esc_html($best_category) . '</code> <span style="color:#666;">(приоритет: ' . $best_priority . ')</span>';
            } else {
                echo '<span style="color:#dc3545;font-weight:bold;">НЕ НАЙДЕНА!</span>';
            }
            echo '<br><strong>Будет редирект:</strong> ' . (!isset($_GET['product_cat']) ? '<span style="color:#28a745;">ДА ✓</span>' : '<span style="color:#666;">НЕТ (уже есть product_cat)</span>');
            echo '</div>';
        }

        if ($best_category !== null && !isset($_GET['product_cat'])) {
            $active_category = $best_category;
            
            $redirect_url = add_query_arg(
                array(
                    's' => $search_term,
                    'post_type' => 'product',
                    'product_cat' => $active_category,
                ),
                home_url('/')
            );
            
            wp_safe_redirect($redirect_url);
            exit;
        }
    } else {
        if (current_user_can('manage_options')) {
            echo '</table>';
            echo '<hr style="margin:15px 0;border:none;border-top:2px solid #ff9800;">';
            echo '<strong style="color:#007bff;">ℹ️ Категория уже выбрана:</strong> <code>' . esc_html($active_category) . '</code>';
            echo '</div>';
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