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

if ( class_exists( 'iworks_simple_password_policy_password' ) ) {
	return;
}

require_once( dirname( __DIR__ ) . '/class-simple-password-policy-base.php' );

class iworks_simple_password_policy_password extends iworks_simple_password_policy_base {

	/**
	 * configuration array for conditions
	 *
	 * @since 1.0.0
	 */
	private array $conditions = array();

	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_filter( 'wp_authenticate_user', array( $this, 'filter_wp_authenticate_user_update_score' ), PHP_INT_MAX, 2 );
		add_filter( 'wp_authenticate_user', array( $this, 'filter_wp_authenticate_user_check_reason_to_change' ), PHP_INT_MAX, 2 );
		add_action( 'init', array( $this, 'action_init_setup' ), PHP_INT_MAX );
	}

	/**
	 * try to count password score
	 *
	 * @since 1.0.0
	 */
	public function filter_wp_authenticate_user_update_score( $user, $password ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		if ( wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			if ( ! add_user_meta( $user->ID, $this->user_meta_name_password_score, $this->calculate_score( $password ), true ) ) {
				update_user_meta( $user->ID, $this->user_meta_name_password_score, $this->calculate_score( $password ) );
			}
		}
		return $user;
	}

	/**
	 * check is a reason to change a password?
	 *
	 * @since 1.0.0
	 */
	public function filter_wp_authenticate_user_check_reason_to_change( $user, $password ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			return $user;
		}
		$this->check_option_object();
		$roles = $this->options->get_option( 'roles' );
		if ( empty( $roles ) ) {
			return $user;
		}
		if ( ! is_array( $roles ) ) {
			return $user;
		}
		$check_user = false;
		foreach ( $roles as $role ) {
			if ( $check_user ) {
				continue;
			}
			$check_user = user_can( $user, $role );
		}
		if ( ! $check_user ) {
			return $user;
		}
		/**
		 * have user already some reasons?
		 */
		$reasons = get_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change );
		if ( ! is_array( $reasons ) ) {
			$reasons = array();
		}
		/**
		 * check conditions
		 */
		foreach ( $this->conditions as $condition => $data ) {
			if ( $data['use'] ) {
				if ( ! in_array( $condition, $reasons ) ) {
					if ( isset( $data['regexp'] ) ) {
						if ( ! preg_match( $data['regexp'], $password ) ) {
							add_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change, $condition );
						}
					} else {
						switch ( $data['option_name'] ) {
							case 'length':
								$length = intval( $data['use'] );
								if ( 0 < $length ) {
									if ( strlen( $password ) < $length ) {
										add_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change, $condition );
									}
								}
								break;
						}
					}
				}
			} else {
				delete_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change, $condition );
			}
		}
		/**
		 * Force password reset on first login if not compliant with policy?
		 */
		if ( $this->options->get_option( 'force' ) ) {
			$reasons = get_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change );
			if ( ! empty( $reasons ) ) {
				if ( ! is_array( $reasons ) ) {
					$reasons = array( $reasons );
				}
			}
			$text = '';
			foreach ( $reasons as $reason ) {
				if ( isset( $this->conditions[ $reason ] ) ) {
					$text .= wpautop( $this->conditions[ $reason ]['message'] );
					$text .= '<br>';
				}
			}
			if ( $text ) {
				$text .= '<br>';
				$text .= wpautop(
					sprintf(
						// translators: link to "Lost your password?"
						esc_html__( 'Please use link %s to reset your password.', 'simple-password-policy' ),
						sprintf(
							'<a href="%s">%s</a>',
							add_query_arg(
								array(
									'action' => 'lostpassword',
								),
								site_url( 'wp-login.php' )
							),
							esc_html__( 'Lost your password?', 'simple-password-policy' )
						)
					)
				);
				return new WP_Error(
					'policy',
					$text
				);
			}
		}
		/**
		 * return user Object
		 */
		return $user;
	}

	public function action_init_setup() {
		$this->check_option_object();
		$this->conditions = array(
			'password_not_contain_lower_letters'      => array(
				'option_name' => 'letters',
				'use'         => $this->options->get_option( 'letters' ),
				'regexp'      => '/[a-z]/',
				'message'     => esc_html__( 'Your password must include at least one lowercase letter.', 'simple-password-policy' ),
			),
			'password_not_contain_upper_letters'      => array(
				'option_name' => 'letters',
				'use'         => $this->options->get_option( 'letters' ),
				'regexp'      => '/[A-Z]/',
				'message'     => esc_html__( 'Your password must include at least one uppercase letter.', 'simple-password-policy' ),
			),
			'password_not_contain_digits'             => array(
				'option_name' => 'digits',
				'use'         => $this->options->get_option( 'digits' ),
				'regexp'      => '/\d/',
				'message'     => esc_html__( 'Your password must include at least one digit.', 'simple-password-policy' ),
			),
			'password_not_contain_special_characters' => array(
				'option_name' => 'specials',
				'use'         => $this->options->get_option( 'specials' ),
				'regexp'      => '/[#$%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\`]/',
				'message'     => esc_html__( 'Your password must include at least one special character.', 'simple-password-policy' ),
			),
			'password_is_to_short'                    => array(
				'option_name' => 'length',
				'use'         => intval( $this->options->get_option( 'length' ) ),
				'message'     => sprintf(
					// translators: %d Minimal Password Length
					_n(
						'Your passwords must be at least %d character long, but longer passphrases are recommended.',
						'Your passwords must be at least %d characters long, but longer passphrases are recommended.',
						intval( $this->options->get_option( 'length' ) ),
						'simple-password-policy'
					),
					intval( $this->options->get_option( 'length' ) )
				),
			),
		);
	}


	/**
	 * Function to return strength of the given password.
	 *
	 * @param string $password user password.
	 * @return int
	 */
	public function calculate_score( $password ) {
		if ( empty( $password ) ) {
			return 0;
		}
		$score = 1;
		if ( strlen( $password ) > 7 ) {
			$score = $score + 2;
		}
		if ( preg_match( '/[a-z]/', $password ) ) {
			$score++;
		}
		if ( preg_match( '/[A-Z]/', $password ) ) {
			$score++;
		}
		if ( preg_match( '#[0-9]+#', $password ) ) {
			$score++;
		}
		if ( preg_match( $this->conditions['password_not_contain_special_characters']['regexp'], $password ) ) {
			$score++;
		}
		if ( strlen( $password ) > 12 ) {
			$score = $score + 2;
		}
		if ( strlen( $password ) > 17 ) {
			$score = $score + 2;
		}
		return $score;
	}

	/**
	 * Function to validate password
	 *
	 * @param string $password user password.
	 * @return string message
	 */
	public function validate_password( $password ) {
		$length_pass = strlen( $password );
		if ( ( get_site_option( 'moppm_Numeric_digit' ) === '1' ) && ( ! preg_match( '#[0-9]+#', $password ) ) ) {
			return 'New password must contain numeric value.';
		}
		if ( ( get_site_option( 'moppm_letter' ) === '1' ) && ( ! preg_match( '/[a-z]/', $password ) ) ) {
			return 'New password must contain lower case letter.';
		}
		if ( ( get_site_option( 'moppm_letter' ) === '1' ) && ( ! preg_match( '/[A-Z]/', $password ) ) ) {
			return 'New password must contain upper case letter.';
		}

		if ( ( get_site_option( 'moppm_special_char' ) === '1' ) && ( ! preg_match( "/[@#$\%&\!*?()_+{:;'\><,.}]/", $password ) ) ) {
			return 'New password must contain special character.';
		}
		if ( $length_pass < get_site_option( 'moppm_digit' ) ) {
			return 'New password must contain at least ' . get_site_option( 'moppm_digit' ) . ' characters.';
		}
		return 'VALID';
	}


	private function get_user_reasons_by_user_id( $user_id ) {
		$reasons = array();
		$value   = get_user_meta( $user_id, $this->user_meta_name_password_reason_to_change );
		if ( empty( $value ) ) {
			return $reasons;
		}
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}
		foreach ( $value as $reason ) {
			$r = $this->get_reason( $value );
			if ( $r ) {
				$reasons[ $value ] = $r;
			}
		}
		return $reasons;
	}

	private function get_reason( $value ) {
		if ( isset( $this->conditions[ $value ] ) ) {
			return $values[ $value ]['message'];
		}
		return null;
	}
}


