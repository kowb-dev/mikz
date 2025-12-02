<?php
/**
 * Search Categories Template
 *
 * @package MKX_Live_Search
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mkx-search-categories">
    <div class="mkx-search-categories__header">
        <h4 class="mkx-search-categories__title">
            <?php echo esc_html__('Поиск:', 'mkx-live-search') . ' ' . esc_html($search_term); ?>
        </h4>
    </div>

    <div class="mkx-search-categories__tags">
        <button 
            class="mkx-search-tag <?php echo empty($active_category) ? 'mkx-search-tag--active' : ''; ?>"
            data-category=""
            type="button">
            <?php echo esc_html__('Все товары', 'mkx-live-search') . ' (' . esc_html($GLOBALS['wp_query']->found_posts) . ')'; ?>
        </button>

        <?php foreach ($categories as $category) : ?>
            <button 
                class="mkx-search-tag <?php echo $active_category === $category['slug'] ? 'mkx-search-tag--active' : ''; ?>"
                data-category="<?php echo esc_attr($category['slug']); ?>"
                type="button">
                <?php echo esc_html($category['name']); ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>
