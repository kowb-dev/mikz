<?php
/**
 * YITH WooCommerce Compare fixes.
 *
 * @package shoptimizer-child
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function kb_disable_yith_compare_button_on_product_page() {
    if ( class_exists( 'YITH_Woocompare' ) ) {
        update_option( 'yith_woocompare_compare_button_in_product_page', 'no' );
    }
}
add_action( 'init', 'kb_disable_yith_compare_button_on_product_page' );

function mkx_hide_yith_compare_added_notice() {
    echo '<style>
        .compare.added,
        a.compare.added {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            position: absolute !important;
            left: -9999px !important;
        }
    </style>';
    
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const removeCompareNotices = () => {
                document.querySelectorAll(".compare.added, a.compare.added").forEach(el => {
                    el.remove();
                });
            };
            removeCompareNotices();
            
            const observer = new MutationObserver(removeCompareNotices);
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>';
}
add_action( 'wp_head', 'mkx_hide_yith_compare_added_notice', 999 );
add_action( 'wp_footer', 'mkx_hide_yith_compare_added_notice', 999 );
