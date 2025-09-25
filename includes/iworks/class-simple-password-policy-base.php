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

if ( class_exists( 'iworks_simple_password_policy_base' ) ) {
	return;
}

class iworks_simple_password_policy_base {

	protected $dev;
	protected $meta_prefix = '_iw';
	protected $base;
	protected $dir;
	protected $url;
	protected $plugin_file;
	protected $plugin_file_path;

	/**
	 * plugin settings capability
	 */
	private string $capability = 'manage_options';

	/**
	 * plugin version
	 */
	protected string $version = 'PLUGIN_VERSION.BUILDTIME';

	/**
	 * plugin includes directory
	 *
	 * @since 1.0.0
	 */
	protected string $includes_directory;

	/**
	 * DEBUG
	 *
	 * @since 1.0.0
	 */
	protected $debug = false;

	/**
	 * EOL?
	 *
	 * @since 1.0.0
	 */
	protected string $eol = '';

	/**
	 * iWorks Options Class Object
	 *
	 * @since 1.0.0
	 */
	protected $options;

	/**
	 * user meta name for password score
	 *
	 * @since 1.0.0
	 */
	protected string $user_meta_name_password_score = 'spp_pass_score';

	/**
	 * user meta name for password reason to change
	 *
	 * @since 1.0.0
	 */
	protected string $user_meta_name_password_reason_to_change = 'spp_pass_reason';

	/**
	 * constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		/**
		 * static settings
		 */
		$this->debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE );
		/**
		 * use minimized scripts if not debug
		 */
		$this->dev = $this->debug ? '' : '.min';
		/**
		 * add EOL if debug
		 */
		$this->eol = $this->debug ? PHP_EOL : '';
		/**
		 * directories and urls
		 */
		$this->base = __DIR__;
		$this->dir  = basename( dirname( $this->base, 2 ) );
		$this->url  = plugins_url( $this->dir );
		/**
		 * plugin ID
		 */
		$this->plugin_file_path = $this->base . '/simple-password-policy.php';
		$this->plugin_file      = plugin_basename( $this->plugin_file_path );
		/**
		 * plugin includes directory
		 */
		$this->includes_directory = __DIR__ . '/simple-password-policy';
		/**
		 * WordPress Hooks
		 */
	}

	/**
	 * Get the plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Optional. The file path to generate a version based on. Defaults to null.
	 * @return string The plugin version.
	 */
	public function get_version( $file = null ) {
		if ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE ) {
			if ( null != $file ) {
				$file = dirname( $this->base ) . $file;
				if ( is_file( $file ) ) {
					return md5_file( $file );
				}
			}
			return time();
		}
		return $this->version;
	}

	/**
	 * Get the meta name for a given name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name to generate a meta name for.
	 * @return string The meta name.
	 */
	protected function get_meta_name( $name ) {
		return sprintf( '%s_%s', $this->meta_prefix, sanitize_title( $name ) );
	}

	/**
	 * Get the capability required to access the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return string The capability required to access the plugin.
	 */
	public function get_this_capability() {
		return $this->capability;
	}

	/**
	 * Get the slug name for a given name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The name to generate a slug name for.
	 * @return string The slug name.
	 */
	protected function slug_name( $name ) {
		return preg_replace( '/[_ ]+/', '-', strtolower( __CLASS__ . '_' . $name ) );
	}

	/**
	 * Get the post meta value for a given post ID and meta key.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id The ID of the post to retrieve the meta value for.
	 * @param string $meta_key The meta key to retrieve the value for.
	 * @return mixed The meta value.
	 */
	public function get_post_meta( $post_id, $meta_key ) {
		return get_post_meta( $post_id, $this->get_meta_name( $meta_key ), true );
	}

	/**
	 * Get the module file path for a given filename and vendor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename The filename of the module to retrieve the path for.
	 * @param string $vendor Optional. The vendor of the module to retrieve the path for. Defaults to 'iworks'.
	 * @return string The module file path.
	 */
	protected function get_module_file( $filename, $vendor = 'iworks' ) {
		return realpath(
			sprintf(
				'%s/%s/%s/%s.php',
				$this->base,
				$vendor,
				$this->dir,
				$filename
			)
		);
	}

	/**
	 * HTML title
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The text to display in the title.
	 */
	protected function html_title( $text ) {
		printf( '<h1 class="wp-heading-inline">%s</h1>', esc_html( $text ) );
	}

	/**
	 * check option object
	 *
	 * @since 1.0.0
	 */
	protected function check_option_object() {
		if ( is_a( $this->options, 'iworks_options' ) ) {
			return;
		}
		$this->options = iworks_simple_password_policy_get_options();
	}
}
