<?php
/**
 * Footer Customizer Settings
 *
 * @package Shoptimizer Child
 * @version 1.0.2
 * @author KW
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Добавление настроек футера в customizer
 */
add_action( 'customize_register', 'mkx_customize_register_footer' );
function mkx_customize_register_footer( $wp_customize ) {
    
    // ==========================================================================
    // FOOTER PANEL
    // ==========================================================================
    
    $wp_customize->add_panel( 'mkx_footer_panel', array(
        'title'       => __( 'Настройки футера', 'shoptimizer-child' ),
        'description' => __( 'Настройка всех элементов футера сайта', 'shoptimizer-child' ),
        'priority'    => 130,
    ) );

    // ==========================================================================
    // COMPANY SECTION
    // ==========================================================================
    
    $wp_customize->add_section( 'mkx_footer_company', array(
        'title'    => __( 'О компании', 'shoptimizer-child' ),
        'panel'    => 'mkx_footer_panel',
        'priority' => 10,
    ) );

    // Company Title
    $wp_customize->add_setting( 'mkx_footer_company_title', array(
        'default'           => __( 'О магазине', 'shoptimizer-child' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_footer_company_title', array(
        'label'       => __( 'Заголовок секции', 'shoptimizer-child' ),
        'section'     => 'mkx_footer_company',
        'type'        => 'text',
        'description' => __( 'Заголовок для секции "О компании"', 'shoptimizer-child' ),
    ) );

    // Company Description
    $wp_customize->add_setting( 'mkx_footer_description', array(
        'default'           => __( 'Качественные запчасти для мобильных телефонов с гарантией качества и быстрой доставкой по всей стране.', 'shoptimizer-child' ),
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_footer_description', array(
        'label'       => __( 'Описание компании', 'shoptimizer-child' ),
        'section'     => 'mkx_footer_company',
        'type'        => 'textarea',
        'description' => __( 'Краткое описание вашей компании', 'shoptimizer-child' ),
    ) );

    // Show Working Hours
    $wp_customize->add_setting( 'mkx_footer_show_working_hours', array(
        'default'           => true,
        'sanitize_callback' => 'mkx_sanitize_checkbox',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_footer_show_working_hours', array(
        'label'   => __( 'Показывать режим работы', 'shoptimizer-child' ),
        'section' => 'mkx_footer_company',
        'type'    => 'checkbox',
    ) );

    // Working Hours
    $wp_customize->add_setting( 'mkx_footer_working_hours', array(
        'default'           => __( 'Пн-Пт: 9:00-18:00, Сб-Вс: 10:00-16:00', 'shoptimizer-child' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_footer_working_hours', array(
        'label'           => __( 'Режим работы', 'shoptimizer-child' ),
        'section'         => 'mkx_footer_company',
        'type'            => 'text',
        'active_callback' => 'mkx_is_working_hours_active',
    ) );

    // ==========================================================================
    // CONTACT SECTION
    // ==========================================================================
    
    $wp_customize->add_section( 'mkx_footer_contacts', array(
        'title'    => __( 'Контактная информация', 'shoptimizer-child' ),
        'panel'    => 'mkx_footer_panel',
        'priority' => 20,
    ) );

    // Email
    $wp_customize->add_setting( 'mkx_email', array(
        'default'           => 'info@example.com',
        'sanitize_callback' => 'sanitize_email',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_email', array(
        'label'       => __( 'Email адрес', 'shoptimizer-child' ),
        'section'     => 'mkx_footer_contacts',
        'type'        => 'email',
        'description' => __( 'Основной email для связи', 'shoptimizer-child' ),
    ) );

    // Address
    $wp_customize->add_setting( 'mkx_address', array(
        'default'           => __( 'г. Москва, ул. Примерная, д. 123', 'shoptimizer-child' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_address', array(
        'label'   => __( 'Адрес', 'shoptimizer-child' ),
        'section' => 'mkx_footer_contacts',
        'type'    => 'text',
    ) );

    // ==========================================================================
    // SOCIAL LINKS SECTION
    // ==========================================================================
    
    $wp_customize->add_section( 'mkx_footer_social', array(
        'title'    => __( 'Социальные сети', 'shoptimizer-child' ),
        'panel'    => 'mkx_footer_panel',
        'priority' => 30,
    ) );

    // Show Social Links
    $wp_customize->add_setting( 'mkx_footer_show_social', array(
        'default'           => true,
        'sanitize_callback' => 'mkx_sanitize_checkbox',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_footer_show_social', array(
        'label'   => __( 'Показывать социальные сети', 'shoptimizer-child' ),
        'section' => 'mkx_footer_social',
        'type'    => 'checkbox',
    ) );

    // Social Networks
    $social_networks = array(
        'vk'        => __( 'VKontakte', 'shoptimizer-child' ),
        'telegram'  => __( 'Telegram', 'shoptimizer-child' ),
        'whatsapp'  => __( 'WhatsApp', 'shoptimizer-child' ),
        'instagram' => __( 'Instagram', 'shoptimizer-child' ),
    );

    foreach ( $social_networks as $network => $label ) {
        $wp_customize->add_setting( "mkx_social_{$network}", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'postMessage',
        ) );

        $wp_customize->add_control( "mkx_social_{$network}", array(
            'label'           => $label,
            'section'         => 'mkx_footer_social',
            'type'            => 'url',
            'active_callback' => 'mkx_is_social_active',
        ) );
    }

    // ==========================================================================
    // NEWSLETTER SECTION
    // ==========================================================================
    
    $wp_customize->add_section( 'mkx_footer_newsletter', array(
        'title'    => __( 'Подписка на рассылку', 'shoptimizer-child' ),
        'panel'    => 'mkx_footer_panel',
        'priority' => 40,
    ) );

    // Newsletter Description
    $wp_customize->add_setting( 'mkx_newsletter_description', array(
        'default'           => __( 'Подпишитесь на нашу рассылку и получайте информацию о новых товарах и акциях', 'shoptimizer-child' ),
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_newsletter_description', array(
        'label'       => __( 'Описание рассылки', 'shoptimizer-child' ),
        'section'     => 'mkx_footer_newsletter',
        'type'        => 'textarea',
        'description' => __( 'Текст-приглашение к подписке', 'shoptimizer-child' ),
    ) );

    // CF7 Shortcode
    $wp_customize->add_setting( 'mkx_newsletter_cf7_shortcode', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_newsletter_cf7_shortcode', array(
        'label'       => __( 'Shortcode Contact Form 7', 'shoptimizer-child' ),
        'section'     => 'mkx_footer_newsletter',
        'type'        => 'text',
        'description' => __( 'Вставьте shortcode формы CF7, например: [contact-form-7 id="123"]', 'shoptimizer-child' ),
    ) );

    // Show Benefits
    $wp_customize->add_setting( 'mkx_footer_show_benefits', array(
        'default'           => true,
        'sanitize_callback' => 'mkx_sanitize_checkbox',
        'transport'         => 'postMessage',
    ) );

    $wp_customize->add_control( 'mkx_footer_show_benefits', array(
        'label'   => __( 'Показывать преимущества подписки', 'shoptimizer-child' ),
        'section' => 'mkx_footer_newsletter',
        'type'    => 'checkbox',
    ) );

    // ==========================================================================
    // SELECTIVE REFRESH (PARTIAL REFRESH) 
    // ==========================================================================
    
    if ( isset( $wp_customize->selective_refresh ) ) {
        // Company info partial
        $wp_customize->selective_refresh->add_partial( 'mkx_footer_company_title', array(
            'selector'        => '.mkx-footer-company .mkx-footer-section-title',
            'render_callback' => function() {
                return esc_html( get_theme_mod( 'mkx_footer_company_title', __( 'О магазине', 'shoptimizer-child' ) ) );
            },
        ) );

        $wp_customize->selective_refresh->add_partial( 'mkx_footer_description', array(
            'selector'        => '.mkx-footer-description',
            'render_callback' => function() {
                return wp_kses_post( get_theme_mod( 'mkx_footer_description', __( 'Качественные запчасти для мобильных телефонов с гарантией качества и быстрой доставкой по всей стране.', 'shoptimizer-child' ) ) );
            },
        ) );

        // Newsletter partial
        $wp_customize->selective_refresh->add_partial( 'mkx_newsletter_description', array(
            'selector'        => '.mkx-newsletter-description',
            'render_callback' => function() {
                return esc_html( get_theme_mod( 'mkx_newsletter_description', __( 'Подпишитесь на нашу рассылку и получайте информацию о новых товарах и акциях', 'shoptimizer-child' ) ) );
            },
        ) );
    }
}

/**
 * Active callback for working hours
 */
function mkx_is_working_hours_active( $control ) {
    return $control->manager->get_setting( 'mkx_footer_show_working_hours' )->value();
}

/**
 * Active callback for social links
 */
function mkx_is_social_active( $control ) {
    return $control->manager->get_setting( 'mkx_footer_show_social' )->value();
}

/**
 * Sanitize checkbox
 */
function mkx_sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Register footer menus
 */
add_action( 'init', 'mkx_register_footer_menus' );
function mkx_register_footer_menus() {
    register_nav_menus( array(
        'footer-catalog' => __( 'Футер - Каталог', 'shoptimizer-child' ),
        'footer-links'   => __( 'Футер - Дополнительные ссылки', 'shoptimizer-child' ),
    ) );
}
