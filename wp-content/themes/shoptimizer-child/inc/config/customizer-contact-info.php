<?php
/**
 * Adds contact information settings to the WordPress Customizer.
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register contact information settings in the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function mkx_register_contact_info_customizer( $wp_customize ) {

    // 1. Add Section for Contact Information
    $wp_customize->add_section( 'mkx_contact_info_section', [
        'title'    => __( 'Контактная информация', 'shoptimizer-child' ),
        'description' => __( 'Управление контактными данными, отображаемыми в шапке и подвале сайта.', 'shoptimizer-child' ),
        'priority' => 30,
    ] );

    // 2. Add Phone Number Setting & Control
    $wp_customize->add_setting( 'mkx_phone', [
        'default'           => '+7 (999) 123-45-67',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'mkx_phone_control', [
        'label'    => __( 'Номер телефона', 'shoptimizer-child' ),
        'section'  => 'mkx_contact_info_section',
        'settings' => 'mkx_phone',
        'type'     => 'text',
    ] );

    // 3. Add Email Address Setting & Control
    $wp_customize->add_setting( 'mkx_email', [
        'default'           => 'info@example.com',
        'sanitize_callback' => 'sanitize_email',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'mkx_email_control', [
        'label'    => __( 'Email адрес', 'shoptimizer-child' ),
        'section'  => 'mkx_contact_info_section',
        'settings' => 'mkx_email',
        'type'     => 'email',
    ] );

    // 4. Add Physical Address Setting & Control
    $wp_customize->add_setting( 'mkx_address', [
        'default'           => 'г. Москва, ул. Примерная, д. 123',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'mkx_address_control', [
        'label'    => __( 'Физический адрес', 'shoptimizer-child' ),
        'section'  => 'mkx_contact_info_section',
        'settings' => 'mkx_address',
        'type'     => 'textarea',
    ] );

    // 5. Add Working Hours Setting & Control (matches existing theme_mod key)
    $wp_customize->add_setting( 'mkx_footer_working_hours', [
        'default'           => 'Пн-Пт: 9:00-18:00, Сб-Вс: 10:00-16:00',
        'sanitize_callback' => 'wp_kses_post', // Use wp_kses_post to allow simple line breaks if needed
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'mkx_footer_working_hours_control', [
        'label'    => __( 'Режим работы', 'shoptimizer-child' ),
        'section'  => 'mkx_contact_info_section',
        'settings' => 'mkx_footer_working_hours',
        'type'     => 'textarea',
    ] );

}
add_action( 'customize_register', 'mkx_register_contact_info_customizer' );
