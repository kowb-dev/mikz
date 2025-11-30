<?php
/**
 * Search Query Handler with Enhanced Search Combinations
 *
 * @package MKX_Live_Search
 * @version 1.0.2
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

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_search_combinations();
    }

    /**
     * Initialize search combinations from JS file
     */
    private function init_search_combinations() {
        $this->search_combinations = array(
            // БРЕНДЫ
            'brands' => array(
                'apple' => array('apple', 'аппле', 'апл', 'эпл', 'эппл', 'апель', 'фззду'),
                'iphone' => array('iphone', 'айфон', 'ифон', 'іфон', 'b`jyt', 'b`jy', 'fqa', 'fqaj', 'fqajy', 'айфoн'),
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
                'tecno' => array('te', 'tecn', 'tecn', 'tecno', 'тек', 'текн', 'текно', 'тэк', 'тэкн', 'тэкно', 'ntryj'),
            ),
            // ТИПЫ ЗАПЧАСТЕЙ
            'parts' => array(
                'display' => array('дисплей', 'диспей', 'дисплэй', 'диспл', 'дисп', 'lbcgktq', 'lbcgk', 'экран', 'єкран', '\'rhfy', 'lcd', 'лсд', 'лцд', 'ktl', 'dis', 'disp', 'displ', 'displa', 'display', 'модуль', 'vjlekm'),
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
     * Expand search term with combinations
     */
    private function expand_search_term($search_term) {
        $search_term = mb_strtolower(trim($search_term), 'UTF-8');
        $words = preg_split('/\s+/', $search_term);
        $expanded_terms = array($search_term); // Always include original
        
        foreach ($words as $word) {
            if (empty($word)) continue;
            
            $word_length = mb_strlen($word, 'UTF-8');
            $found_exact = false;
            
            // Check exact match first
            foreach ($this->search_combinations as $category => $subcategories) {
                foreach ($subcategories as $canonical => $variants) {
                    if (in_array($word, $variants)) {
                        // Add canonical term
                        $expanded_terms[] = $canonical;
                        // Add all variants
                        $expanded_terms = array_merge($expanded_terms, $variants);
                        $found_exact = true;
                        break 2;
                    }
                }
            }
            
            // If no exact match and word is 2-4 characters, check partial matches
            if (!$found_exact && $word_length >= 2 && $word_length <= 4) {
                foreach ($this->search_combinations as $category => $subcategories) {
                    foreach ($subcategories as $canonical => $variants) {
                        foreach ($variants as $variant) {
                            // Check if variant starts with the word (first 2-4 chars)
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
        
        return array_unique($expanded_terms);
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
        
        // Expand search term
        $expanded_terms = $this->expand_search_term($search_term);
        
        $product_ids = $this->get_product_ids_by_search($expanded_terms, $args);

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
     * Get product IDs by expanded search terms
     */
    private function get_product_ids_by_search($search_terms, $args) {
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
            
            // Add 5 placeholders for each term
            $prepare_values[] = $like;
            $prepare_values[] = $like;
            $prepare_values[] = $like;
            $prepare_values[] = $like;
            $prepare_values[] = $like;
        }

        $conditions_sql = implode(' OR ', $like_conditions);

        // Приоритетные slugs для категорий дисплеев
        $display_slugs = array(
            'displei-iphone',
            'displei-huawei-honor',
            'displei-dlya-infinix',
            'displei-oppo',
            'displei-realme',
            'displei-dlya-samsung',
            'displei-tecno',
            'displei-vivo',
            'displei-xiaomi-redmi',
            'displei-ekrany-lcd',
        );
        
        $display_slugs_str = "'" . implode("','", array_map('esc_sql', $display_slugs)) . "'";

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
                       WHEN t_cat.slug IN ({$display_slugs_str}) THEN 0
                       ELSE 1
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
            AND ({$conditions_sql})
            ORDER BY category_priority ASC, relevance ASC, p.post_title ASC
            LIMIT %d
        ";

        // Prepare values for relevance calculation (first term only)
        $first_term_like = '%' . $wpdb->esc_like($search_terms[0]) . '%';
        $relevance_values = array(
            $first_term_like,
            $first_term_like,
            $first_term_like,
            $first_term_like,
        );

        // Combine all prepare values
        $all_values = array_merge($relevance_values, $prepare_values);
        $all_values[] = absint($args['limit']) * 3; // Limit

        $results = $wpdb->get_col(
            $wpdb->prepare($sql, $all_values)
        );

        return array_map('absint', $results);
    }

    /**
     * Get categories from search results with Display priority
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

        // Expand search term
        $expanded_terms = $this->expand_search_term($search_term);
        
        $product_ids = $this->get_product_ids_by_search($expanded_terms, array('limit' => 100));

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

                $categories[] = array(
                    'id' => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                );

                $seen_cats[] = $cat->term_id;
            }
        }
        
        // Приоритетные slugs для категорий дисплеев (всегда показываем первыми)
        $display_priority_slugs = array(
            'displei-iphone',
            'displei-huawei-honor',
            'displei-dlya-infinix',
            'displei-oppo',
            'displei-realme',
            'displei-dlya-samsung',
            'displei-tecno',
            'displei-vivo',
            'displei-xiaomi-redmi',
            'displei-ekrany-lcd',
        );

        // Сортировка: сначала дисплеи (в порядке приоритета), затем остальное по алфавиту
        usort($categories, function($a, $b) use ($display_priority_slugs) {
            $a_is_display = in_array($a['slug'], $display_priority_slugs);
            $b_is_display = in_array($b['slug'], $display_priority_slugs);

            // Если обе категории - дисплеи, сортируем по приоритету
            if ($a_is_display && $b_is_display) {
                $a_priority = array_search($a['slug'], $display_priority_slugs);
                $b_priority = array_search($b['slug'], $display_priority_slugs);
                return $a_priority - $b_priority;
            }

            // Дисплеи всегда первые
            if ($a_is_display && !$b_is_display) {
                return -1;
            } elseif (!$a_is_display && $b_is_display) {
                return 1;
            }

            // Остальные категории сортируем по алфавиту
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