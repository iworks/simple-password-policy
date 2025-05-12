<?php
class Test_Simple_Password_Policy_Base extends WP_UnitTestCase {

	protected $base;

	public function setUp(): void {
		parent::setUp();
		$this->base = $this->getMockForAbstractClass( 'Simple_Password_Policy_Base' );
	}

	public function test_get_version_returns_string() {
		$version = $this->base->get_version();
		$this->assertIsString( $version );
	}

	public function test_slug_name_format() {
		$slug = $this->base->slug_name( 'test_name' );
		$this->assertStringContainsString( 'test_name', $slug );
	}
}

