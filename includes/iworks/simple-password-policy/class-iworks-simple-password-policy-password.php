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

	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'after_password_reset', array( $this, 'action_after_password_reset_check_reason_to_change' ), PHP_INT_MAX, 2 );
		add_action( 'resetpass_form', array( $this, 'action_resetpass_form_add_requirements' ) );
		add_filter( 'wp_authenticate_user', array( $this, 'filter_wp_authenticate_user_check_reason_to_change' ), PHP_INT_MAX, 2 );
		add_filter( 'wp_authenticate_user', array( $this, 'filter_wp_authenticate_user_update_score' ), PHP_INT_MAX, 2 );
		/**
		 * Own Hooks
		 */
		add_filter( 'iworks/simple-password-policy/login/script', array( $this, 'filter_wp_localize_script_add_configuration' ) );
	}

	/**
	 * add configuration to wp_localize_script object
	 *
	 * @since 1.0.0
	 */
	public function filter_wp_localize_script_add_configuration( $configuration ) {
		$this->check_option_object();
		foreach ( $this->get_conditions() as $condition => $data ) {
			if ( $data['use'] ) {
				$data['condition']             = $condition;
				$data['id']                    = $this->options->get_option_name( $condition );
				$configuration['conditions'][] = $data;
			}
		}
		return $configuration;
	}

	/**
	 * ads requirements list to password create form
	 *
	 * @since 1.0.0
	 */
	public function action_resetpass_form_add_requirements() {
		$this->check_option_object();
		$content = '';
		foreach ( $this->get_conditions() as $condition => $data ) {
			if ( $data['use'] ) {
				$content .= sprintf(
					'<li id="%s" data-pass="%s">%s</li>',
					esc_attr( $this->options->get_option_name( $condition ) ),
					esc_attr( $data['messages']['pass'] ),
					esc_html( $data['messages']['ask'] )
				);
			}
		}
		if ( $content ) {
			echo '<div class="simple-password-policy-requirements">';
			printf( '<h2>%s</h2>', esc_html__( 'Password Policy Requirements', 'simple-password-policy' ) );
			printf( '<ul id="%s">', esc_attr( $this->options->get_option_name( 'conditions' ) ) );
			/**
			 * this is escaped few lines above
			 */
			echo $content;
			echo '</ul>';
			echo '</div>';
		}
	}

	/**
	 * try to count password score
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user     The user.
	 * @param string  $password New user password.
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
	 *
	 * @param WP_User $user     The user.
	 * @param string  $password New user password.
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
		 * check conditions
		 * have user already some reasons?
		 */
		$reasons = $this->check_password_and_get_reasons( $user, $password );
		/**
		 * Force password reset on first login if not compliant with policy?
		 */
		if ( $this->options->get_option( 'force' ) ) {
			$text = '';
			foreach ( $reasons as $reason ) {
				if ( isset( $this->get_conditions()[ $reason ] ) ) {
					$text .= $this->get_conditions()[ $reason ]['messages']['need'];
					$text .= '<br>';
					$text .= '<br>';
				}
			}
			if ( $text ) {
				$text .= '<br>';
				$text .= sprintf(
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
				);
				/**
				 * first try to go to reset password page
				 */
				if ( wp_redirect(
					add_query_arg(
						array(
							'action'  => 'rp',
							'key'     => get_password_reset_key( $user ),
							'login'   => $user->user_login,
							'wp_lang' => get_user_locale( $user ),
							'reasons' => 'show',
						),
						site_url( 'wp-login.php' )
					)
				)
				) {
					exit;
				}
				/**
				 * return WP_Error as fallback
				 */
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

	/**
	 * Get the password policy conditions and their configurations.
	 *
	 * This method returns an array of password policy conditions with their respective configurations,
	 * including validation rules and user-facing messages. Each condition defines a specific
	 * password requirement such as minimum length, required character types, etc.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *     An associative array of password policy conditions where each key is a condition identifier
	 *     and the value is an array containing:
	 *     @type string   $option_name The option name this condition is associated with.
	 *     @type bool     $use         Whether this condition is enabled.
	 *     @type string   $regexp      Optional. Regular expression pattern to validate the condition.
	 *     @type string[] $messages    User-facing messages for this condition with keys:
	 *                                - 'ask':  Message shown when setting a new password.
	 *                                - 'need': Message shown when password doesn't meet requirement.
	 *                                - 'pass': Message shown when password meets requirement.
	 * }
	 */
	public function get_conditions() {
		$this->check_option_object();
		$password_minimal_length = intval( $this->options->get_option( 'length' ) );
		return apply_filters(
			'iworks/simple-password-policy/password/conditions',
			array(
				'password_not_contain_lower_letters'      => array(
					'option_name' => 'letters',
					'use'         => $this->options->get_option( 'letters' ),
					'regexp'      => '[a-z]',
					'messages'    => array(
						'ask'  => esc_html__( 'Please include at least one lowercase letter in your password.', 'simple-password-policy' ),
						'need' => esc_html__( 'Your password must include at least one lowercase letter.', 'simple-password-policy' ),
						'pass' => esc_html__( 'Lowercase letters included.', 'simple-password-policy' ),
					),
				),
				'password_not_contain_upper_letters'      => array(
					'option_name' => 'letters',
					'use'         => $this->options->get_option( 'letters' ),
					'regexp'      => '[A-Z]',
					'messages'    => array(
						'ask'  => esc_html__( 'Please include at least one uppercase letter in your password.', 'simple-password-policy' ),
						'need' => esc_html__( 'Your password must include at least one uppercase letter.', 'simple-password-policy' ),
						'pass' => esc_html__( 'Uppercase letters included.', 'simple-password-policy' ),
					),
				),
				'password_not_contain_digits'             => array(
					'option_name' => 'digits',
					'use'         => $this->options->get_option( 'digits' ),
					'regexp'      => '\d',
					'messages'    => array(
						'ask'  => esc_html__( 'Please include at least one digit in your password.', 'simple-password-policy' ),
						'need' => esc_html__( 'Your password must include at least one digit.', 'simple-password-policy' ),
						'pass' => esc_html__( 'Digits included.', 'simple-password-policy' ),
					),
				),
				'password_not_contain_special_characters' => array(
					'option_name' => 'specials',
					'use'         => $this->options->get_option( 'specials' ),
					'regexp'      => '\W',
					'messages'    => array(
						'ask'  => esc_html__( 'Please include at least one special character in your password.', 'simple-password-policy' ),
						'need' => esc_html__( 'Your password must include at least one special character.', 'simple-password-policy' ),
						'pass' => esc_html__( 'Special characters included.', 'simple-password-policy' ),
					),
				),
				'password_is_too_short'                   => array(
					'option_name' => 'length',
					'use'         => $password_minimal_length,
					'messages'    => array(
						'ask'  => sprintf(
							// translators: %d Minimal Password Length
							_n(
								'Your password must be at least %d character long. Please add more characters.',
								'Your password must be at least %d characters long. Please add more characters.',
								$password_minimal_length,
								'simple-password-policy'
							),
							$password_minimal_length
						),
						'need' => sprintf(
							// translators: %d Minimal Password Length
							_n(
								'Your passwords must be at least %d character long, but longer passphrases are recommended.',
								'Your passwords must be at least %d characters long, but longer passphrases are recommended.',
								$password_minimal_length,
								'simple-password-policy'
							),
							$password_minimal_length
						),
						'pass' => esc_html__( 'Password length meets the minimum requirement.', 'simple-password-policy' ),
					),
				),
			)
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
		/**
		 * it have a password!!! it is nice.
		 */
		$score = 1;
		/**
		 * Lowercase, Uppercase, Digits, Special Characters
		 */
		foreach ( $this->get_conditions() as $condition => $data ) {
			if ( isset( $data['regexp'] ) ) {
				if ( preg_match( '/' . $data['regexp'] . '/', $password ) ) {
					$score++;
				}
			}
		}
		/**
		 * length
		 */
		if ( strlen( $password ) > 7 ) {
			$score = $score + 2;
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
	 * Check new password after the user's password is reset.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user     The user.
	 * @param string  $password New user password.
	 */
	public function action_after_password_reset_check_reason_to_change( $user, $password ) {
		/**
		 * calculate user password score
		 */
		if ( ! add_user_meta( $user->ID, $this->user_meta_name_password_score, $this->calculate_score( $password ), true ) ) {
			update_user_meta( $user->ID, $this->user_meta_name_password_score, $this->calculate_score( $password ) );
		}
		/**
		 * check conditions
		 */
		$reasons = $this->check_password_and_get_reasons( $user, $password );
	}

	/**
	 * get list of reason why password not meets policy
	 *
	 * @since 1.0.0
	 */
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

	/**
	 * get single reason helper
	 *
	 * @since 1.0.0
	 */
	private function get_reason( $value ) {
		if ( isset( $this->get_conditions()[ $value ] ) ) {
			return $this->get_conditions()[ $value ]['messages']['need'];
		}
		return null;
	}

	/**
	 * get reasons and check user meta state
	 *
	 * Function saves data about password improvements.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user     The user.
	 * @param string  $password New user password.
	 */
	private function check_password_and_get_reasons( $user, $password ) {
		/**
		 * get reasons
		 */
		$reasons = get_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change );
		if ( empty( $reasons ) ) {
			$reasons = array();
		} elseif ( ! is_array( $reasons ) ) {
			$reasons = array( $reasons );
		}
		/**
		 * check conditions
		 */
		foreach ( $this->get_conditions() as $condition => $data ) {
			$delete = false;
			if ( $data['use'] ) {
				if ( ! in_array( $condition, $reasons ) ) {
					if ( isset( $data['regexp'] ) ) {
						if ( preg_match( '/' . $data['regexp'] . '/', $password ) ) {
							$delete = true;
						} else {
							add_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change, $condition );
						}
					} else {
						switch ( $data['option_name'] ) {
							case 'length':
								$length = intval( $data['use'] );
								if ( 0 < $length ) {
									if ( strlen( $password ) < $length ) {
										add_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change, $condition );
									} else {
										$delete = true;
									}
								} else {
									$delete = true;
								}
								break;
						}
					}
				} else {
					$delete = true;
				}
			} else {
				$delete = true;
			}
			if ( $delete ) {
				delete_user_meta( $user->ID, $this->user_meta_name_password_reason_to_change, $condition );
			}
		}
		/**
		 * return $reasons whatever it is
		 */
		return $reasons;
	}
}


