<?php
/**
 * Kernel Interface
 *
 * Establish base methods for Concrete services
 * used across plugin.
 *
 * @package ManageBlockTemplate
 */

namespace ManageBlockTemplate\Interfaces;

interface Kernel {
	/**
	 * Register logic.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void;
}
