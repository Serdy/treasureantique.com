<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

class AuctionWidget extends WP_Widget
{
    const TEXT_DOMAIN = "dam-auction-masters";

    public function __construct()
    {
        parent::__construct('dam_auction_widget',
            __('Auction Single Widget', self::TEXT_DOMAIN),
            array(
                'description' => __('A single auction', 'dam-auction-masters')
            ));
    }

    public function widget($args, $instance)
    {
        extract($args);

        $instance = wp_parse_args((array)$instance,
            array('title' => '',
                'display_pictures' => true,
                'display_description' => true,
                'display_customprops' => true,
                'display_action' => true
            ));

        $title = apply_filters('widget_title', $instance ['title']);

        if (isset($instance ['shortcode'])) {
            $auction_id = isset ($instance ['id']) ? $instance ['id'] : (isset($_GET ["id"]) ? $_GET ["id"] : "");
        } else {
            $auction_id = 0;
        }

        $display_description = isset ($instance ['display_description']) ? $instance ['display_description'] : 1;

        $display_customprops = isset ($instance ['display_customprops']) ? $instance ['display_customprops'] : 1;

        $display_pictures = isset ($instance ['display_pictures']) ? $instance ['display_pictures'] : 1;

        $display_action = isset ($instance ['display_action']) ? $instance ['display_action'] : 1;

        echo $before_widget;
        if (!empty ($title))
            echo $before_title . $title . $after_title;

        $loader = new template_loader ();
        $loader->set("title", $title);

        if (!empty ($auction_id)) {
            $auction = SC_DataProvider::get_auction($auction_id);

        } else if (isset($instance['post_id'])) {
            $auction = SC_DataProvider::get_auction_by_post_id($instance['post_id']);
        } else {
            $auction = SC_DataProvider::query_single();
        }

        if (!empty($auction) && $auction->status == AuctionStatus::NORMAL) {
            global $dam_main_auction_id;
            $dam_main_auction_id = $auction->id;

            $hits = $auction->hits + 1;
            SC_DataProvider::updateAuction(array("hits" => $hits), array("id" => $dam_main_auction_id));
        }

        $loader->set("auction", $auction);
        $loader->set("display_pictures", $display_pictures);
        $loader->set("display_description", $display_description);
        $loader->set("display_customprops", $display_customprops);
        $loader->set("display_action", $display_action);
        $loader->set("action_nonce", basename(__FILE__));
        $html = $loader->process("widget.ui.php");
        echo $html;
        echo $after_widget;
    }

    public function update($new_instance, $old_instance)
    {

        $new_instance = wp_parse_args((array)$new_instance,
            array('title' => '',
                'display_description' => 0,
                'display_customprops' => 0,
                'display_pictures' => 0,
                'display_action' => 0
            ));

        $instance ['title'] = strip_tags($new_instance ['title']);

        $instance ['display_description'] = $new_instance ['display_description'] ? 1 : 0;

        $instance ['display_customprops'] = $new_instance ['display_customprops'] ? 1 : 0;

        $instance ['display_pictures'] = $new_instance ['display_pictures'] ? 1 : 0;

        $instance ['display_action'] = $new_instance ['display_action'] ? 1 : 0;

        return $instance;
    }

    public function form($instance)
    {

        $instance = wp_parse_args((array)$instance,
            array('title' => '',
                'display_pictures' => true,
                'display_description' => true,
                'display_customprops' => true,
                'display_action' => true
            ));

        $title = strip_tags($instance['title']);

        if ($instance) {
            $display_pictures_checked = $instance ['display_pictures'] ? 'checked="checked"' : '';
            $display_description_checked = $instance ['display_description'] ? 'checked="checked"' : '';
            $display_customprops_checked = $instance ['display_customprops'] ? 'checked="checked"' : '';
            $display_action_checked = $instance ['display_action'] ? 'checked="checked"' : '';
        } else {
            $display_pictures_checked = 'checked="checked"';
            $display_description_checked = 'checked="checked"';
            $display_customprops_checked = 'checked="checked"';
            $display_action_checked = 'checked="checked"';
        }
        ?>
        <p>
            <label
                for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', "dam-auction-masters"); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <input
                id="<?php echo $this->get_field_id('display_description'); ?>" <?php echo $display_description_checked; ?>
                name="<?php echo $this->get_field_name('display_description'); ?>" type="checkbox">
            <label
                for="<?php echo $this->get_field_id('display_description'); ?>"><?php _e("Display description", "dam-auction-masters"); ?></label>
        </p>
        <p>
            <input
                id="<?php echo $this->get_field_id('display_customprops'); ?>" <?php echo $display_customprops_checked; ?>
                name="<?php echo $this->get_field_name('display_customprops'); ?>" type="checkbox">
            <label
                for="<?php echo $this->get_field_id('display_customprops'); ?>"><?php _e("Display custom properties", "dam-auction-masters"); ?></label>
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('display_pictures'); ?>" <?php echo $display_pictures_checked; ?>
                   name="<?php echo $this->get_field_name('display_pictures'); ?>" type="checkbox">
            <label
                for="<?php echo $this->get_field_id('display_pictures'); ?>"><?php _e("Display mutiple pictures", "dam-auction-masters"); ?></label>
        </p>

        <p>
            <input id="<?php echo $this->get_field_id('display_action'); ?>" <?php echo $display_action_checked; ?>
                   name="<?php echo $this->get_field_name('display_action'); ?>" type="checkbox">
            <label
                for="<?php echo $this->get_field_id('display_action'); ?>"><?php _e("Display bid button", "dam-auction-masters"); ?></label>
        </p>
    <?php
    }
} 