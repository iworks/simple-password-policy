<?php
class Test_Iworks_Simple_Password_Policy_User extends WP_UnitTestCase {

	protected $user;

	public function setUp(): void {
		parent::setUp();
		$this->user = new Iworks_Simple_Password_Policy_User();
	}

	public function test_get_score_by_user_id_returns_integer() {
		$user_id = $this->factory->user->create();
		update_user_meta( $user_id, $this->user->user_meta_name_password_score, 5 );
		$score = $this->user->get_score_by_user_id( $user_id );
		$this->assertEquals( 5, $score );
	}

	public function test_filter_manage_users_columns_add_password_strength() {
		$columns = array();
		$result  = $this->user->filter_manage_users_columns_add_password_strength( $columns );
		$this->assertArrayHasKey( $this->user->user_column_password_strength_score_name, $result );
	}

	public function test_filter_manage_users_custom_column_add_password_strength() {
		$user_id = $this->factory->user->create();
		update_user_meta( $user_id, $this->user->user_meta_name_password_score, 5 );
		$column_name = $this->user->user_column_password_strength_score_name;
		$value       = '';
		$result      = $this->user->filter_manage_users_custom_column_add_password_strength( $value, $column_name, $user_id );
		$this->assertIsString( $result );
	}
}

