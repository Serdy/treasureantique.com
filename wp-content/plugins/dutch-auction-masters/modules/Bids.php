<?php

if (!class_exists('SC_BidsRender')) {
    class SC_BidsRender
    {
        const TEXT_DOMAIN = "dam-auction-masters";
        public function __construct()
        {
            $this->menu = array(
                "parent_slug" => "edit.php?post_type=dam_auction",
                "page_title" => __("Bids", self::TEXT_DOMAIN),
                "menu_title" => __("Bids", self::TEXT_DOMAIN),
                "capability" => "activate_plugins",
                "menu_slug" => "dam_auction_masters_bids",
                "includeUri" => null,
                "function" => array($this, 'render'),
                "icon_url" => null,
                "position" => null
            );
            add_action('admin_menu', array($this, 'initMenu'));
        }

        public function render()
        {
            $table = new SC_Bids();
            $table->prepare_items();
            $pid = $table->get_pagenum();
            ?>
            <div class="wrap">
                <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                <h2><?php _e('Bids', 'dam-auction-masters') ?>
                </h2>
                <div class="clearfix">
                    <?php echo isset($message) ? $message : ""; ?>
                    <form id="auctions-table" method="GET">
                        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                        <?php $table->display() ?>
                    </form>
                </div>
            </div>
        <?php
        }

        public function initMenu()
        {
            extract($this->menu);
            add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        }
    }

    new SC_BidsRender();
}


if (!class_exists('SC_Bids')) {

    class SC_Bids extends WP_List_Table
    {
        const TEXT_DOMAIN = "dam-auction-masters";

         function __construct()
        {
            $this->defaultSortColumn = "id";

            $this->colums = array (
                'id' => array ('input',	'p', __ ('user_id note', 'dam-auction-masters' )),
                'user_name' => array ('input',	'p', __ ('user_name note', 'dam-auction-masters' )),
                'user_email' => array ('input',	'p', __ ('user_email note', 'dam-auction-masters' )),
                'quantity' => array ('input',	'p', __ ('quantity note', 'dam-auction-masters' )),
                'price' => array ('input',	'p', __ ('price note', 'dam-auction-masters' )),
                'created' => array ('input',	'p', __ ('created note', 'dam-auction-masters' ))
            );

            parent::__construct(array(
                'singular' => 'auction',
                'plural' => 'auctions'
            ));
        }

        function column_default($item, $column_name) {
            return $item [$column_name];
        }

        function get_columns() {
            $columns = array (
//                'cb' => '<input type="checkbox" />',
            );

            foreach ( $this->colums as $key => $value ) {
                $columns [$key] =  str_replace('_', ' ',  ucfirst ( $key )) ;
            }
            return $columns;
        }

        function column_price($item) {
            $price = $item['price'];
            $price = SC_functions::money_format($price);
            return $price;
        }

        function prepare_items()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'dam_bid';
            $per_page = 20;
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array(
                $columns,
                $hidden,
                $sortable
            );
            $auctionId = isset($_GET["auctionid"]) ? $_GET["auctionid"] : "";
            if ($auctionId) {
                $where = "  WHERE `auction_id` = $auctionId";
            } else {
                $where = "";
            }

            $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name" . $where);

            $paged = isset ($_REQUEST ['paged']) ? max(0, intval($_REQUEST ['paged']) - 1) : 0;
            $paged = $paged * $per_page;

            $orderBy = (isset ($_REQUEST ['orderby']) && in_array($_REQUEST ['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST ['orderby'] : $this->defaultSortColumn;
            $order = (isset ($_REQUEST ['order']) && in_array($_REQUEST ['order'], array(
                    'asc',
                    'desc'
                ))) ? $_REQUEST ['order'] : 'asc';

            $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name $where ORDER BY $orderBy $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

            $this->set_pagination_args(array(
                'total_items' => $total_items,
                'per_page' => $per_page,
                'total_pages' => ceil($total_items / $per_page)
            ));

        }
    }
}