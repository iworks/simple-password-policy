<?php
/**
 * Test class for iworks_simple_password_policy_user functionality.
 */
class Test_Iworks_Simple_Password_Policy_User extends WP_UnitTestCase {

	/**
	 * Instance of the user policy class being tested.
	 *
	 * @var iworks_simple_password_policy_user
	 */
	protected $user;

	/**
	 * Set up the test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->user = new iworks_simple_password_policy_user();
	}

	/**
	 * Test that get_score_by_user_id() returns the correct integer score.
	 *
	 * @covers iworks_simple_password_policy_user::get_score_by_user_id
	 */
	public function test_get_score_by_user_id_returns_integer() {
		$user_id = $this->factory->user->create();
		update_user_meta( $user_id, $this->user->get_user_meta_strength_score_name(), 5 );
		$score = $this->user->get_score_by_user_id( $user_id );
		$this->assertEquals( 5, $score );
	}

	/**
	 * Test that password strength column is added to users table.
	 *
	 * @covers iworks_simple_password_policy_user::filter_manage_users_columns_add_password_strength
	 */
	public function test_filter_manage_users_columns_add_password_strength() {
		$columns = array();
		$result  = $this->user->filter_manage_users_columns_add_password_strength( $columns );
		$this->assertArrayHasKey( $this->user->get_user_meta_strength_score_name(), $result );
	}

	/**
	 * Test that password strength value is correctly displayed in users table.
	 *
	 * @covers iworks_simple_password_policy_user::filter_manage_users_custom_column_add_password_strength
	 */
	public function test_filter_manage_users_custom_column_add_password_strength() {
		$user_id = $this->factory->user->create();
		update_user_meta( $user_id, $this->user->get_user_meta_strength_score_name(), 5 );
		$column_name = $this->user->get_user_meta_strength_score_name();
		$value       = '';
		$result      = $this->user->filter_manage_users_custom_column_add_password_strength( $value, $column_name, $user_id );
		$this->assertIsString( $result );
	}
}
