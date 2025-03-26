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

if ( class_exists( 'iworks_simple_password_policy_user' ) ) {
	return;
}

require_once( dirname( __DIR__ ) . '/class-simple-password-policy-base.php' );

class iworks_simple_password_policy_user extends iworks_simple_password_policy_base {

	/**
	 * User column key for Password Strength Indicator
	 *
	 * @since 1.0.0
	 */
	private string $user_column_password_strength_score_name = 'simple-password-policy-password-strength-score';

	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_filter( 'manage_users_custom_column', array( $this, 'filter_manage_users_custom_column_add_password_strength' ), 10, 3 );
		add_filter( 'manage_users_columns', array( $this, 'filter_manage_users_columns_add_password_strength' ) );
	}

	/**
	 * Function to add one column 'Password Strength Score' in user table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns column.
	 * @return array
	 */
	public function filter_manage_users_columns_add_password_strength( $columns ) {
		$columns[ $this->user_column_password_strength_score_name ] = esc_html__( 'Password Strength Score', 'simple-password-policy' );
		return $columns;
	}

	private function get_score_by_user_id( $user_id ) {
		return intval( get_user_meta( $user_id, $this->user_meta_name_password_score, true ) );
	}

	/**
	 * Function to add content to custom row in user table
	 *
	 * @since 1.0.0
	 *
	 * @param string $value data to add in user table.
	 * @param string $column_name column in which we add custom data.
	 * @param int    $user_id user_id for which we want to add data in custom column.
	 * @return string
	 */
	public function filter_manage_users_custom_column_add_password_strength( $value, $column_name, $user_id ) {
		if ( $this->user_column_password_strength_score_name === $column_name ) {
			$score = $this->get_score_by_user_id( $user_id );
			if ( $score ) {
				$value  = sprintf( '<span aria-hidden="true">%d</span>', $score );
				$value .= sprintf(
					'<span class="screen-reader-text">%s</span>',
					sprintf(
						// translators: %d score of the user password
						_n( '%d point by this password', '%d points by this password', $score, 'simple-password-policy' ),
						$score
					)
				);
			} else {
				$value  = sprintf( '<span aria-hidden="true">%s</span>', esc_html__( 'N/A', 'simple-password-policy' ) );
				$value .= sprintf(
					'<span class="screen-reader-text">%s</span>',
					esc_html__( 'There is no password score.', 'simple-password-policy' )
				);
			}
		}
		return $value;
	}
}

