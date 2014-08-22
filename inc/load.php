<?php
namespace FakerPress;

// Main Class for the plugin, last file loaded like that
require_once plugin_dir_path( __FP_FILE__ ) . 'classes' . DIRECTORY_SEPARATOR . 'plugin.php';

// Create the Plugin static instance
$FakerPress = new Plugin;

// Include the Faker Main Class
require_once Plugin::path( 'inc/vendor/Faker/src/autoload.php' );

// Include the Carbon class to deal with dates
require_once Plugin::path( 'inc/vendor/Carbon/Carbon.php' );

// Include the Carbon class to deal with dates
require_once Plugin::path( 'classes/class-fp-dates.php' );

// Require our Administration Class
require_once Plugin::path( 'classes/filter.php' );

// Require our Administration Class
require_once Plugin::path( 'classes/admin.php' );

// Inluding needed providers
require_once Plugin::path( 'providers/html.php' );
require_once Plugin::path( 'providers/placeholdit.php' );

// Require the Base module
require_once Plugin::path( 'modules/base.php' );

// Require the Post module
require_once Plugin::path( 'providers/wp-post.php' );
require_once Plugin::path( 'modules/post.php' );
require_once Plugin::path( 'inc/init/posts.php' );

// Require the User module
require_once Plugin::path( 'providers/wp-user.php' );
require_once Plugin::path( 'modules/user.php' );
require_once Plugin::path( 'inc/init/users.php' );

// Require the Term module
require_once Plugin::path( 'providers/wp-term.php' );
require_once Plugin::path( 'modules/term.php' );
require_once Plugin::path( 'inc/init/terms.php' );

// Require the Comment module
require_once Plugin::path( 'providers/wp-comment.php' );
require_once Plugin::path( 'modules/comment.php' );
require_once Plugin::path( 'inc/init/comments.php' );

// Load the Settings page actions
require_once Plugin::path( 'inc/init/settings.php' );

// Initialize the main Class of the plugin Administration
Plugin::$admin = new Admin;

