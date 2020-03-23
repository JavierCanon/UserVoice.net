<?php
	
class AdminFeedbackPlugin {		

	public static function register_feedback() {
		
		$labels = array(
			'name' => __( 'Feedback', 'ay-feedback-user-plugin' ),
			'singular_name' =>  __( 'Feedback', 'ay-feedback-user-plugin' ),
			'add_new' =>  __( 'Add', 'ay-feedback-user-plugin' ),
			'add_new_item' =>  __( 'Add', 'ay-feedback-user-plugin' ),
			'edit_item' =>  __( 'Edit', 'ay-feedback-user-plugin' ),
			'new_item' =>  __( 'New', 'ay-feedback-user-plugin' ),
			'menu_name' =>  __( 'Feedback', 'ay-feedback-user-plugin' ),
		);
	
		$args = array(
			'labels' => $labels,
			'supports' => array( 'title', 'thumbnail', 'editor' ),
			'menu_icon' => 'dashicons-thumbs-up',
			'menu_position' => 11,		
			'public' => true,
			'has_archive' => false,
			'publicly_queryable' => false,
		);
		
		register_post_type( 'feedback', $args );
	
	}
	
	
	public static function feedback_insert_post() {

		if ( ( $_POST['text'] ) != '' && ( $_POST['title'] ) != '' && ( $_POST['link'] ) != '' && ( $_POST['user_id'] ) != '' ) {	
						
			if ( filter_var ($_POST['link'], FILTER_VALIDATE_URL) === false ) {
				wp_send_json_error( __( 'Wrong URL', 'ay-feedback-user-plugin' ) );
			}			
			else {		
		
				$post_information = array(
					'post_title' => wp_strip_all_tags( $_POST['title'] ),
					'post_content' => wp_strip_all_tags( $_POST['text'] ),
					'post_type' => 'feedback',
					'post_status' => 'pending',
				);
				
				$post_id = wp_insert_post( $post_information );
				
				$emailTo = get_option( 'admin_email' );
				$blogname = get_option( 'blogname' );
				$subject = __( 'You have new feedback on site', 'ay-feedback-user-plugin' ) . ': ' . $blogname;
				$message = wp_strip_all_tags( $_POST['text'] );
				$body = $message;
				$headers = '';		    
				wp_mail( $emailTo, $subject, $body, $headers );

				
				wp_update_user( array ( 
					'ID' => $_POST['user_id'], 
					'user_url' => $_POST['link'] 
				) );
				
				wp_send_json_success( __( 'Done! Thank you!', 'ay-feedback-user-plugin' ) );
			
			}
		
		}
		else {			
			wp_send_json_error( __( 'All fields are required', 'ay-feedback-user-plugin' ) );		
		}

	}	
	
	
	public static function feedback_load_more() {

		$args = json_decode( stripslashes( $_POST['query'] ), true );	
		$post_type = 'feedback';
		$args['paged'] = $_POST['page'] + 1;
		$args['post_type'] = $post_type;
	
		query_posts( $args );
	 
		if ( have_posts() ) {
	 
			while( have_posts() ) { 
				
				the_post();
				
				FeedbackShortcode::get_feedback_loop();
				
			}
		}
		wp_reset_query();
		wp_die();
	}
	
}