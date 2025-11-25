<?php
/**
 * Search Results Handler
 *
 * @package MKX_Live_Search
 * @version 1.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * MKX Search Results Class
 *
 * @class MKX_Search_Results
 */
class MKX_Search_Results {

    /**
     * Instance
     *
     * @var MKX_Search_Results
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return MKX_Search_Results
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
        add_action('woocommerce_before_shop_loop', array($this, 'display_search_categories'), 5);
        add_filter('pre_get_posts', array($this, 'modify_search_query'));
        add_action('save_post_product', array($this, 'clear_cache_on_product_update'));
        add_action('edited_product_cat', array($this, 'clear_cache_on_category_update'));
    }

    /**
     * Clear cache on product update
     *
     * @param int $post_id Post ID
     * @return void
     */
    public function clear_cache_on_product_update($post_id) {
        $query_handler = MKX_Search_Query::instance();
        $query_handler->clear_search_cache();
    }

    /**
     * Clear cache on category update
     *
     * @param int $term_id Term ID
     * @return void
     */
    public function clear_cache_on_category_update($term_id) {
        $query_handler = MKX_Search_Query::instance();
        $query_handler->clear_search_cache();
    }

    /**
     * Display search categories
     *
     * @return void
     */
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

    /**
     * Render search categories inline
     *
     * @param array  $categories Categories array
     * @param string $search_term Search term
     * @param string $active_category Active category slug
     * @return void
     */
    private function render_search_categories($categories, $search_term, $active_category) {
        ?>
        <div class="mkx-search-categories">
            <div class="mkx-search-categories__header">
                <h4 class="mkx-search-categories__title">
                    <?php 
                    printf(
                        /* translators: %s: search term */
                        esc_html__('Search: %s', 'mkx-live-search'),
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

    /**
     * Modify search query
     *
     * @param WP_Query $query Query object
     * @return WP_Query
     */
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

    /**
     * Extend search query
     *
     * @param string   $search Search SQL
     * @param WP_Query $query Query object
     * @return string
     */
    public function extend_search_query($search, $query) {
        global $wpdb;

        if (empty($search) || !$query->is_search() || !$query->is_main_query()) {
            return $search;
        }

        $search_term = $query->query_vars['s'];
        $search_term_like = '%' . $wpdb->esc_like($search_term) . '%';

        $search = " AND (
            {$wpdb->posts}.post_title LIKE %s
            OR {$wpdb->posts}.post_content LIKE %s
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
                AND tt.taxonomy IN ('product_cat', 'product_brand')
                AND t.name LIKE %s
            )
            OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm_attr
                WHERE pm_attr.post_id = {$wpdb->posts}.ID
                AND pm_attr.meta_key LIKE 'attribute_%%'
                AND pm_attr.meta_value LIKE %s
            )
        )";

        $search = $wpdb->prepare(
            $search,
            $search_term_like,
            $search_term_like,
            $search_term_like,
            $search_term_like,
            $search_term_like
        );

        remove_filter('posts_search', array($this, 'extend_search_query'), 10);

        return $search;
    }
}