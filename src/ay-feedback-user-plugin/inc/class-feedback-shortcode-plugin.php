<?php

class FeedbackShortcode {
	
	// Display feedback form for logged in users
	public static function feedback_form() {
		
		echo '<div class="feedback-container">';
	
		if ( is_user_logged_in() ) { 
			
			$current_user = wp_get_current_user(); 
			
			$args = array(
				'post_status' => array( 'publish', 'pending' ),
				'post_type'  => 'feedback',
				'author'     =>  $current_user->ID,
			);
	
			$wp_posts = get_posts( $args );
			
			if ( count( $wp_posts ) ) {
				echo '<div class="feedback-notice">' . __( 'You already left a feedback', 'ay-feedback-user-plugin' ) . '</div>';	
			}			
			else {
						
				// You can use this with my AY Social Login Plugin or WP Social Login by Milled for avatar display
				$avatar_src = get_user_meta( $current_user->ID, 'wsl_current_user_image', true );
				$first_name = get_the_author_meta( 'user_firstname', $current_user->ID );								
				$last_name = get_the_author_meta( 'user_lastname', $current_user->ID );				
				
				?>
												
				<div class="feedback-form__notice"></div>
										
				<form action="" id="feedback_form" class="feedback-form" method="POST">		
					
					<div class="feedback__user">
						
						<?php
						if ( $avatar_src ) {
							echo '<img class="feedback__avatar" src="' . $avatar_src . '" />';
						}
						?>
						
						<h3 class="feedback__name" data-user-id="<?php echo $current_user->ID; ?>">
							<?php echo $first_name . ' ' . $last_name; ?>
						</h3>
					
					</div>		
					
					<fieldset>
					
					    <input type="url" placeholder="<?php _e( 'Full site address', 'ay-feedback-user-plugin' ) ?>*" name="feedbackLink" id="feedbackLink" class="feedback__link">
					    				    				
					</fieldset>						
					
					<fieldset>
					
					    <textarea placeholder="<?php _e( 'Your feedback', 'ay-feedback-user-plugin' ) ?>*" name="feedbackContent" id="feedbackContent" class="feedback__textarea"></textarea>
					    				    				
					</fieldset>
									
					<button class="feedback__submit-button"><?php _e( 'Send', 'ay-feedback-user-plugin' ) ?></button>								
				
				</form>
			
			<?php
			}			
		
		}
		else {
			echo '<div class="feedback-notice">';
			echo __( 'You must be logged in to leave a review', 'ay-feedback-user-plugin' );
			do_action( 'sl_form' );
			echo '</div>';
		}
		
		echo '</div>';
		
	}
	
	
	// Loop template for get_feedback() and feedback_load_more() functions
	public static function get_feedback_loop() { 
		
		$user_id = get_the_author_meta( 'ID' );		
		
		// You can use this with my AY Social Login Plugin or WP Social Login by Milled for avatar display
		$avatar_src = get_user_meta( $user_id, 'wsl_current_user_image', true );
		$user_url = get_the_author_meta( 'user_url' , $user_id );
		
		?>
		
		<div class="feedback__item">
						
			<div class="feedback__user">
				
				<?php
				if ( $avatar_src ) {
					echo '<img class="feedback__avatar" src="' . $avatar_src . '" />';
				}
				?>								
				
				<a class="feedback__link" href="<?php echo $user_url; ?>" target="_blank" rel="nofollow"><h3 class="feedback__name"><?php the_title(); ?></h3></a>
			
			</div> 
			
			<div class="feedback__text">
			
				<?php the_content(); ?>
				
			</div>
			
		</div>
	
	<?php	
	}
	
	
	// Display feedback posts
	public static function get_feedback()	{
	
		global $post;	
				
		if ( is_front_page() ) {
			$paged = ( get_query_var('page') ) ? get_query_var('page') : 1;
		} 
		else {
			$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		}
		
		$arg = array( 
			//'post_status' => array( 'publish', 'pending' ),
			'post_type' => 'feedback',
			'posts_per_page' => 4,
			'paged' => $paged,
			'page' => $paged,
		);
		
		$q = new WP_Query( $arg );
		
		if ( $q->have_posts() ) { ?>		
		
			<div class="feedback">
						
				<?php
				while ( $q->have_posts() ) { 
					
					$q->the_post(); 
					
					FeedbackShortcode::get_feedback_loop();
					
				}
				?>				
					
			</div>
		
		<?php
		}
		wp_reset_postdata();	
		
		?>
		
		<div class="feedback-actions">
		
			<?php
			if ( $q->max_num_pages > 1 ) { ?>
						
				<button data-posts="<?php echo esc_attr( json_encode( $arg ) ); ?>" data-current-page="<?php echo $paged; ?>" data-max-page="<?php echo $q->max_num_pages; ?>" class="feedback-load-more-button"><?php _e( 'Load more', 'ay-feedback-user-plugin' ); ?></button>
					
			<?php
			}
			?>
		
			<button class="feedback-add-button"><?php _e( 'Leave feedback', 'ay-feedback-user-plugin' ); ?></button>
		
		</div>
	
	<?php
	}
		
	
	// [ay-feedback] shortcode without options
	public static function feedback_shortcode() {
		
		ob_start();
				
		FeedbackShortcode::get_feedback();
		FeedbackShortcode::feedback_form();
					
		return ob_get_clean();
		
	}
	
}