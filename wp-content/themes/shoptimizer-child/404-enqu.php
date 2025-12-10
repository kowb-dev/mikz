<?php
/**
 * 404 Page Assets Enqueue
 * Version: 1.0.0
 * Author: Костя Вебин
 * URI: https://kowb.ru
 * 
 * Add this to your child theme's functions.php or include it
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue 404 page styles and scripts
 */
function mkx_enqueue_404_assets() {
    // Only load on 404 pages
    if (!is_404()) {
        return;
    }

    $theme_version = wp_get_theme()->get('Version');
    $child_theme_dir = get_stylesheet_directory();
    $child_theme_uri = get_stylesheet_directory_uri();

    // Enqueue 404 page styles
    wp_enqueue_style(
        'mkx-404-page',
        $child_theme_uri . '/assets/css/404-page.css',
        array(),
        file_exists($child_theme_dir . '/assets/css/404-page.css') 
            ? filemtime($child_theme_dir . '/assets/css/404-page.css') 
            : $theme_version
    );

    // Enqueue Phosphor icons if not already loaded
    if (!wp_style_is('phosphor-icons-regular', 'enqueued')) {
        wp_enqueue_style(
            'phosphor-icons-regular',
            'https://unpkg.com/@phosphor-icons/web@2.1.2/src/regular/style.css',
            array(),
            '2.1.2'
        );
    }

    if (!wp_style_is('phosphor-icons-thin', 'enqueued')) {
        wp_enqueue_style(
            'phosphor-icons-thin',
            'https://unpkg.com/@phosphor-icons/web@2.1.2/src/thin/style.css',
            array(),
            '2.1.2'
        );
    }

    // Enqueue 404 page JavaScript
    wp_enqueue_script(
        'mkx-404-page',
        $child_theme_uri . '/assets/js/404-page.js',
        array(),
        file_exists($child_theme_dir . '/assets/js/404-page.js') 
            ? filemtime($child_theme_dir . '/assets/js/404-page.js') 
            : $theme_version,
        true
    );

    // Pass data to JavaScript
    wp_localize_script('mkx-404-page', 'mkx404Data', array(
        'homeUrl' => esc_url(home_url('/')),
        'shopUrl' => esc_url(home_url('/shop/')),
        'currentUrl' => esc_url($_SERVER['REQUEST_URI'] ?? ''),
        'searchPlaceholder' => __('Например: дисплей iPhone 13', 'shoptimizer-child'),
        'nonce' => wp_create_nonce('mkx_404_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'mkx_enqueue_404_assets');

/**
 * Add custom body class for 404 page
 */
function mkx_404_body_class($classes) {
    if (is_404()) {
        $classes[] = 'mkx-is-404';
        $classes[] = 'mkx-error-page';
    }
    return $classes;
}
add_filter('body_class', 'mkx_404_body_class');

/**
 * Log 404 errors for monitoring (optional)
 */
function mkx_log_404_errors() {
    if (!is_404()) {
        return;
    }

    // Only log if WP_DEBUG is enabled
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $request_uri = sanitize_text_field($_SERVER['REQUEST_URI'] ?? '');
    $referer = sanitize_text_field($_SERVER['HTTP_REFERER'] ?? 'direct');
    $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

    // Log to debug.log
    error_log(sprintf(
        '[404 Error] URI: %s | Referer: %s | User Agent: %s',
        $request_uri,
        $referer,
        $user_agent
    ));

    // Optional: Store in custom table for analytics
    // mkx_store_404_in_database($request_uri, $referer);
}
add_action('wp', 'mkx_log_404_errors');

/**
 * Suggest similar pages based on 404 URL (optional enhancement)
 */
function mkx_suggest_similar_pages($url) {
    // Extract potential search terms from URL
    $path = parse_url($url, PHP_URL_PATH);
    $terms = array_filter(explode('/', $path));
    
    if (empty($terms)) {
        return array();
    }

    // Search for similar products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 4,
        's' => implode(' ', $terms),
        'orderby' => 'relevance',
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);
    $suggestions = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $suggestions[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
            );
        }
        wp_reset_postdata();
    }

    return $suggestions;
}

/**
 * Set proper HTTP headers for 404 page
 */
function mkx_404_http_headers() {
    if (is_404()) {
        status_header(404);
        nocache_headers();
        
        // Add custom header for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            header('X-MKX-404: true');
        }
    }
}
add_action('send_headers', 'mkx_404_http_headers');

/**
 * Redirect common typos to correct pages (optional)
 */
function mkx_redirect_common_typos() {
    if (!is_404()) {
        return;
    }

    $request_uri = sanitize_text_field($_SERVER['REQUEST_URI'] ?? '');
    
    // Define common typos and their correct URLs
    $redirects = array(
        '/kontakty' => '/contacts/',
        '/kontakt' => '/contacts/',
        '/oplata' => '/oplata-i-dostavka/',
        '/dostavka' => '/oplata-i-dostavka/',
        '/katolog' => '/shop/',
        '/magazin' => '/shop/',
    );

    foreach ($redirects as $typo => $correct) {
        if (strpos($request_uri, $typo) !== false) {
            wp_safe_redirect(home_url($correct), 301);
            exit;
        }
    }
}
add_action('template_redirect', 'mkx_redirect_common_typos', 1);

/**
 * Clear 404 page cache when products are updated
 */
function mkx_clear_404_cache_on_product_update($post_id) {
    // Only for products
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // Clear transient cache