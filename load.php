<?php
namespace FakerPress;

// Load Composer Vendor Modules
require_once plugin_dir_path( __FP_FILE__ ) . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

require_once 'src/functions/array.php';
require_once 'src/functions/globals.php';
require_once 'src/functions/filter.php';
require_once 'src/functions/conditionals.php';
require_once 'src/functions/sorting.php';
require_once 'src/functions/assets.php';

// Create the Plugin static instance
$FakerPress = new Plugin;

// Require our Administration Class
Plugin::$admin = new Admin;

// Require our Ajax Class
Plugin::$ajax = new Ajax;