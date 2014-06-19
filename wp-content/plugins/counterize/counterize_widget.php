<?php

// security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	die( 'There is nothing to see here.' );
}

/**
 * CounterizeWidget Class
 */
class CounterizeWidget extends WP_Widget
{
	/* constructor */
	function CounterizeWidget()
	{
		// widget actual processes
		parent::WP_Widget( false, $name = 'CounterizeWidget' );
	}

	function counterize_widget_init()
	{
		return register_widget( 'CounterizeWidget' ) ;
	}

	function form( $instance )
	{
		// outputs the options form on admin
		if( ! isset( $instance['title'] ) )
		{
			$instance['title'] = __( 'Default title', COUNTERIZE_TD );
		}
		$title = esc_attr( $instance['title'] );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', COUNTERIZE_TD ); ?></label>
		<input
			class="widefat"
			id="<?php echo $this->get_field_id( 'title' ); ?>"
			name="<?php echo $this->get_field_name( 'title' ); ?>"
			type="text"
			value="<?php echo $title; ?>"
		/>
		</p>
		<?php
	}

	function update( $new_instance, $old_instance )
	{
		// processes widget options to be saved
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
	}

	function widget( $args, $instance )
	{
		// outputs the content of the widget
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if( $title )
		{
			echo $before_title . $title . $after_title;
		}
		?>
		Hello, World!
		<?php
		echo $after_widget;
	}

} // class

function counterize_widgets_init()
{
	return register_widget( 'CounterizeWidget' );
}

//Not ready yet! A next version :)
//add_action( 'widgets_init', 'counterize_widgets_init' );

?>