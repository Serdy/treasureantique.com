<div>
	<input id="<?php echo $widget_form_title_id; ?>" name="key_name" type="hidden"  value="<?php echo $title_value;?>" />
	<label ><input class="widefat-radio"
		
		name="<?php echo $widget_form_title_name; ?>" type="radio"  <?php echo $title_value=="running"?"checked='checked'":""?>
		value="running" /><?php _e("Running","dam-auction-masters");?></label>
	<label ><input class="widefat-radio"
		
		name="<?php echo $widget_form_title_name; ?>" type="radio" <?php echo $title_value=="upcoming"?"checked='checked'":""?>
		value="upcoming" /><?php _e("Upcoming","dam-auction-masters");?></label>

</div>