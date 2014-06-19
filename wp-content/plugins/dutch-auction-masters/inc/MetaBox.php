<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */
if (!class_exists('SC_MetaBox')) {
    class SC_MetaBox
    {

        public function __construct($postTypeName, $title, $fields, $metaPrefix)
        {
            $this->id = $postTypeName . "_meta";
            $this->title = $title;
            $this->fields = apply_filters("add_custom_post_fields_" . $postTypeName, $fields);
            $this->metaPrefix = $metaPrefix;
            add_action('add_meta_boxes_' . $postTypeName, array($this, 'add_post_meta_box'), 10, 1);
            add_action('admin_footer', array($this, "admin_footer"));
        }

        public function admin_footer()
        {
            echo '<div class="powered-by"><img src="http://www.dutchauctionmasters.com/logo" /> &nbsp;<a href="http://www.dutchauctionmasters.com/">Powered by DAM</a> <br/></div>';
        }

        public function add_post_meta_box($post)
        {
            add_meta_box($this->id, $this->title, array(
                $this,
                'post_detail_template'
            ), null, 'normal', 'high', null);
        }

        public function post_detail_template($post)
        {
            $default = array(
                'id' => 0,
                'post_id' => $post->ID,
                'title' => '',
                'picture' => '',
                'begin' => '',
                'end' => '',
                'amount' => '',
                'auction_type' => 0,
                'original_price' => '',
                'reserve_price' => '',
                'shipping_fee' => '',
                'start_price' => '',
                'step_price' => '',
                'description' => '',
                'properties' => ''
            );

            $auction = SC_DataProvider::get_auction_by_post_id($post->ID);
            $entry = shortcode_atts($default, $auction);

            echo ' <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
                        <tbody>';

            foreach ($this->fields as $key => $atts) {
                $atts['name'] = $key;

                if ($auction)
                    $atts['value'] = $entry[$key];

                SC_FieldControls::render($atts);
            }

            echo '</tbody>
            </table>';
        }
    }
}