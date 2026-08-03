<?php
/**
 * Custom block registration.
 *
 * Native blocks whose structural markup stays in git while their content lives
 * in the database (editing surface = block attributes only). Source is in
 * blocks/; @wordpress/scripts compiles it to build/. Each block is registered
 * from its built directory; the registration lines are added as blocks land.
 *
 * @package starter-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sb_register_blocks() {
	// A registration line is added here as each block lands, e.g.:
	// register_block_type( get_template_directory() . '/build/intro-section' );
}
add_action( 'init', 'sb_register_blocks' );
