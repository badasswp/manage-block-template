<?php

namespace ManageBlockTemplate\Tests\Services;

use Mockery;
use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Services\Template;

/**
 * @covers \ManageBlockTemplate\Services\Template::init
 * @covers \ManageBlockTemplate\Services\Template::register_template
 * @covers \ManageBlockTemplate\Services\Template::register
 */
class TemplateTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_register() {
		$template = new Template();

		\WP_Mock::expectActionAdded( 'init', [ $template, 'init' ] );

		$template->register();

		$this->assertConditionsMet();
	}

	public function test_init_does_not_register_any_templates_if_post_types_is_empty() {
		\WP_Mock::userFunction( 'get_option' )
			->andReturn( [] );

		( new Template() )->init();

		$this->assertConditionsMet();
	}

	public function test_init_registers_templates_if_post_types_are_found() {
		\WP_Mock::userFunction( 'get_option' )
			->andReturn(
				[
					'post_types' => [
						'post' => 1,
						'page' => 2,
					],
				]
			);

		\WP_Mock::userFunction( 'post_type_exists' )
			->twice()
			->andReturn( true );

		$template = Mockery::mock( Template::class )->makePartial();
		$template->shouldAllowMockingProtectedMethods();

		$template->shouldReceive( 'register_template' )
			->twice();

		$template->init();

		$this->assertConditionsMet();
	}

	public function test_register_template_fails_if_post_type_object_is_null() {
		$template = Mockery::mock( Template::class )->makePartial();
		$template->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_post_type_object' )
			->andReturnUsing(
				function ( $arg ) {
					return is_null( $arg ) ? null : $arg;
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		$template->register_template( null, 1 );

		$this->assertConditionsMet();
	}

	public function test_register_template_fails_if_ID_is_zero() {
		$template = Mockery::mock( Template::class )->makePartial();
		$template->shouldAllowMockingProtectedMethods();

		$wp_post_type = Mockery::mock( \WP_Post_Type::class )->makePartial();
		$wp_post_type->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_post_type_object' )
			->andReturnUsing(
				function ( $arg ) {
					return is_null( $arg ) ? null : $arg;
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		$template->register_template( $wp_post_type, '0' );

		$this->assertConditionsMet();
	}

	public function test_register_template_fails_if_post_content_is_empty() {
		$template = Mockery::mock( Template::class )->makePartial();
		$template->shouldAllowMockingProtectedMethods();

		$wp_post_type = Mockery::mock( \WP_Post_Type::class )->makePartial();
		$wp_post_type->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_post_type_object' )
			->andReturnUsing(
				function ( $arg ) {
					return is_null( $arg ) ? null : $arg;
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'get_post_field' )
			->andReturn( '' );

		$template->register_template( $wp_post_type, 1 );

		$this->assertConditionsMet();
	}

	public function test_register_template_passes_with_empty_array() {
		$template = Mockery::mock( Template::class )->makePartial();
		$template->shouldAllowMockingProtectedMethods();

		$wp_post_type = Mockery::mock( \WP_Post_Type::class )->makePartial();
		$wp_post_type->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_post_type_object' )
			->andReturnUsing(
				function ( $arg ) {
					return is_null( $arg ) ? null : $arg;
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'get_post_field' )
			->andReturn( 'Hello World!' );

		\WP_Mock::userFunction( 'parse_blocks' )
			->andReturn( [] );

		\WP_Mock::expectFilter( 'manage_block_template_blocks', [], $wp_post_type );

		$template->register_template( $wp_post_type, 1 );

		$this->assertSame( $wp_post_type->template, [] );
		$this->assertSame( $wp_post_type->template_lock, false );
		$this->assertConditionsMet();
	}

	public function test_register_template_passes_with_parsed_blocks_array() {
		$template = Mockery::mock( Template::class )->makePartial();
		$template->shouldAllowMockingProtectedMethods();

		$wp_post_type = Mockery::mock( \WP_Post_Type::class )->makePartial();
		$wp_post_type->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_post_type_object' )
			->andReturnUsing(
				function ( $arg ) {
					return is_null( $arg ) ? null : $arg;
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'get_post_field' )
			->andReturn( 'Hello World!' );

		\WP_Mock::userFunction( 'parse_blocks' )
			->andReturn(
				[
					[
						'blockName' => 'core/paragraph',
						'attrs'     => [
							'placeholder' => 'Type in your text here...',
						],
					],
					[
						'blockName' => '',
					],
					[
						'blockName' => 'core/heading',
						'attrs'     => [
							'textAlign' => 'center',
							'color'     => 'yellow',
						],
					],
				]
			);

		\WP_Mock::expectFilter(
			'manage_block_template_blocks',
			[
				[
					'core/paragraph',
					[
						'placeholder' => 'Type in your text here...',
					],
				],
				[
					'core/heading',
					[
						'placeholder' => '',
						'textAlign'   => 'center',
						'color'       => 'yellow',
					],
				],
			],
			$wp_post_type
		);

		$template->register_template( $wp_post_type, 1 );

		$this->assertSame(
			$wp_post_type->template,
			[
				[
					'core/paragraph',
					[
						'placeholder' => 'Type in your text here...',
					],
				],
				[
					'core/heading',
					[
						'placeholder' => '',
						'textAlign'   => 'center',
						'color'       => 'yellow',
					],
				],
			]
		);
		$this->assertSame( $wp_post_type->template_lock, false );
		$this->assertConditionsMet();
	}
}
