<?php
/**
 * MBT Class.
 *
 * This class defines the MBT CPT
 * for the plugin.
 *
 * @package ManageBlockTemplate
 */

namespace ManageBlockTemplate\Posts;

use ManageBlockTemplate\Abstracts\Post;
use ManageBlockTemplate\Services\Admin;

class MBT extends Post {
	/**
	 * Post type.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $name = 'mbt';

	/**
	 * Get Singular Label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_singular_label(): string {
		return 'Template';
	}

	/**
	 * Get Plural Label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_plural_label(): string {
		return 'Templates';
	}

	/**
	 * Get Support options.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	protected function get_supports(): array {
		return [ 'title', 'thumbnail', 'editor' ];
	}

	/**
	 *
	 * Slug on rewrite.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug(): string {
		return 'mbt';
	}

	/**
	 * Is Post visible in REST.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_post_visible_in_rest(): bool {
		return true;
	}

	/**
	 * Is Post visible in Menu.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|string
	 */
	protected function is_post_visible_in_menu() {
		return Admin::PLUGIN_SLUG;
	}

	/**
	 * Get Post meta schema.
	 *
	 * This method should return an array of key value pairs representing
	 * the post meta schema for the custom post type.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_post_meta_schema(): array {
		$blocks           = parse_blocks( get_post_field( 'post_content', get_the_ID() ) );
		$number_of_blocks = 0;

		$blocks = array_reduce(
			$blocks,
			function ( $carry, $block ) use ( &$number_of_blocks ) {
				if ( ! $block['blockName'] ) {
					return $carry;
				}

				$carry .= sprintf(
					'<span style="margin: 0; display: block;">%s</span>',
					esc_html( $block['blockName'] ?? '' )
				);

				$number_of_blocks++;

				return $carry;
			},
			''
		);

		return [
			'blocks'                 => [
				'label'   => esc_html__( 'Blocks', 'manage-block-template' ),
				'value'   => $blocks,
				'type'    => 'string',
				'default' => '',
			],
			'total_number_of_blocks' => [
				'label'   => esc_html__( 'Total Number of Blocks', 'manage-block-template' ),
				'value'   => $number_of_blocks,
				'type'    => 'integer',
				'default' => 0,
			],
		];
	}

	/**
	 * Save Post Type.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    WP Post.
	 * @return void
	 */
	public function save_post_type( $post_id, $post ): void {}

	/**
	 * Delete Post Type.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    WP Post.
	 * @return void
	 */
	public function delete_post_type( $post_id, $post ): void {}
}
