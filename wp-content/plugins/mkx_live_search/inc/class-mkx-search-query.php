<?php
/**
 * Search Query Handler with Enhanced Search Combinations
 *
 * @package MKX_Live_Search
 * @version 1.0.4
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKX_Search_Query {

    private static $instance = null;
    
    /**
     * Search combinations mapping
     */
    private $search_combinations = array();
    
    /**
     * Category priority mapping
     */
    private $category_priority = array();

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_search_combinations();
        $this->init_category_priority();
    }

    /**
     * Initialize category priority based on part type
     */
    private function init_category_priority() {
        // Приоритет категорий по типам запчастей для каждого бренда
        $this->category_priority = array(
            'iphone' => array(
                'display' => array('displei-iphone'),
                'battery' => array('akb-iphone'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-iphone'),
                'charging' => array('shleif-zaryadki-iphone'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-iphone'),
                'speaker' => array('dinamiki-iphone'),
                'flex' => array('shleif-zaryadki-iphone'), // межплатные шлейфы для iPhone
            ),
            'samsung' => array(
                'display' => array('displei-dlya-samsung'),
                'battery' => array('akb-dlya-samsung'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-samsung'),
                'flex' => array('mezhplatnyi-shleif-dlya-samsung'),
                'charging' => array('plata-zaryadki-samsung'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-samsung'),
            ),
            'xiaomi' => array(
                'display' => array('displei-xiaomi-redmi'),
                'battery' => array('akb-dlya-xiaomi-redmi'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-xiaomi-redmi'),
                'flex' => array('mezhplatnyi-shleif-xiaomi-redmi'),
                'charging' => array('plata-zaryadki-xiaomi-redmi'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-xiaomi'),
            ),
            'redmi' => array(
                'display' => array('displei-xiaomi-redmi'),
                'battery' => array('akb-dlya-xiaomi-redmi'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-xiaomi-redmi'),
                'flex' => array('mezhplatnyi-shleif-xiaomi-redmi'),
                'charging' => array('plata-zaryadki-xiaomi-redmi'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-xiaomi'),
            ),
            'huawei' => array(
                'display' => array('displei-huawei-honor'),
                'battery' => array('akb-huawei-honor'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-huawei-honor'),
                'flex' => array('mezhplatnyi-shleif-huawei-honor'),
                'charging' => array('plata-zaryadki-huawei-honor'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-huawei-honor'),
            ),
            'honor' => array(
                'display' => array('displei-huawei-honor'),
                'battery' => array('akb-huawei-honor'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-huawei-honor'),
                'flex' => array('mezhplatnyi-shleif-huawei-honor'),
                'charging' => array('plata-zaryadki-huawei-honor'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-huawei-honor'),
            ),
            'infinix' => array(
                'display' => array('displei-dlya-infinix'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-infinix'),
            ),
            'oppo' => array(
                'display' => array('displei-oppo'),
                'battery' => array('oppo-akkumulyatory'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-oppo'),
            ),
            'realme' => array(
                'display' => array('displei-realme'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-realme'),
            ),
            'tecno' => array(
                'display' => array('displei-tecno'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-tecno'),
            ),
            'vivo' => array(
                'display' => array('displei-vivo'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-vivo'),
            ),
            'nokia' => array(
                'battery' => array('akb-dlya-nokia'),
            ),
            'apple' => array(
                'display' => array('displei-iphone'),
                'battery' => array('akb-iphone', 'akb-ipad'),
                'back_cover' => array('zadnyaya-kryshka-ramka-korpus-iphone'),
                'charging' => array('shleif-zaryadki-iphone'),
                'glass' => array('steklo-tachskrin-dlya-perekleiki-iphone'),
                'speaker' => array('dinamiki-iphone'),
                'flex' => array('shleif-zaryadki-iphone'),
            ),
        );
    }

    /**
     * Initialize search combinations from JS file
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
                'display' => array('дисплей', 'диспей', 'дисплюй', 'диспл', 'дисп', 'lbcgktq', 'lbcgk', 'экран', 'єкран', '\'rhfy', 'lcd', 'лсд', 'лцд', 'ktl', 'dis', 'disp', 'displ', 'displa', 'display', 'модуль', 'vjlekm', 'дисплеи', 'экраны'),
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
     * Get priority category slugs based on search intent
     */
    private function get_priority_category_slugs($intent) {
        $priority_slugs = array();
        
        if ($intent['is_brand_only'] && !empty($intent['brand'])) {
            // Только бренд → показываем дисплеи
            if (isset($this->category_priority[$intent['brand']]['display'])) {
                $priority_slugs = $this->category_priority[$intent['brand']]['display'];
            }
        } elseif ($intent['is_brand_and_part'] && !empty($intent['brand']) && !empty($intent['part'])) {
            // Бренд + запчасть → показываем конкретную запчасть
            if (isset($this->category_priority[$intent['brand']][$intent['part']])) {
                $priority_slugs = $this->category_priority[$intent['brand']][$intent['part']];
            }
        }
        
        return $priority_slugs;
    }

    /**
     * Expand search term with combinations
     */
    private function expand_search_term($search_term) {
        $search_term = mb_strtolower(trim($search_term), 'UTF-8');
        $words = preg_split('/\s+/', $search_term);
        $expanded_terms = array($search_term);
        
        foreach ($words as $word) {
            if (empty($word)) continue;
            
            $word_length = mb_strlen($word, 'UTF-8');
            $found_exact = false;
            
            foreach ($this->search_combinations as $category => $subcategories) {
                foreach ($subcategories as $canonical => $variants) {
                    if (in_array($word, $variants)) {
                        $expanded_terms[] = $canonical;
                        $expanded_terms = array_merge($expanded_terms, $variants);
                        $found_exact = true;
                        break 2;
                    }
                }
            }
            
            if (!$found_exact && $word_length >= 2 && $word_length <= 5) {
                foreach ($this->search_combinations as $category => $subcategories) {
                    foreach ($subcategories as $canonical => $variants) {
                        foreach ($variants as $variant) {
                            if (mb_strlen($variant, 'UTF-8') >= $word_length) {
                                $variant_prefix = mb_substr($variant, 0, $word_length, 'UTF-8');
                                if ($variant_prefix === $word) {
                                    $expanded_terms[] = $canonical;
                                    $expanded_terms = array_merge($expanded_terms, $variants);
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $expanded_terms = array_values(array_unique($expanded_terms));

        // Limit the number of expanded terms to keep queries fast and reliable
        if (count($expanded_terms) > 12) {
            $expanded_terms = array_slice($expanded_terms, 0, 12);
        }

        return $expanded_terms;
    }

    /**
     * Search products with expanded terms
     */
    public function search_products($search_term, $args = array()) {
        if (empty($search_term) || strlen($search_term) < 2) {
            return array();
        }

        $defaults = array(
            'limit' => 10,
            'category' => '',
            'orderby' => 'relevance',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);
        
        $expanded_terms = $this->expand_search_term($search_term);
        $intent = $this->detect_search_intent($search_term);
        
        $product_ids = $this->get_product_ids_by_search($expanded_terms, $args, $intent);

        if (empty($product_ids)) {
            return array();
        }

        $query_args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => absint($args['limit']),
            'post__in' => $product_ids,
            'orderby' => 'post__in',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );

        if (!empty($args['category'])) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => sanitize_title($args['category']),
                ),
            );
        }

        $query = new WP_Query($query_args);

        return $query->posts;
    }

    /**
     * Get product IDs by expanded search terms with intent-based priority
     */
    private function get_product_ids_by_search($search_terms, $args, $intent) {
        global $wpdb;

        if (!is_array($search_terms)) {
            $search_terms = array($search_terms);
        }

        $like_conditions = array();
        $prepare_values = array();

        foreach ($search_terms as $term) {
            $like = '%' . $wpdb->esc_like($term) . '%';
            
            $like_conditions[] = "
                (p.post_title LIKE %s 
                OR pm_sku.meta_value LIKE %s 
                OR t.name LIKE %s 
                OR pm_attr.meta_value LIKE %s
                OR p.post_content LIKE %s)
            ";
            
            $prepare_values[] = $like;
            $prepare_values[] = $like;
            $prepare_values[] = $like;
            $prepare_values[] = $like;
            $prepare_values[] = $like;
        }

        $conditions_sql = implode(' OR ', $like_conditions);

        // Получаем приоритетные категории на основе intent
        $priority_slugs = $this->get_priority_category_slugs($intent);
        
        $category_priority_sql = 'WHEN 1=1 THEN 1';
        if (!empty($priority_slugs)) {
            $priority_slugs_str = "'" . implode("','", array_map('esc_sql', $priority_slugs)) . "'";
            $category_priority_sql = "WHEN t_cat.slug IN ({$priority_slugs_str}) THEN 0 ELSE 1";
        }

        $sql = "
            SELECT DISTINCT p.ID, 
                   CASE 
                       WHEN p.post_title LIKE %s THEN 1
                       WHEN pm_sku.meta_value LIKE %s THEN 2
                       WHEN t.name LIKE %s THEN 3
                       WHEN pm_attr.meta_value LIKE %s THEN 4
                       ELSE 5
                   END as relevance,
                   CASE 
                       {$category_priority_sql}
                   END as category_priority
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_sku ON (p.ID = pm_sku.post_id AND pm_sku.meta_key = '_sku')
            LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy IN ('product_cat', 'product_brand', 'pa_brand'))
            LEFT JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
            LEFT JOIN {$wpdb->term_relationships} tr_cat ON (p.ID = tr_cat.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt_cat ON (tr_cat.term_taxonomy_id = tt_cat.term_taxonomy_id AND tt_cat.taxonomy = 'product_cat')
            LEFT JOIN {$wpdb->terms} t_cat ON (tt_cat.term_id = t_cat.term_id)
            LEFT JOIN {$wpdb->postmeta} pm_attr ON (p.ID = pm_attr.post_id AND pm_attr.meta_key LIKE 'attribute_%')
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND NOT EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr_misc
                INNER JOIN {$wpdb->term_taxonomy} tt_misc ON tr_misc.term_taxonomy_id = tt_misc.term_taxonomy_id
                WHERE tt_misc.taxonomy = 'product_cat' AND tt_misc.term_id = 253 AND tr_misc.object_id = p.ID
            )
            AND ({$conditions_sql})
            ORDER BY category_priority ASC, relevance ASC, p.post_title ASC
            LIMIT %d
        ";

        $first_term_like = '%' . $wpdb->esc_like($search_terms[0]) . '%';
        $relevance_values = array(
            $first_term_like,
            $first_term_like,
            $first_term_like,
            $first_term_like,
        );

        $all_values = array_merge($relevance_values, $prepare_values);
        $all_values[] = absint($args['limit']) * 3;

        $results = $wpdb->get_col(
            $wpdb->prepare($sql, $all_values)
        );

        if (empty($results) || !empty($wpdb->last_error)) {
            return array();
        }

        return array_map('absint', $results);
    }

    /**
     * Get categories from search results with intent-based priority
     */
    public function get_search_categories($search_term) {
        if (empty($search_term) || strlen($search_term) < 2) {
            return array();
        }

        $cache_key = 'mkx_search_cats_' . md5($search_term);
        $cached = get_transient($cache_key);

        if (false !== $cached && is_array($cached)) {
            return $cached;
        }

        $expanded_terms = $this->expand_search_term($search_term);
        $intent = $this->detect_search_intent($search_term);
        
        $product_ids = $this->get_product_ids_by_search($expanded_terms, array('limit' => 100), $intent);

        if (empty($product_ids)) {
            set_transient($cache_key, array(), HOUR_IN_SECONDS);
            return array();
        }

        $category_terms = wp_get_object_terms($product_ids, 'product_cat');
        
        $categories = array();
        $seen_cats = array();

        if (!is_wp_error($category_terms) && !empty($category_terms)) {
            foreach ($category_terms as $cat) {
                if (in_array($cat->term_id, $seen_cats)) {
                    continue;
                }
                
                if (253 === (int) $cat->term_id) {
                    continue;
                }

                $categories[] = array(
                    'id' => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                );

                $seen_cats[] = $cat->term_id;
            }
        }
        
        // Сортировка на основе intent
        $priority_slugs = $this->get_priority_category_slugs($intent);
        
        usort($categories, function($a, $b) use ($priority_slugs) {
            $a_is_priority = in_array($a['slug'], $priority_slugs);
            $b_is_priority = in_array($b['slug'], $priority_slugs);

            if ($a_is_priority && $b_is_priority) {
                $a_priority = array_search($a['slug'], $priority_slugs);
                $b_priority = array_search($b['slug'], $priority_slugs);
                return $a_priority - $b_priority;
            }

            if ($a_is_priority && !$b_is_priority) {
                return -1;
            } elseif (!$a_is_priority && $b_is_priority) {
                return 1;
            }

            return strcmp($a['name'], $b['name']);
        });

        set_transient($cache_key, $categories, HOUR_IN_SECONDS);

        return $categories;
    }

    /**
     * Format product for response
     */
    public function format_product_response($product) {
        $wc_product = wc_get_product($product->ID);

        if (!$wc_product) {
            return null;
        }

        $categories = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'names'));

        return array(
            'id' => $product->ID,
            'title' => get_the_title($product->ID),
            'url' => get_permalink($product->ID),
            'image' => $this->get_product_image($wc_product),
            'price' => $wc_product->get_price_html(),
            'sku' => $wc_product->get_sku(),
            'in_stock' => $wc_product->is_in_stock(),
            'categories' => $categories,
        );
    }

    /**
     * Get product image
     */
    private function get_product_image($product) {
        $image_id = $product->get_image_id();

        if ($image_id) {
            return wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail');
        }

        return wc_placeholder_img_src('woocommerce_thumbnail');
    }

    /**
     * Clear search cache
     */
    public function clear_search_cache() {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_mkx_search_cats_%' 
            OR option_name LIKE '_transient_timeout_mkx_search_cats_%'"
        );
    }
}