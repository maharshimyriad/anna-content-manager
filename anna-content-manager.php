<?php
/**
 * Plugin Name: Anna Content Manager
 * Description: Classic editor page content management for Anna Baylis theme sections.
 * Version: 0.1.0
 * Author: Anna Baylis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ANNA_CONTENT_MANAGER_VERSION', '0.1.0' );
define( 'ANNA_CONTENT_MANAGER_FILE', __FILE__ );
define( 'ANNA_CONTENT_MANAGER_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANNA_CONTENT_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once ANNA_CONTENT_MANAGER_DIR . 'includes/class-anna-content-manager.php';

Anna_Content_Manager::instance();

register_activation_hook( ANNA_CONTENT_MANAGER_FILE, array( 'Anna_Content_Manager', 'activate' ) );
