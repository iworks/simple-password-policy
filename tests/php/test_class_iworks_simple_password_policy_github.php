<?php
class Test_Iworks_Simple_Password_Policy_Github extends WP_UnitTestCase {

	protected $github;

	public function setUp(): void {
		parent::setUp();
		$this->github = new Iworks_Simple_Password_Policy_Github();
	}

	public function test_modify_transient_returns_object() {
		$transient = (object) array( 'checked' => array( 'plugin-file.php' => '1.0.0' ) );
		$result    = $this->github->modify_transient( $transient );
		$this->assertIsObject( $result );
	}
}

