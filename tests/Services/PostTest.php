<?php

namespace ManageBlockTemplate\Tests\Services;

use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Posts\MBT;
use ManageBlockTemplate\Services\Post;

/**
 * @covers \ManageBlockTemplate\Services\Post::__construct
 * @covers \ManageBlockTemplate\Services\Post::register
 * @covers \ManageBlockTemplate\Services\Post::register_post_types
 * @covers \ManageBlockTemplate\Abstracts\Post::__construct
 * @covers \ManageBlockTemplate\Abstracts\Post::get_labels
 * @covers \ManageBlockTemplate\Abstracts\Post::get_name
 * @covers \ManageBlockTemplate\Abstracts\Post::get_options
 * @covers \ManageBlockTemplate\Abstracts\Post::register_post_type
 * @covers \ManageBlockTemplate\Posts\MBT::get_plural_label
 * @covers \ManageBlockTemplate\Posts\MBT::get_singular_label
 * @covers \ManageBlockTemplate\Posts\MBT::get_slug
 * @covers \ManageBlockTemplate\Posts\MBT::get_supports
 * @covers \ManageBlockTemplate\Posts\MBT::is_post_visible_in_menu
 * @covers \ManageBlockTemplate\Posts\MBT::is_post_visible_in_rest
 */
class PostTest extends TestCase {
	private $post;

	public function setUp(): void {
		\WP_Mock::setUp();

		\WP_Mock::expectFilter(
			'manage_block_template_cpts',
			[
				MBT::class,
			]
		);

		\WP_Mock::userFunction( 'wp_kses' )
			->andReturnUsing(
				function ( $arg1, $arg2 ) {
					$dom = new \DOMDocument();

					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					@$dom->loadHTML( $arg1 );

					// phpcs:ignore PEAR.Functions.FunctionCallSignature.SpaceAfterOpenBracket
					$elements = $dom->getElementsByTagName( '*' );

					$tags = [];

					foreach ( $elements as $el ) {
						// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$tags[] = $el->tagName;
					}

					$html_tags = array_values( array_diff( array_unique( $tags ), [ 'html', 'body' ] ) );

					if ( ! empty( array_diff( array_keys( $arg2 ), $html_tags ) ) ) {
						return '';
					}

					return $arg1;
				}
			);

		$this->post = new Post();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_register() {
		\WP_Mock::expectActionAdded( 'init', [ $this->post, 'register_post_types' ] );

		\WP_Mock::expectFilterAdded(
			'manage_mbt_posts_columns',
			[ $this->post->objects[0], 'register_post_column_labels' ],
			10,
			1
		);

		\WP_Mock::expectFilterAdded(
			'manage_edit-mbt_sortable_columns',
			[ $this->post->objects[0], 'register_post_sortable_columns' ],
			10,
			1
		);

		\WP_Mock::expectActionAdded(
			'manage_mbt_posts_custom_column',
			[ $this->post->objects[0], 'register_post_column_data' ],
			10,
			2
		);

		\WP_Mock::expectActionAdded(
			'publish_mbt',
			[ $this->post->objects[0], 'save_post_type' ],
			10,
			2
		);

		\WP_Mock::expectActionAdded(
			'wp_trash_post',
			[ $this->post->objects[0], 'delete_post_type' ],
			10,
			2
		);

		\WP_Mock::expectActionAdded(
			'pre_get_posts',
			[ $this->post->objects[0], 'register_post_column_sorting' ],
			10,
			1
		);

		$this->post->register();

		$this->assertConditionsMet();
	}

	public function test_register_post_types() {
		$labels = [
			'name'          => 'Templates',
			'singular_name' => 'Template',
			'menu_name'     => 'Templates',
			'add_new'       => 'Add New Template',
			'add_new_item'  => 'Add New Template',
			'new_item'      => 'New Template',
			'edit_item'     => 'Edit Template',
			'view_item'     => 'View Template',
			'search_items'  => 'Search Templates',
		];

		\WP_Mock::userFunction( 'post_type_exists' )
			->with( 'mbt' )
			->andReturn( false );

		\WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		\WP_Mock::userFunction( 'register_post_type' )
			->with(
				'mbt',
				[
					'name'         => 'mbt',
					'labels'       => $labels,
					'supports'     => [ 'title', 'thumbnail', 'editor' ],
					'show_in_rest' => true,
					'show_in_menu' => 'manage-block-template',
					'public'       => true,
					'rewrite'      => [
						'slug' => 'mbt',
					],
				]
			);

		\WP_Mock::expectFilter(
			'manage_block_template_post_options',
			[
				'name'         => 'mbt',
				'labels'       => $labels,
				'supports'     => [ 'title', 'thumbnail', 'editor' ],
				'show_in_rest' => true,
				'show_in_menu' => 'manage-block-template',
				'public'       => true,
				'rewrite'      => [
					'slug' => 'mbt',
				],
			]
		);

		$this->post->register_post_types();

		$this->assertConditionsMet();
	}
}
