<?php
/*
 * Plugin Name: Simple Password Policy
 * Text Domain: simple-password-policy
 * Plugin URI: PLUGIN_URI
 * Description: PLUGIN_TAGLINE
 * Version: PLUGIN_VERSION
 * Author: Marcin Pietrzak
 * Author URI: http://iworks.pl/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html

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
defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/**
 * static options
 */
define( 'IWORKS_SIMPLE_PASSWORD_POLICY_VERSION', 'PLUGIN_VERSION' );
define( 'IWORKS_SIMPLE_PASSWORD_POLICY_PREFIX', 'iwo_spp_' );
$base   = dirname( __FILE__ );
$vendor = $base . '/includes';

/**
 * require: Iworkssimple-password-policy Class
 */
if ( ! class_exists( 'iworks_simple_password_policy' ) ) {
	require_once $vendor . '/iworks/class-simple-password-policy.php';
}
/**
 * configuration
 */
require_once $base . '/etc/options.php';
/**
 * require: IworksOptions Class
 */
if ( ! class_exists( 'iworks_options' ) ) {
	require_once $vendor . '/iworks/options/options.php';
}
/**
 * load options
 */
function iworks_simple_password_policy_get_options() {
	global $iworks_simple_password_policy_options;
	if ( is_object( $iworks_simple_password_policy_options ) ) {
		return $iworks_simple_password_policy_options;
	}
	$iworks_simple_password_policy_options = new iworks_options();
	$iworks_simple_password_policy_options->set_option_function_name( 'iworks_simple_password_policy_options' );
	$iworks_simple_password_policy_options->set_option_prefix( IWORKS_SIMPLE_PASSWORD_POLICY_PREFIX );
	if ( method_exists( $iworks_simple_password_policy_options, 'set_plugin' ) ) {
		$iworks_simple_password_policy_options->set_plugin( basename( __FILE__ ) );
	}
	$iworks_simple_password_policy_options->options_init();
	return $iworks_simple_password_policy_options;
}

$iworks_simple_password_policy = new iworks_simple_password_policy();

/**
 * install & uninstall
 */
register_activation_hook( __FILE__, array( $iworks_simple_password_policy, 'register_activation_hook' ) );
register_deactivation_hook( __FILE__, array( $iworks_simple_password_policy, 'register_deactivation_hook' ) );
