<?php
/*

Copyright 2025-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_simple_password_policy' ) ) {
	return;
}

require_once( dirname( __FILE__ ) . '/class-simple-password-policy-base.php' );

class iworks_simple_password_policy extends iworks_simple_password_policy_base {

	private $capability;

	/**
	 * Plugin Objects
	 *
	 * @since 1.0.0
	 */
	private array $objects = array();

	public function __construct() {
		parent::__construct();
		$this->version    = 'PLUGIN_VERSION';
		$this->capability = apply_filters( 'iworks_simple_password_policy_capability', 'manage_options' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'action_login_enqueue_scripts_enqueue_assets' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'action_login_enqueue_scripts_register_assets' ), 0 );
		/**
		 * load plugin classes class
		 */
		foreach ( array( 'user', 'password' ) as $class_key ) {
			include_once $this->includes_directory . '/class-iworks-simple-password-policy-' . $class_key . '.php';
			$class_name                  = sprintf( 'iworks_simple_password_policy_%s', $class_key );
			$this->objects[ $class_key ] = new $class_name();
		}
		/**
		 * load github class
		 */
		$filename = $this->includes_directory . '/class-iworks-simple-password-policy-github.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_simple_password_policy_github();
		}
		/**
		 * iWorks Options Class Hooks
		 */
		add_filter( 'iworks_simple_password_policy_options', array( $this, 'filter_options_add_roles' ) );
		/**
		 * is active?
		 */
		add_filter( 'simple-password-policy/is_active', '__return_true' );
	}

	/**
	 * register assets for login screen
	 *
	 * @since 1.0.0
	 */
	public function action_login_enqueue_scripts_register_assets() {
		$this->check_option_object();
		/**
		 * Cascading Style Sheets
		 */
		$handle = $this->slug_name( $this->options->get_option_name( 'login' ) );
		$file   = sprintf(
			'assets/styles/simple-password-policy-login%s.css',
			$this->dev
		);
		$file   = plugins_url( $file, dirname( $this->base ) );
		wp_register_style( $handle, $file, false, $this->get_version() );
		/**
		 * JavaScripts
		 */
		$file = sprintf(
			'assets/scripts/simple-password-policy-login%s.js',
			$this->dev
		);
		$file = plugins_url( $file, dirname( $this->base ) );
		wp_register_script( $handle, $file, false, $this->get_version() );
	}

	/**
	 * equeue assets for login screen
	 *
	 * @since 1.0.0
	 */
	public function action_login_enqueue_scripts_enqueue_assets() {
		$handle = $this->slug_name( $this->options->get_option_name( 'login' ) );
		wp_enqueue_style( $handle );
		wp_enqueue_script( $handle );
		wp_localize_script(
			$handle,
			preg_replace( '/-/', '_', $this->options->get_option_name( 'data' ) ),
			apply_filters(
				'iworks/simple-password-policy/login/script',
				array(
					'conditions' => array(),
					'fields'     => array(
						'ids' => array(
							'pass1',
						),
					),
					'list'       => array(
						'id' => 'iworks_simple-password-policy_conditions',
					),
				)
			)
		);
	}

	public function action_admin_init() {
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Plugin row data
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/simple-password-policy.php' == $file ) {
			if ( ! is_multisite() && current_user_can( $this->capability ) ) {
				$links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						add_query_arg(
							array(
								'page' => $this->dir . '/admin/index.php',
							),
							admin_url( 'admin.php' )
						)
					),
					esc_html__( 'Settings', 'simple-password-policy' )
				);
			}
			/* start:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'simple-password-policy',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://ko-fi.com/iworks'
					)
				),
				esc_html__( 'Donate', 'simple-password-policy' )
			);
			/* end:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'simple-password-policy',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://github.com/iworks.pl/simple-password-policy'
					)
				),
				esc_html__( 'GitHub', 'simple-password-policy' )
			);
		}
		return $links;
	}

	/**
	 * register_activation_hook
	 *
	 * @since 1.0.0
	 */
	public function register_activation_hook() {
		$this->db_install();
		$this->check_option_object();
		$this->options->activate();
		do_action( 'iworks/simple-password-policy/register_activation_hook' );
	}

	/**
	 * register_deactivation_hook
	 *
	 * @since 1.0.0
	 */
	public function register_deactivation_hook() {
		$this->check_option_object();
		$this->options->deactivate();
		do_action( 'iworks/simple-password-policy/register_deactivation_hook' );
	}

	/**
	 * db install (if needed)
	 *
	 * @since 1.0.0
	 */
	private function db_install() {
	}


	/**
	 * Get roles list to options.
	 *
	 * @since 1.0.0
	 */
	public function filter_options_add_roles( $options ) {
		global $wp_roles;
		if ( $wp_roles->roles && is_array( $wp_roles->roles ) ) {
			$roles = array();
			foreach ( $wp_roles->roles as $role => $role_data ) {
				$roles[ $role ] = translate_user_role( $role_data['name'] );
			}
			asort( $roles );
			$options['options']['roles']['options'] = $roles;
		}
		return $options;
	}

}
