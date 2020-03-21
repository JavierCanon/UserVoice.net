<?php
	
class FeedbackPlugin {	
	
	function __construct() {
		$this->load_dependencies();
		add_action( 'wp_enqueue_scripts', array( $this, 'ay_fp_scripts' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );	
		add_action( 'init', array( 'AdminFeedbackPlugin', 'register_feedback' ) );			
		add_action( 'wp_ajax_nopriv_feedback_insert_post', array( 'AdminFeedbackPlugin', 'feedback_insert_post' ) );
		add_action( 'wp_ajax_feedback_insert_post', array( 'AdminFeedbackPlugin', 'feedback_insert_post') );
		add_action( 'wp_ajax_nopriv_feedback_load_more', array( 'AdminFeedbackPlugin', 'feedback_load_more' ) );
		add_action( 'wp_ajax_feedback_load_more', array( 'AdminFeedbackPlugin', 'feedback_load_more') );
		add_shortcode( 'ay-feedback', array( 'FeedbackShortcode', 'feedback_shortcode' ) );
	}
	
	
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'ay-feedback-user-plugin', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}
	
	
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-admin-feedback-plugin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/class-feedback-shortcode-plugin.php';
	}
				
	
	function ay_fp_scripts() {			
		wp_enqueue_style( 'ay-feedback-user-plugin', plugin_dir_url( dirname( __FILE__ ) ) . 'css/ay-feedback-user-plugin.css', null, '1.0.1' );
		wp_enqueue_script( 'ay-feedback-user-plugin', plugin_dir_url( dirname( __FILE__ ) ) . '/js/ay-feedback-user-plugin.js', array( 'jquery' ), '1.0.1', true );
		wp_localize_script( 'ay-feedback-user-plugin', 'php_data', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
	}
					
}