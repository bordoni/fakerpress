<?php
namespace FakerPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load Composer Vendor Modules
require_once plugin_dir_path( __FP_FILE__ ) . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Create the Plugin static instance
$FakerPress = new Plugin;

// Require our Administration Class
Plugin::$admin = new Admin;;

// Require our Ajax Class
Plugin::$ajax = new Ajax;

