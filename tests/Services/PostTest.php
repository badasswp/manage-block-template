<?php

namespace ManageBlockTemplate\Tests\Services;

use stdClass;
use WP_Mock\Tools\TestCase;
use ManageBlockTemplate\Posts\MBT;
use ManageBlockTemplate\Services\Post;

/**
 * @covers \ManageBlockTemplate\Services\Post::register
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
}
