<?php
/**
 * Test class for iworks_simple_password_policy_base functionality.
 */
class Test_Simple_Password_Policy_Base extends WP_UnitTestCase {

	/**
	 * Instance of the base policy class being tested.
	 *
	 * @var iworks_simple_password_policy_base
	 */
	protected $base;

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->base = $this->getMockForAbstractClass( 'iworks_simple_password_policy_base' );
	}

	public function test_nothing() {
		$this->assertTrue( true );
	}
}

