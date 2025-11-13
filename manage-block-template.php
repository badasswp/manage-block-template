<?php
/**
 * Plugin Name: Manage Block Template
 * Plugin URI:  https://github.com/badasswp/manage-block-template
 * Description: A simple plugin to manage block templates easily.
 * Version:     1.0.1
 * Author:      badasswp
 * Author URI:  https://github.com/badasswp
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: manage-block-template
 * Domain Path: /languages
 *
 * @package ManageBlockTemplate
 */

namespace badasswp\ManageBlockTemplate;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

define( 'MANAGE_BLOCK_TEMPLATE_AUTOLOAD', __DIR__ . '/vendor/autoload.php' );

// Composer Check.
if ( ! file_exists( MANAGE_BLOCK_TEMPLATE_AUTOLOAD ) ) {
	add_action(
		'admin_notices',
		function () {
			vprintf(
				/* translators: Plugin directory path. */
				esc_html__( 'Fatal Error: Composer not setup in %s', 'manage-block-template' ),
				[ __DIR__ ]
			);
		}
	);

	return;
}

// Run Plugin.
require_once MANAGE_BLOCK_TEMPLATE_AUTOLOAD;
( \ManageBlockTemplate\Plugin::get_instance() )->run();
