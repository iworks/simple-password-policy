<?php
class Test_Iworks_Simple_Password_Policy_Password extends WP_UnitTestCase {

	protected $password;

	public function setUp(): void {
		parent::setUp();
		$this->password             = new Iworks_Simple_Password_Policy_Password();
		$this->password->conditions = array(
			'min_length' => array(
				'use'   => true,
				'label' => 'Minimum Length',
			),
		);
		$this->password->options    = $this->getMockBuilder( stdClass::class )->getMock();
		$this->password->options->method( 'get_option_name' )->willReturn( 'min_length' );
	}

	public function test_get_configuration_returns_array() {
		$config = $this->password->get_configuration();
		$this->assertIsArray( $config );
		$this->assertArrayHasKey( 'conditions', $config );
	}
}
