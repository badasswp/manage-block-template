<?php
/**
 * Admin Service.
 *
 * This service manages the admin area of the
 * plugin. It provides functionality for registering
 * the plugin options/settings.
 *
 * @package ManageBlockTemplate
 */

namespace ManageBlockTemplate\Services;

use ManageBlockTemplate\Abstracts\Service;
use ManageBlockTemplate\Interfaces\Kernel;

class Admin extends Service implements Kernel {
	/**
	 * Plugin Option.
	 *
	 * @var string
	 */
	const PLUGIN_SLUG = 'manage-block-template';

	/**
	 * Plugin Option.
	 *
	 * @var string
	 */
	const PLUGIN_OPTION = 'manage_block_template';

	/**
	 * Plugin Group.
	 *
	 * @var string
	 */
	const PLUGIN_GROUP = 'manage-block-template-group';

	/**
	 * Plugin Section.
	 *
	 * @var string
	 */
	const PLUGIN_SECTION = 'manage-block-template-section';

	/**
	 * Plugin Options.
	 *
	 * @var mixed[]
	 */
	public array $options;

	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_options_page' ] );
		add_action( 'admin_init', [ $this, 'register_options_init' ] );
	}

	/**
	 * Register Options Page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_page(): void {
		add_menu_page(
			esc_html__( 'Manage Block Template', 'manage-block-template' ),
			esc_html__( 'Manage Block Template', 'manage-block-template' ),
			'manage_options',
			self::PLUGIN_SLUG,
			null,
			'dashicons-admin-customizer',
			100
		);

		add_submenu_page(
			self::PLUGIN_SLUG,
			esc_html__( 'Settings', 'manage-block-template' ),
			esc_html__( 'Settings', 'manage-block-template' ),
			'manage_options',
			self::PLUGIN_SLUG,
			[ $this, 'register_options_cb' ],
		);
	}

	/**
	 * Register Options Callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_cb(): void {
		$this->options = get_option( self::PLUGIN_OPTION, [] )['post_types'] ?? [];

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Manage Block Template', 'manage-block-template' ); ?></h1>
			<p><?php esc_html_e( 'A simple plugin to manage block templates easily.', 'manage-block-template' ); ?></p>
			<form method="post" action="options.php">
			<?php
				settings_fields( self::PLUGIN_GROUP );
				do_settings_sections( self::PLUGIN_SLUG );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register Options Init.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_init(): void {
		register_setting(
			self::PLUGIN_GROUP,
			self::PLUGIN_OPTION,
			[ $this, 'sanitize_options' ]
		);

		foreach ( $this->get_sections() as $section ) {
			add_settings_section(
				$section['name'] ?? '',
				$section['label'] ?? '',
				null,
				self::PLUGIN_SLUG
			);
		}

		foreach ( $this->get_options() as $option ) {
			if ( ! isset( $option['name'] ) || ! isset( $option['cb'] ) || ! is_callable( $option['cb'] ) ) {
				continue;
			}

			add_settings_field(
				$option['name'] ?? '',
				$option['label'] ?? '',
				$option['cb'],
				$option['page'] ?? '',
				$option['section'] ?? ''
			);
		}
	}

	/**
	 * Get Form Sections.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_sections(): array {
		return [
			[
				'name'  => self::PLUGIN_SECTION,
				'label' => esc_html__( 'Settings', 'manage-block-template' ),
			],
		];
	}

	/**
	 * Get Callback name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Form Control name.
	 * @return string
	 */
	protected function get_callback_name( $name ): string {
		return sprintf( '%s_cb', $name );
	}

	/**
	 * Get Plugin Options.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_options(): array {
		$options = array_map(
			function ( $post_type ) {
				return [
					'name'    => $post_type,
					'label'   => esc_html__( ucwords( sprintf( '%s Template', $post_type ) ), 'manage-block-template' ),
					'cb'      => [ $this, 'template_cb_' . $post_type ],
					'page'    => self::PLUGIN_SLUG,
					'section' => self::PLUGIN_SECTION,
				];
			},
			$this->get_allowed_post_types()
		);

		/**
		 * Filter Option Fields.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed[] $options Option Fields.
		 * @return mixed[]
		 */
		return apply_filters( 'manage_block_template_admin_fields', $options );
	}

	/**
	 * Call magic method.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $method Method name e.g. 'template_cb_post'.
	 * @param mixed[] $args   Method args.
	 *
	 * @return void
	 */
	protected function __call( $method, $args ) {
		$method    = explode( '_', $method );
		$post_type = array_pop( $method );

		if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
			return;
		}

		$mbt_ids = wp_list_pluck( get_posts( [ 'post_type' => 'mbt' ] ), 'ID' );

		foreach ( $mbt_ids as $post_id ) {
			$selected = '';

			if ( ( $this->options[ $post_type ] ?? '' ) === $post_id ) {
				$selected = 'selected';
			}

			$options .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $post_id ),
				esc_attr( $selected ),
				esc_html( get_post_field( 'post_title', absint( $post_id ) ) ),
			);
		}

		printf(
			'<select
				id="%2$s"
				name="%1$s[post_types][%2$s]"
				value="%3$s"
			>%4$s</select>',
			esc_attr( self::PLUGIN_OPTION ),
			esc_attr( $post_type ),
			esc_attr( $this->options[ $post_type ] ?? '' ),
			$options
		);
	}

	/**
	 * Sanitize Options.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $input Plugin Options.
	 * @return mixed[]
	 */
	public function sanitize_options( $input ): array {
		$sanitized_options = [];

		foreach ( $this->get_allowed_post_types() as $post_type ) {
			if ( isset( $input['post_types'][ $post_type ] ) && is_numeric( $input['post_types'][ $post_type ] ) ) {
				$sanitized_options['post_types'][ $post_type ] = absint( $input['post_types'][ $post_type ] );
			} else {
				$sanitized_options['post_types'][ $post_type ] = '';
			}
		}

		return $sanitized_options;
	}

	/**
	 * Get Allowed Post Types.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	protected function get_allowed_post_types(): array {
		$post_types = get_post_types( [ 'public' => true ], 'names' );
		$post_types = array_diff( $post_types, [ 'mbt', 'attachment' ] );

		/**
		 * Filter Post types.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $post_types Post types.
		 * @return string[]
		 */
		return apply_filters( 'manage_block_template_post_types', $post_types );
	}
}
