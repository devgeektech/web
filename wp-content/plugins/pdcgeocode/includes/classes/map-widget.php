<?php

class PdcGeocode_Map_Widget extends WP_Widget {
        
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
            parent::__construct(
			'map_widget', // Base ID
			__( 'Setlr Map Widget', 'pdcgeocode' ), // Name
			array( 'description' => __( 'Displays Helpers Map', 'pdcgeocode' ), ) // Args
		);
            write_log( 'Map_Widget construct');
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
                echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
                if ( shortcode_exists( 'setlr_map' ) ) :
                    echo do_shortcode('[setlr_map]');
                else :
                    write_log( 'Error: no setlr_map shortcode');
                endif;
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
            $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'pdcgeocode' );
        ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
	<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
            // processes widget options to be saved
            $instance = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

            return $instance;
	}
}

