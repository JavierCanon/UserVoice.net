<?php
/**
* Plugin Name: AY Feedback User Plugin
* Description: This plugin allow your website logged in visitors to leave feedback via front-end and ajax
* Plugin URI:  https://yarovikov.com/
* Author URI:  https://yarovikov.com/
* Author:      Alexandr Yarovikov
* Text Domain: ay-feedback-user-plugin
* Domain Path: /languages
* License:     GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Version:     1.0.1
*/


defined( 'ABSPATH' ) || exit;


require plugin_dir_path( __FILE__ ) . 'inc/class-feedback-plugin.php';


if ( class_exists( 'FeedbackPlugin' ) ) {
	$FeedbackPlugin = new FeedbackPlugin();
}