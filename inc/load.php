<?php
namespace FakerPress;

// Main Class for the plugin, last file loaded like that
require_once plugin_dir_path( __FP_FILE__ ) . 'classes' . DIRECTORY_SEPARATOR . 'plugin.php';

// Create the Plugin static instance
$FakerPress = new Plugin;

// Require our Administration Class
require_once Plugin::path( 'classes/admin.php' );

// Initialize the main Class of the plugin Administration
Plugin::$admin = new Admin;
