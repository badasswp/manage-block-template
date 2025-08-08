<?php

namespace ManageBlockTemplate\Tests\Posts;

use Mockery;
use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Posts\MBT;

/**
 * @covers \ManageBlockTemplate\Posts\MBT::get_singular_label
 * @covers \ManageBlockTemplate\Posts\MBT::get_plural_label
 * @covers \ManageBlockTemplate\Posts\MBT::get_supports
 * @covers \ManageBlockTemplate\Posts\MBT::is_post_visible_in_rest
 * @covers \ManageBlockTemplate\Posts\MBT::is_post_visible_in_menu
 */
class MBTTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_mbt_post_type_returns_singular_label() {
		$mbt = Mockery::mock( MBT::class )->makePartial();
		$mbt->shouldAllowMockingProtectedMethods();

		$this->assertSame( 'Template', $mbt->get_singular_label() );
	}

	public function test_mbt_post_type_returns_plural_label() {
		$mbt = Mockery::mock( MBT::class )->makePartial();
		$mbt->shouldAllowMockingProtectedMethods();

		$this->assertSame( 'Templates', $mbt->get_plural_label() );
	}

	public function test_mbt_post_type_returns_supports_params() {
		$mbt = Mockery::mock( MBT::class )->makePartial();
		$mbt->shouldAllowMockingProtectedMethods();

		$this->assertSame(
			[
				'title',
				'thumbnail',
				'editor',
			],
			$mbt->get_supports()
		);
	}

	public function test_is_post_visible_in_rest() {
		$mbt = Mockery::mock( MBT::class )->makePartial();
		$mbt->shouldAllowMockingProtectedMethods();

		$this->assertSame( true, $mbt->is_post_visible_in_rest() );
	}

	public function test_is_post_visible_in_menu() {
		$mbt = Mockery::mock( MBT::class )->makePartial();
		$mbt->shouldAllowMockingProtectedMethods();

		$this->assertSame( 'manage-block-template', $mbt->is_post_visible_in_menu() );
	}
}
