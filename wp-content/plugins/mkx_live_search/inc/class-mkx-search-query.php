<?php
/**
 * Search Query Handler
 *
 * @package MKX_Live_Search
 * @version 1.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * MKX Search Query Class
 *
 * @class MKX_Search_Query
 */
class MKX_Search_Query {

    /**
     * Instance
     *
     * @var MKX_Search_Query
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return MKX_Search_Query
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
        // Empty constructor
    }

    /**
     * Search products
     *
     * @param string $search_term Search term
     * @param array  $args Additional arguments
     * @return array
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
        $search_term = sanitize_text_field($search_term);

        $product_ids = $this->get_product_ids_by_search($search_term, $args);

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
     * Get product IDs by search term
     *
     * @param string $search_term Search term
     * @param array  $args Arguments
     * @return array
     */
    private function get_product_ids_by_search($search_term, $args) {
        global $wpdb;

        $search_term_like = '%' . $wpdb->esc_like($search_term) . '%';
        
        $sql = "
            SELECT DISTINCT p.ID, 
                   CASE 
                       WHEN p.post_title LIKE %s THEN 1
                       WHEN pm_sku.meta_value LIKE %s THEN 2
                       WHEN t.name LIKE %s THEN 3
                       WHEN pm_attr.meta_value LIKE %s THEN 4
                       ELSE 5
                   END as relevance
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_sku ON (p.ID = pm_sku.post_id AND pm_sku.meta_key = '_sku')
            LEFT JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            LEFT JOIN {$wpdb->term_taxonomy} tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy IN ('product_cat', 'product_brand'))
            LEFT JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
            LEFT JOIN {$wpdb->postmeta} pm_attr ON (p.ID = pm_attr.post_id AND pm_attr.meta_key LIKE 'attribute_%')
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND (
                p.post_title LIKE %s
                OR pm_sku.meta_value LIKE %s
                OR t.name LIKE %s
                OR pm_attr.meta_value LIKE %s
            )
            ORDER BY relevance ASC, p.post_title ASC
            LIMIT %d
        ";

        $limit = absint($args['limit']) * 2;

        $results = $wpdb->get_col(
            $wpdb->prepare(
                $sql,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $limit
            )
        );

        return array_map('absint', $results);
    }

    /**
     * Get categories from search results
     *
     * @param string $search_term Search term
     * @return array
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

        global $wpdb;

        $search_term_like = '%' . $wpdb->esc_like($search_term) . '%';

        $sql = "
            SELECT DISTINCT t.term_id, t.name, t.slug
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
            WHERE tt.taxonomy = 'product_cat'
            AND p.post_type = 'product'
            AND p.post_status = 'publish'
            AND (
                t.name LIKE %s
                OR t.slug LIKE %s
                OR p.post_title LIKE %s
                OR EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm
                    WHERE pm.post_id = p.ID
                    AND pm.meta_key = '_sku'
                    AND pm.meta_value LIKE %s
                )
            )
            AND tt.count > 0
            ORDER BY t.name ASC
            LIMIT 20
        ";

        $results = $wpdb->get_results(
            $wpdb->prepare(
                $sql,
                $search_term_like,
                $search_term_like,
                $search_term_like,
                $search_term_like
            )
        );

        $categories = array();
        $seen_cats = array();

        if (!empty($results)) {
            foreach ($results as $result) {
                if (in_array($result->term_id, $seen_cats)) {
                    continue;
                }

                $categories[] = array(
                    'id' => (int) $result->term_id,
                    'name' => $result->name,
                    'slug' => $result->slug,
                );

                $seen_cats[] = $result->term_id;
            }
        }

        set_transient($cache_key, $categories, HOUR_IN_SECONDS);

        return $categories;
    }

    /**
     * Format product for response
     *
     * @param WP_Post $product Product post object
     * @return array|null
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
     *
     * @param WC_Product $product Product object
     * @return string
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
     *
     * @return void
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