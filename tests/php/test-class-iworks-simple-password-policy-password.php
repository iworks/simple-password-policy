<?php
/**
 * Test class for iworks_simple_password_policy_password functionality.
 */
class Test_Iworks_Simple_Password_Policy_Password extends WP_UnitTestCase {

	/**
	 * Instance of the password policy class being tested.
	 *
	 * @var iworks_simple_password_policy_password
	 */
	protected $password;

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->password = new iworks_simple_password_policy_password();
	}

	/**
	 * Test that get_conditions() returns an array with expected keys.
	 *
	 * @covers iworks_simple_password_policy_password::get_conditions
	 */
	public function test_get_conditions_returns_array() {
		$conditions = $this->password->get_conditions();
		$this->assertIsArray( $conditions );
		$this->assertArrayHasKey( 'password_not_contain_lower_letters', $conditions );
		$this->assertArrayHasKey( 'password_not_contain_upper_letters', $conditions );
		$this->assertArrayHasKey( 'password_not_contain_digits', $conditions );
		$this->assertArrayHasKey( 'password_not_contain_special_characters', $conditions );
		$this->assertArrayHasKey( 'password_is_too_short', $conditions );
	}

	/**
	 * Test that get_version() returns a string.
	 *
	 * @covers iworks_simple_password_policy_password::get_version
	 */
	public function test_get_version_returns_string() {
		$version = $this->password->get_version();
		$this->assertIsString( $version );
	}

}
