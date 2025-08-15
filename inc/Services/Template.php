<?php
/**
 * Template Service.
 *
 * This service manages the registration and
 * binding of the Template service.
 *
 * @package ManageBlockTemplate
 */

namespace ManageBlockTemplate\Services;

use ManageBlockTemplate\Abstracts\Service;
use ManageBlockTemplate\Interfaces\Kernel;

class Template extends Service implements Kernel {
	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the template.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$post_types = get_option( 'manage_block_template', [] )['post_types'] ?? [];

		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type => $mbt_post_id ) {
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			$this->register_template( $post_type, $mbt_post_id );
		}
	}

	/**
	 * Register a template for a specific post type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type   WP post type.
	 * @param int    $mbt_post_id MBT Post ID.
	 *
	 * @return void
	 */
	protected function register_template( $post_type, $mbt_post_id ): void {
		$post_type_object = get_post_type_object( $post_type );
		$mbt_post_id      = absint( $mbt_post_id );

		if ( ! $post_type_object || ! $mbt_post_id ) {
			return;
		}

		$post_content = get_post_field( 'post_content', $mbt_post_id );

		if ( empty( $post_content ) ) {
			return;
		}

		$blocks = array_map(
			function ( $block ) {
				$name        = $block['blockName'] ?? '';
				$placeholder = $block['attrs']['placeholder'] ?? '';

				return [
					$name,
					[
						'placeholder' => $placeholder,
						...$block['attrs'] ?? [],
					],
				];
			},
			parse_blocks( $post_content )
		);

		$blocks = array_filter(
			$blocks,
			function ( $block ) {
				return ! empty( $block[0] );
			}
		);

		/**
		 * Filter the template blocks for the post type.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed[] $blocks    WP blocks.
		 * @param string  $post_type The post type.
		 *
		 * @return mixed[]
		 */
		$post_type_object->template = apply_filters( 'manage_block_template_blocks', array_values( $blocks ), $post_type );

		// Optional: lock the template to prevent block removal
		$post_type_object->template_lock = false;
	}
}
