<?php
/*
Plugin Name: Stop Spammer Widget
Plugin URI: http://www.blogseye.com/i-make-plugins/stop-spammer-registrations-plugin/
Description: Widget to display Stop Spammer stats in sidebar.
Version: 5.0
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
if (!defined('ABSPATH')) exit; // just in case

// this is the sidebar widget that can display the # of spam stopped.
class Stop_spam_Widget extends WP_Widget {

	public function __construct() {
		// widget actual processes
		parent::__construct(
		'stop_spam_widget', // Base ID
		'Stop_spam_Widget', // Name
		array( 'description' => __( 'Show Spam Stats', 'text_domain' ), ) // Args
		);
	}

	public function form( $instance ) {
		// outputs the options form on admin
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( '', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
		echo $before_title . $title . $after_title;
		// widget stuff goes here
		?>
		<p >This site protected by:<br/> <a target="_blank" href="http://wordpress.org/extend/plugins/stop-spammer-registrations-plugin/">Stop Spammers plugin for Wordpress</a><br/>
		<?php
		if (function_exists('kpg_sp_get_stats')) {

			$stats=kpg_sp_get_stats();
			extract($stats);
			if ($spmcount>0) { ?>
				<?php echo $spmcount; ?> spammers stopped.</p>

				<?php 
			}
		} else {
			echo "Please activate the Stop Spammers Plugin to see stats</p>";
		}
		echo $after_widget;
		
	}

}
add_action('widgets_init', create_function('', 'return register_widget("stop_spam_widget");'));


?>