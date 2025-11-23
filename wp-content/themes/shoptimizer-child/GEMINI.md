## Project Overview

This is a WordPress child theme for the "Shoptimizer" theme, designed for a WooCommerce-based online store. The store, named "МИКЗ," specializes in selling spare parts for mobile phones and computers.

The theme is built with PHP, JavaScript, and CSS. It has a well-organized, modular structure. The main `functions.php` file acts as a loader for various components located in the `inc/` directory, which handle everything from theme setup and script enqueuing to custom features and plugin integrations.

The frontend is designed to be responsive and mobile-first, with a complex header that includes a mega menu, a mobile-specific navigation, and a hero carousel on the front page. The theme uses a modern CSS approach with extensive use of custom properties for a consistent design system.

## Building and Running

This is a WordPress theme and doesn't require a separate build process. To use this theme, you need to:

1.  Have a WordPress site set up.
2.  Install the parent "Shoptimizer" theme.
3.  Install and activate this child theme ("shoptimizer-child") in the WordPress admin panel under "Appearance" > "Themes."

The theme is designed to work with the WooCommerce plugin, which should also be installed and activated.

## Development Conventions

*   **Structure:** The theme's functionality is modularized and located in the `inc/` directory. Core functions, configuration, and features are separated into their own files.
*   **PHP:** The PHP code is well-documented with comments in Russian. It follows WordPress coding standards.
*   **CSS:** The theme uses a comprehensive set of CSS custom properties (variables) for managing the design system, including typography, colors, spacing, and layout. This is the preferred way to make styling changes.
*   **JavaScript:** The JavaScript code is organized into classes and is used to handle dynamic features like the header's mega menu, mobile navigation, and carousels.
*   **Dependencies:** The theme relies on the parent Shoptimizer theme and the WooCommerce plugin.