<?php

namespace ManageBlockTemplate\Tests\Services;

use Mockery;
use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Services\Admin;

/**
 * @covers \ManageBlockTemplate\Services\Admin::register
 * @covers \ManageBlockTemplate\Services\Admin::register_options_page
 * @covers \ManageBlockTemplate\Services\Admin::register_options_init
 * @covers \ManageBlockTemplate\Services\Admin::get_sections
 * @covers \ManageBlockTemplate\Services\Admin::get_options
 * @covers \ManageBlockTemplate\Services\Admin::sanitize_options
 * @covers \ManageBlockTemplate\Services\Admin::get_allowed_post_types
 * @covers \ManageBlockTemplate\Services\Admin::get_callback_name
 */
class AdminTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_register() {
		$admin = new Admin();

		\WP_Mock::expectActionAdded( 'admin_menu', [ $admin, 'register_options_page' ] );
		\WP_Mock::expectActionAdded( 'admin_init', [ $admin, 'register_options_init' ] );

		$register = $admin->register();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_register_options_page() {
		$admin = new Admin();

		\WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'add_menu_page' )
			->with(
				'Manage Block Template',
				'Manage Block Template',
				'manage_options',
				'manage-block-template',
				null,
				'dashicons-align-wide',
				100
			)
			->andReturn( null );

		\WP_Mock::userFunction( 'add_submenu_page' )
			->with(
				'manage-block-template',
				'Settings',
				'Settings',
				'manage_options',
				'manage-block-template',
				[ $admin, 'register_options_cb' ],
			)
			->andReturn( null );

		$register = $admin->register_options_page();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_register_options_init() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$admin->shouldReceive( 'get_sections' )
			->andReturn(
				[
					[
						'name'  => 'manage-block-template-section',
						'label' => 'Settings',
					],
				]
			);

		$admin->shouldReceive( 'get_options' )
			->andReturn(
				[
					[
						'name'    => 'post',
						'label'   => 'Post Template',
						'cb'      => [ $admin, 'template_cb_post' ],
						'page'    => 'manage-block-template',
						'section' => 'manage-block-template-section',
					],
					[
						'name'    => 'page',
						'label'   => 'Page Template',
						'cb'      => [ $admin, 'template_cb_page' ],
						'page'    => 'manage-block-template',
						'section' => 'manage-block-template-section',
					],
				]
			);

		\WP_Mock::userFunction( 'register_setting' )
			->with(
				'manage-block-template-group',
				'manage_block_template',
				[ $admin, 'sanitize_options' ]
			)
			->andReturn( null );

		\WP_Mock::userFunction( 'add_settings_section' )
			->once()
			->with(
				'manage-block-template-section',
				'Settings',
				null,
				'manage-block-template'
			)
			->andReturn( null );

		\WP_Mock::userFunction( 'add_settings_field' )
			->times( 2 );

		$register = $admin->register_options_init();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_get_sections() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$sections = $admin->get_sections();

		$this->assertSame(
			$sections,
			[
				[
					'name'  => 'manage-block-template-section',
					'label' => 'Settings',
				],
			]
		);
	}

	public function test_get_callback_name() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$this->assertSame(
			'name-of-control_cb',
			$admin->get_callback_name( 'name-of-control' )
		);
	}

	public function test_get_options() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$admin->shouldReceive( 'get_allowed_post_types' )
			->andReturn( [ 'post', 'page' ] );

		$options = [
			[
				'name'    => 'post',
				'label'   => 'Post Template',
				'cb'      => [ $admin, 'template_cb_post' ],
				'page'    => 'manage-block-template',
				'section' => 'manage-block-template-section',
			],
			[
				'name'    => 'page',
				'label'   => 'Page Template',
				'cb'      => [ $admin, 'template_cb_page' ],
				'page'    => 'manage-block-template',
				'section' => 'manage-block-template-section',
			],
		];

		\WP_Mock::userFunction( 'esc_html' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::expectFilter(
			'manage_block_template_admin_fields',
			$options
		);

		$this->assertSame( $options, $admin->get_options() );
		$this->assertConditionsMet();
	}

	public function test_template_cb_post_bails_out_if_post_does_not_exist() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'post_type_exists' )
			->andReturn( false );

		$admin->template_cb_post();

		$this->assertConditionsMet();
	}

	public function test_template_cb_post_prints_select_control_with_post_mapped_to_template() {
		$admin                  = new Admin();
		$admin->options['post'] = 1;

		$post1 = Mockery::mock( \WP_Post::class )->makePartial();
		$post2 = Mockery::mock( \WP_Post::class )->makePartial();

		$post1->ID = 1;
		$post2->ID = 2;

		\WP_Mock::userFunction( 'post_type_exists' )
			->andReturn( true );

		\WP_Mock::userFunction( 'get_posts' )
			->andReturn( [ $post1, $post2 ] );

		\WP_Mock::userFunction( 'wp_list_pluck' )
			->andReturnUsing(
				function ( $arg ) {
					return array_map(
						function ( $post_obj ) {
							return $post_obj->ID;
						},
						$arg
					);
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'get_post_field' )
			->andReturnUsing(
				function ( $arg1, $arg2 ) {
					$title = '';

					switch ( $arg2 ) {
						case 1:
							$title = 'My Custom Template 1';
							break;

						case 2:
							$title = 'My Custom Template 2';
							break;
					}

					return $title;
				}
			);

		\WP_Mock::userFunction( 'esc_html' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'esc_attr' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$this->expectOutputString(
			'<select
				id="post"
				name="manage_block_template[post_types][post]"
				value="1"
			><option value="0" >None</option><option value="1" selected>My Custom Template 1</option><option value="2" >My Custom Template 2</option></select>',
		);

		$admin->template_cb_post();

		$this->assertConditionsMet();
	}

	public function test_template_cb_post_prints_select_control_pointing_to_none() {
		$admin                  = new Admin();
		$admin->options['post'] = 0;

		$post1 = Mockery::mock( \WP_Post::class )->makePartial();
		$post2 = Mockery::mock( \WP_Post::class )->makePartial();

		$post1->ID = 1;
		$post2->ID = 2;

		\WP_Mock::userFunction( 'post_type_exists' )
			->andReturn( true );

		\WP_Mock::userFunction( 'get_posts' )
			->andReturn( [ $post1, $post2 ] );

		\WP_Mock::userFunction( 'wp_list_pluck' )
			->andReturnUsing(
				function ( $arg ) {
					return array_map(
						function ( $post_obj ) {
							return $post_obj->ID;
						},
						$arg
					);
				}
			);

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		\WP_Mock::userFunction( 'get_post_field' )
			->andReturnUsing(
				function ( $arg1, $arg2 ) {
					$title = '';

					switch ( $arg2 ) {
						case 1:
							$title = 'My Custom Template 1';
							break;

						case 2:
							$title = 'My Custom Template 2';
							break;
					}

					return $title;
				}
			);

		\WP_Mock::userFunction( 'esc_html' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'esc_attr' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$this->expectOutputString(
			'<select
				id="post"
				name="manage_block_template[post_types][post]"
				value="0"
			><option value="0" selected>None</option><option value="1" >My Custom Template 1</option><option value="2" >My Custom Template 2</option></select>',
		);

		$admin->template_cb_post();

		$this->assertConditionsMet();
	}

	public function test_sanitize_options_does_not_sanitize_any_control_if_not_set() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$admin->shouldReceive( 'get_allowed_post_types' )
			->andReturn( [] );

		$sanitized_options = $admin->sanitize_options(
			[
				'post_types' => [
					'post' => '1',
					'page' => '2',
				],
			]
		);

		$this->assertSame( $sanitized_options, [] );
		$this->assertConditionsMet();
	}

	public function test_sanitize_options_sanitizes_only_controls_that_are_set() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$admin->shouldReceive( 'get_allowed_post_types' )
			->andReturn( [ 'post', 'page' ] );

		\WP_Mock::userFunction( 'absint' )
			->andReturnUsing(
				function ( $arg ) {
					return intval( $arg );
				}
			);

		$sanitized_options = $admin->sanitize_options(
			[
				'post_types' => [
					'post' => '1',
					'page' => '2',
				],
			]
		);

		$this->assertSame(
			$sanitized_options,
			[
				'post_types' => [
					'post' => 1,
					'page' => 2,
				],
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_allowed_post_types() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_post_types' )
			->andReturn( [ 'post', 'page', 'mbt', 'attachment' ] );

		\WP_Mock::expectFilter( 'manage_block_template_post_types', [ 'post', 'page' ] );

		$admin->get_allowed_post_types();

		$this->assertConditionsMet();
	}
}
