<?php
add_action('widgets_init', create_function('', 'return register_widget("DF_contact");'));

class DF_contact extends WP_Widget {
	function DF_contact() {
		 parent::WP_Widget(false, $name = THEME_FULL_NAME.' Contact',array( 'description' => __( "Contact information widget.", THEME_NAME )));	
	}

	function form($instance) {
		/* Set up some default widget settings. */
		$defaults = array(
			'title' => __('Contact info', THEME_NAME),
			'text' => '',
			'address' => '',
			'phone' => '',
			'mail' => '',

		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title = esc_attr($instance['title']);
		$text = esc_attr($instance['text']);
		$address = esc_attr($instance['address']);
		$phone = esc_attr($instance['phone']);
		$mail = esc_attr($instance['mail']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php printf ( __( 'Title:' , THEME_NAME )); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('text'); ?>"><?php  printf ( __( 'Text:' , THEME_NAME )); ?> <textarea style="height:200px;" class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea></label></p>
			<p><label for="<?php echo $this->get_field_id('address'); ?>"><?php printf ( __( 'Address:' , THEME_NAME )); ?> <input class="widefat" id="<?php echo $this->get_field_id('address'); ?>" name="<?php echo $this->get_field_name('address'); ?>" type="text" value="<?php echo $address; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('phone'); ?>"><?php printf ( __( 'Phone:' , THEME_NAME )); ?> <input class="widefat" id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" type="text" value="<?php echo $phone; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('mail'); ?>"><?php printf ( __( 'Mail:' , THEME_NAME )); ?> <input class="widefat" id="<?php echo $this->get_field_id('mail'); ?>" name="<?php echo $this->get_field_name('mail'); ?>" type="text" value="<?php echo $mail; ?>" /></label></p>
		
        <?php 
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text'] = strip_tags($new_instance['text']);
		$instance['address'] = strip_tags($new_instance['address']);
		$instance['phone'] = strip_tags($new_instance['phone']);
		$instance['mail'] = strip_tags($new_instance['mail']);

		return $instance;
	}

	function widget($args, $instance) {
		extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$text = $instance['text'];
		$address = $instance['address'];
		$phone = $instance['phone'];
		$mail = $instance['mail'];
		
?>		
	<?php echo $before_widget; ?>
		<?php if($title) echo $before_title.$title.$after_title; ?>
			<?php 
				if($text) {
	            	echo wpautop(stripslashes($text));
	           	} 
           	?>
            <ul class="fa-ul">
                <?php if($address) { ?><li><i class="fa-li fa fa-map-marker"></i><?php echo stripslashes($address);?></li><?php } ?>
                <?php if($phone) { ?><li><i class="fa-li fa fa-phone"></i><?php echo stripslashes($phone);?></li><?php } ?>
                <?php if($mail) { ?><li><i class="fa-li fa fa-envelope"></i><?php echo stripslashes($mail);?></li><?php } ?>
            </ul>

	<?php echo $after_widget; ?>
		
	
      <?php
	}
}
?>
