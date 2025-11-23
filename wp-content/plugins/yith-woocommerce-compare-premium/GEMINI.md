# Project Overview

This is the premium version of the YITH WooCommerce Compare plugin for WordPress. It allows users to compare products in a WooCommerce store. The plugin is written in PHP and utilizes a custom "YITH plugin framework" for its structure and functionality.

## Key Features:

*   **Product Comparison:** Allows users to select products and compare their features in a table.
*   **WooCommerce Integration:** Deeply integrates with WooCommerce, including products, categories, and attributes.
*   **Customization:** Offers extensive options for customizing the appearance and behavior of the comparison table, buttons, and widgets.
*   **Premium Features:** Includes premium features like comparing by category, excluding categories, limiting the number of compared products, and showing related products.
*   **Shortcodes and Widgets:** Provides shortcodes (`[yith_woocompare_table]`, `[yith_woocompare_counter]`) and widgets for displaying the comparison functionality.
*   **AJAX-powered:** Uses AJAX for a smooth user experience when adding products to compare and filtering the comparison table.
*   **Social Sharing:** Allows users to share their comparison tables on social media.
*   **Plugin Framework:** Built on the YITH Plugin Framework, which handles licensing, updates, and other core functionalities.

# Building and Running

This is a WordPress plugin, so there is no build process. To run the plugin, you need to have a WordPress installation with WooCommerce installed.

1.  **Installation:**
    *   Copy the plugin directory to the `wp-content/plugins` directory of your WordPress installation.
    *   Activate the plugin from the WordPress admin panel.

2.  **Configuration:**
    *   The plugin's settings can be configured from the YITH > Compare menu in the WordPress admin panel.

# Development Conventions

*   **File Structure:** The plugin follows a structured file organization, with separate directories for assets (CSS, JS, images), includes (PHP classes), languages, templates, and widgets.
*   **Class Structure:** The plugin is object-oriented, with a main plugin class (`YITH_Woocompare`) that instantiates frontend and admin classes (`YITH_Woocompare_Frontend_Premium`, `YITH_Woocompare_Admin_Premium`).
*   **Naming Conventions:** The plugin uses the `yith_` prefix for its functions and classes, and the `YITH_` prefix for its classes.
*   **Hooks and Filters:** The plugin makes extensive use of WordPress hooks and filters to extend its functionality and allow for customization.
*   **Templates:** The plugin uses templates for its output, which can be overridden in the theme.
*   **Localization:** The plugin is localized using the `yith-woocommerce-compare` text domain.
