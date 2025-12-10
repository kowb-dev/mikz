<?php
/**
 * Minimal checkout fields for quick purchase.
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_checkout_fields', 'mkx_minimal_checkout_fields' );
function mkx_minimal_checkout_fields( $fields ) {
	$fields['billing'] = array(
		'billing_first_name' => array(
			'label'       => __( 'Имя', 'shoptimizer-child' ),
			'required'    => true,
			'type'        => 'text',
			'class'       => array( 'form-row-wide' ),
			'autocomplete'=> 'given-name',
			'placeholder' => __( 'пожалуйста, укажите Ваше имя', 'shoptimizer-child' ),
			'priority'    => 10,
		),
		'billing_phone' => array(
			'label'       => __( 'Телефон', 'shoptimizer-child' ),
			'placeholder' => __( 'необходимо для подтверждения заказа', 'shoptimizer-child' ),
			'required'    => true,
			'type'        => 'tel',
			'class'       => array( 'form-row-wide' ),
			'autocomplete'=> 'tel',
			'priority'    => 20,
		),
		'billing_email' => array(
			'label'       => __( 'Email', 'shoptimizer-child' ),
			'placeholder' => __( 'укажите для отслеживания статуса заказа', 'shoptimizer-child' ),
			'required'    => false,
			'type'        => 'email',
			'class'       => array( 'form-row-wide' ),
			'autocomplete'=> 'email',
			'priority'    => 30,
		),
	);

	$fields['shipping'] = array();
	$fields['account']  = array();
	$fields['order']    = array();

	return $fields;
}

add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

