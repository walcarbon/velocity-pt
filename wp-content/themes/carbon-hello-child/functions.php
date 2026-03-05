<?php
/**
 * Carbon Hello Child functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	static function () {
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'carbon-hello-child' ),
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'carbon-hello-child-style',
			get_stylesheet_uri(),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	},
	20
);
