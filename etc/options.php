<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function iworks_simple_password_policy_options() {
	$options = array();
	/**
	 * main settings
	 */
	$options['index'] = array(
		'version'    => '0.0',
		'page_title' => __( 'Passwords', 'simple-password-policy' ),
		'menu'       => 'options',
		'options'    => array(
			/**
			 * Password Settings
			 */
			array(
				'type'  => 'subheading',
				'label' => esc_html__( 'Password Policy', 'simple-password-policy' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'letters',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Letters', 'simple-consent-mode' ),
				'description'                => esc_html__( 'Must contain Lower and Uppercase Letters.', 'simple-consent-mode' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'digits',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Digits', 'simple-consent-mode' ),
				'description'                => esc_html__( 'Must contain Numeric Digits.', 'simple-consent-mode' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'special',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Special Characters', 'simple-consent-mode' ),
				'description'                => esc_html__( 'Must contain Special Characters.', 'simple-consent-mode' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'min_length',
				'type'              => 'number',
				'class'             => 'small-text',
				'th'                => __( 'Minimal Length', 'simple-consent-mode' ),
				'description'                => esc_html__( 'Minimal Length of password.', 'simple-consent-mode' ),
				'default'           => 48,
				'sanitize_callback' => 'absint',
				'since'             => '1.2.0',
			),
			/**
			 * Force Reset Password
			 */
			array(
				'type'  => 'subheading',
				'label' => esc_html__( 'Force Reset Password', 'simple-password-policy' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => '',
				'type'              => 'checkbox',
				'th'                => esc_html__( '', 'simple-consent-mode' ),
				'description'                => esc_html__( '', 'simple-consent-mode' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since' => '1.0.0',
			),
			/**
			 * Expiration Time
			 */
			array(
				'type'  => 'subheading',
				'label' => esc_html__( 'Expiration Time', 'simple-password-policy' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'expiration',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Enable', 'simple-consent-mode' ),
				'description'                => esc_html__( '', 'simple-consent-mode' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'exp_time',
				'type'              => 'number',
				'class'             => 'small-text',
				'th'                => __( 'Time', 'simple-consent-mode' ),
				'description'                => esc_html__( 'Select the password expiration time.', 'simple-consent-mode' ),
				'sufix'                => esc_html__( 'weeks', 'simple-consent-mode' ),
				'default'           => 12,
				'sanitize_callback' => 'absint',
				'since'             => '1.2.0',
			),
		),
		'metaboxes'  => array(),
		'pages'      => array(),
	);
	return $options;
}

