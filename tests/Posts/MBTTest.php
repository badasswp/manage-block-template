<?php

namespace ManageBlockTemplate\Tests\Posts;

use Mockery;
use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Posts\MBT;

/**
 * @covers \ManageBlockTemplate\Posts\MBT::get_instance
 */
class MBTTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_mbt_post_type_returns_name() {
		$this->assertSame( 'mbt', MBT::$name );
	}
}
