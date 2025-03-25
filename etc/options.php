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
		'use_tabs'   => true,
		'options'    => array(
			array(
				'type'  => 'heading',
				'label' => esc_html__( 'Password Policy', 'simple-password-policy' ),
				'since' => '1.0.0',
			),
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
				'th'                => esc_html__( 'Letters', 'simple-password-policy' ),
				'description'       => esc_html__( 'Must contain Lower and Uppercase Letters.', 'simple-password-policy' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since'             => '1.0.0',
			),
			array(
				'name'              => 'digits',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Digits', 'simple-password-policy' ),
				'description'       => esc_html__( 'Must contain Numeric Digits.', 'simple-password-policy' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since'             => '1.0.0',
			),
			array(
				'name'              => 'specials',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Special Characters', 'simple-password-policy' ),
				'description'       => esc_html__( 'Must contain Special Characters.', 'simple-password-policy' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since'             => '1.0.0',
			),
			array(
				'name'              => 'length',
				'type'              => 'number',
				'class'             => 'small-text',
				'th'                => __( 'Minimal Length', 'simple-password-policy' ),
				'description'       => esc_html__( 'Minimal Length of password.', 'simple-password-policy' ),
				'default'           => 12,
				'sanitize_callback' => 'absint',
				'since'             => '1.0.0',
			),
			/**
			 * Force Reset Password
			 */
			array(
				'type'  => 'subheading',
				'label' => esc_html__( 'Reset Password', 'simple-password-policy' ),
				'since' => '1.0.0',
			),
			array(
				'name'              => 'force',
				'type'              => 'checkbox',
				'th'                => esc_html__( 'Force', 'simple-password-policy' ),
				'description'       => esc_html__( 'Force password reset on first login if not compliant with policy.', 'simple-password-policy' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since'             => '1.0.0',
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
				'th'                => esc_html__( 'Enable', 'simple-password-policy' ),
				'description'       => esc_html__( 'Specify the number of weeks before the password must be changed.', 'simple-password-policy' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'since'             => '1.0.0',
			),
			array(
				'name'              => 'exp_time',
				'type'              => 'number',
				'class'             => 'small-text',
				'th'                => __( 'Time', 'simple-password-policy' ),
				'description'       => esc_html__( 'Select the password expiration time.', 'simple-password-policy' ),
				'label'             => esc_html__( 'weeks', 'simple-password-policy' ),
				'default'           => 12,
				'sanitize_callback' => 'absint',
				'since'             => '1.0.0',
			),
			array(
				'type'  => 'heading',
				'label' => esc_html__( 'Roles', 'simple-password-policy' ),
				'since' => '1.0.0',
			),
			'roles' => array(
				'name'    => 'roles',
				'type'    => 'checkbox_group',
				'th'      => __( 'Roles', 'simple-password-policy' ),
				'since'   => '1.0.0',
				'options' => array(),
			),
		),
		'metaboxes'  => array(),
		'pages'      => array(),
	);
	return $options;
}

