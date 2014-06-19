<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

class AuctionListWidget extends WP_Widget
{
    const TEXT_DOMAIN = "dam-auction-masters";

    function __construct()
    {
        $this->fields = array(
            'all' => array(
                'label' => __('All', self::TEXT_DOMAIN)
            ),
            'running' => array(
                'label' => __('Running', self::TEXT_DOMAIN)
            ),
            'upcoming' => array(
                'label' => __('Upcoming', self::TEXT_DOMAIN)
            ),
        );

        parent::__construct('dam_auction_list_widget',
            __('Auction List Widget', self::TEXT_DOMAIN),
            array(
                'description' => __('A list of auction', self::TEXT_DOMAIN)
            ));
    }

    function widget($args, $instance)
    {
        extract($args);
        $instance = wp_parse_args((array)$instance,
            array('type' => "all"
            ));

        $category = isset($_REQUEST['category']) ? $_REQUEST['category'] : "";
        $keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : "";
        $type = isset($instance ['type']) ? $instance ['type'] : "all";

        if (array_key_exists($type, $this->fields)) {
            $label = $this->fields[$type];
            $list = SC_functions::get_auctions($type, $category, $keyword);

            if (!empty($category)) {
                $category = get_category($category);
                if ($category)
                    $category = $category->name;
            }

            $title = $label['label'];
            if ($list && $list->auctions && count($list->auctions) > 0)
                $this->renderUI($title, $category, $list->auctions, $type, $list->islast);
            else
                echo __('No auctions', self::TEXT_DOMAIN);
        }
    }

    function update($new_instance, $old_instance)
    {
        $new_instance = wp_parse_args((array)$new_instance,
            array('type' => 'all',
            ));
        $instance ['type'] = strip_tags($new_instance ['type']);
        return $instance;
    }

    function form($instance)
    {
        $instance = wp_parse_args((array)$instance,
            array('type' => 'all'
            ));
        $type = $instance ['type'];

        foreach ($this->fields as $key => $label) {
            $this->renderInput($key, $label, $type);
        }
    }

    function renderInput($key, $label, $type)
    {
        $id = $this->get_field_id($key);
        $name = $this->get_field_name('type');
        $value = $key;
        extract($label);
        $checked = (stripos($type, $key) === false) ? "" : "checked";
        echo "<p><input id='$id' name='$name' value='$value' $checked type='radio' /> <label for='$id'>$label</label></p>";
    }

    function renderUI($title, $category, $auctions, $type, $last)
    {
//        $category = empty($category) ? $category . "<span style='font-family:Arial'> &gt; </span>" : "";

        $label = __("auctions", self::TEXT_DOMAIN);
        echo "<div class='auction-list-wrap clearfix'>";
        echo "<h5> $category  $title $label</h5>";

        foreach ($auctions as $auction) {
            $this->renderAuction($type, $auction);
        }

        ?>
        <script type="text/javascript">
            window._visable_more_btn_upcoming = false;
            window._visable_more_btn_running = false;
            window._visable_more_btn_all = false;
            window._visable_more_btn_<?php echo $type;?> = !<?php echo json_encode($last) ;?>;
        </script>
        <!-- ko foreach: <?php echo $type;?> -->
        <div class="auction <?php echo $type; ?> clearBoth">
            <div class="picture">
                <a data-bind="attr: { href: auction_link}"><img
                        data-bind="attr: { src: pic}"/></a>
            </div>
            <a data-bind="attr: { href: auction_link}"><h5 class="title"
                                                           data-bind="text:title,attr:{'title':alttitle}"></h5></a>

            <div class="price">
                <div>
                    <del>
                        <span data-bind="html:originalPrice"></span>
                    </del>
                    / <span
                        data-bind="html:biddingPrice,attr:{'data-id':'auction_' + auction_ID}"></span>
                </div>
            </div>
            <div class="bar clearfix">
                <div class="auction-start-time" data-bind="visible:!started">
                    <strong><?php _e("Start", "dam-auction-masters"); ?></strong> &nbsp;<span
                        data-bind="text:start_time"></span>
                </div>

                <div class="auction-timer" data-bind="visible:started">
                    <input class="time-value" name='time' data-bind="value:end"
                           type="hidden"><b data-name="hour"></b><?php _e("h", "dam-auction-masters") ?> <b
                        data-name="min"></b><?php _e("m", "dam-auction-masters") ?> <b
                        data-name="sec"></b><?php _e("s", "dam-auction-masters") ?> <b
                        class="arrow-right"></b>
                </div>
                <a data-bind="attr: { href: auction_link}"
                   class="submit buttons"><?php _e("Bid me", "dam-auction-masters") ?></a>
            </div>
        </div>
        <!-- /ko -->

        <div class="clearboth center">
            <button
                class="btn-more-items btn-more-<?php echo $type; ?>"> <?php echo _e("show more items", "dam-auction-masters") ?></button>
        </div>

        <?php

        echo "<div class='auction-list$type clearfix' style='min-height: 200px; display: none;'><div></div></div>";
        echo "<input type='hidden' class='indicator' value='$type' />";
        echo "</div>";
    }

    function renderAuction($type, $auction)
    {
        ?>
        <div class="auction <?php echo $type; ?> clearBoth">
            <div class="picture">
                <a href="<?php echo $auction->auction_link; ?>"><img
                        src="<?php echo $auction->pic; ?>"/></a>
            </div>
            <a href="<?php echo $auction->auction_link; ?>"><h5
                    class="title"><?php echo $auction->title; ?> </h5></a>

            <div class="price">
                <div>
                    <del>
                        <span><?php echo $auction->originalPrice; ?> </span>
                    </del>
                    / <span
                        data-id="<?php echo 'auction_' . $auction->auction_ID; ?>"> <?php echo $auction->biddingPrice; ?></span>
                </div>
            </div>
            <div class="bar clearfix">
                <div
                    class="auction-start-time" <?php echo $auction->started ? "style='display:none'" : "" ?>>
                    <strong><?php _e("Start", "dam-auction-masters"); ?></strong>
                    &nbsp;<span><?php echo $auction->start_time; ?></span>
                </div>

                <div class="auction-timer" <?php echo $auction->started ? "" : "style='display:none'" ?>>
                    <input class="time-value" name='time' value="<?php echo $auction->end ?>" type="hidden">
                    <b data-name="hour"></b><?php _e("h", "dam-auction-masters") ?> <b
                        data-name="min"></b><?php _e("m", "dam-auction-masters") ?> <b
                        data-name="sec"></b><?php _e("s", "dam-auction-masters") ?> <b
                        class="arrow-right"></b>
                </div>
                <a href="<?php echo $auction->auction_link; ?>"
                   class="submit buttons"><?php _e("Bid me", "dam-auction-masters") ?></a>
            </div>
        </div>
    <?php
    }
}