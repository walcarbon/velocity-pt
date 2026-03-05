<?php
/**
 * Plugin Name: Carbon Core
 * Description: Core PT starter functionality (CPTs, ACF, seed tools, shortcodes).
 * Version: 1.0.0
 * Author: Carbon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CARBON_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CARBON_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once CARBON_CORE_PATH . 'includes/class-carbon-core.php';

Carbon_Core::init();
