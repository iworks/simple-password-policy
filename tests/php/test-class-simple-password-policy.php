<?php
class Test_Simple_Password_Policy extends WP_UnitTestCase {

	protected $objects = array();
	protected $capability;

	public function setUp(): void {
		parent::setUp();
		$this->policy = new Simple_Password_Policy();
	}

	public function test_action_hooks_registered() {
		global $wp_filter;
		$this->assertArrayHasKey( 'admin_init', $wp_filter );
		$this->assertArrayHasKey( 'login_enqueue_scripts', $wp_filter );
	}

	public function test_register_activation_hook() {
		$this->policy->register_activation_hook();
		// Assert options are set, or actions are triggered as expected
		$this->assertTrue( has_action( 'iworks/simple-password-policy/register_activation_hook' ) !== false );
	}
}

