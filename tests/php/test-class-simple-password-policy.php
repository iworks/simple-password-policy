<?php
class Test_Simple_Password_Policy extends WP_UnitTestCase {
	/**
	 * @var iworks_simple_password_policy
	 */
	private $policy;

	public function setUp(): void {
		parent::setUp();
		$this->policy = new iworks_simple_password_policy();
	}

	public function test_action_hooks_registered() {
		global $wp_filter;
		$this->assertArrayHasKey( 'admin_init', $wp_filter );
		$this->assertArrayHasKey( 'login_enqueue_scripts', $wp_filter );
	}

	public function test_register_activation_hook() {
		$this->policy->register_activation_hook();
		// Assert options are set, or actions are triggered as expected
		$this->assertTrue( 0 < did_action( 'iworks/simple-password-policy/register_activation_hook' ) );
	}

	public function test_register_deactivation_hook() {
		$this->policy->register_deactivation_hook();
		// Assert options are set, or actions are triggered as expected
		$this->assertTrue( 0 < did_action( 'iworks/simple-password-policy/register_deactivation_hook' ) );
	}
}
