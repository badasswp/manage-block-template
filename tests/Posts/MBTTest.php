<?php

namespace ManageBlockTemplate\Tests\Posts;

use WP_Mock;
use Mockery;
use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Posts\MBT;

/**
 * @covers \ManageBlockTemplate\Posts\MBT::get_singular_label
 * @covers \ManageBlockTemplate\Posts\MBT::get_plural_label
 * @covers \ManageBlockTemplate\Posts\MBT::get_supports
 * @covers \ManageBlockTemplate\Posts\MBT::is_post_visible_in_rest
 * @covers \ManageBlockTemplate\Posts\MBT::is_post_visible_in_menu
 * @covers \ManageBlockTemplate\Posts\MBT::get_post_meta_schema
 */
class MBTTest extends TestCase {
	public function setUp(): void {
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
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

	public function test_get_post_meta_schema() {
		WP_Mock::userFunction( 'esc_html' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg1, $arg2 ) {
					return $arg1;
				}
			);

		WP_Mock::userFunction( 'get_the_ID' )
			->andReturn( 1 );

		WP_Mock::userFunction( 'get_post_field' )
			->with( 'post_content', 1 )
			->andReturn( 'Hello World' );

		WP_Mock::userFunction( 'parse_blocks' )
			->with( 'Hello World' )
			->andReturn(
				[
					[ 'blockName' => 'core/paragraph' ],
					[ 'blockName' => 'core/image' ],
					[ 'blockName' => null ],
					[ 'blockName' => 'core/blockquote' ],
				]
			);

		$mbt = Mockery::mock( MBT::class )->makePartial();
		$mbt->shouldAllowMockingProtectedMethods();

		$this->assertSame(
			[
				'blocks'                 => [
					'label'   => 'Blocks',
					'value'   => '<p style="margin: 0;">core/paragraph</p><p style="margin: 0;">core/image</p><p style="margin: 0;">core/blockquote</p>',
					'type'    => 'string',
					'default' => '',
				],
				'total_number_of_blocks' => [
					'label'   => 'Total Number of Blocks',
					'value'   => 3,
					'type'    => 'integer',
					'default' => 0,
				],
			],
			$mbt->get_post_meta_schema()
		);
	}
}
