<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

if (!class_exists('SC_DataProvider')) {

    class Auction
    {
        public $id;
        public $post_id;
        public $auction_type;
        public $title;
        public $brand;
        public $tags;
        public $picture;
        public $category;
        public $properties;
        public $begin;
        public $end;
        public $start_price;
        public $step_price;
        public $bid_price;
        public $original_price;
        public $reserve_price;
        public $shipping_fee;
        public $status;
        public $notified;
        public $deal_state;
        public $amount;
        public $winner;
        public $last_bids;
        public $description;
        public $winner_id;
        public $hits;
        public $txn_id;
    }

    class SC_DataProvider
    {
        const BID_PAGE_SIZE = 3;
        const DAM_AUCTION_TABLE = "dam_auction";

        public static function initData()
        {
            SC_DataProvider::insertDemoAuctions();
        }

        public static function upgrade()
        {
            global $wpdb;
            /** upgrade 1.2.3.8 to 1.6.0.0 */
            /** @var $wpdb wpdb */
            $table = $wpdb->prefix . self::DAM_AUCTION_TABLE;
            $rows = $wpdb->get_results("select * from $table");
            $wpdb->delete($wpdb->posts, array('post_type' => 'dam_auction'));
            self::initDatabase();
            if ($rows) {
                foreach ($rows as $auction) {
                    /** @var $auction Auction */
                    $postId = $auction->post_id;
                    if (empty($postId)) {
                        $post = array(
                            'post_title' => $auction->title,
                            'post_status' => $auction->status == AuctionStatus::NORMAL ? 'publish' : 'draft',
                            'post_author' => 1,
                            'post_date' => date('Y-m-d H:i:s', current_time("timestamp")),
                            "post_type" => "dam_auction"
                        );
                        $postId = wp_insert_post($post);
                        $auction->post_id = $postId;
                        $wpdb->update($table, (array)$auction, array('id' => $auction->id));
                    }
                }
            }
        }

        public static function clearDATA()
        {
            SC_DataProvider::removeTables();
        }

        public static function initDatabase()
        {
            global $wpdb, $charset_collate;

            if (method_exists($wpdb, "get_charset_collate")) {
                $charset_collate = $wpdb->get_charset_collate();

            } else {
                $charset_collate = "DEFAULT CHARACTER SET 'utf-8'";
            }
            // should keep tow space between key and id "PRIMARY KEY  (id)";
            $sql = "CREATE TABLE " . $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			post_id int(11) NOT NULL default 0,
			auction_type int(11) NOT NULL default 0,
			title VARCHAR(200) NOT NULL,
			brand VARCHAR(100) NULL default NULL,
			tags VARCHAR(500) NULL default NULL,
			picture VARCHAR(1000) NULL,
			category VARCHAR(100) NULL,
			properties VARCHAR(500) NULL default NULL,
			begin datetime NOT NULL default '0000-00-00 00:00:00',
			end datetime NULL,
			start_price decimal(10,2) NULL,
			step_price decimal(10,2) NULL,
			bid_price decimal(10,2) NULL default 0,
			original_price decimal(10,2) NULL default 0,
			reserve_price decimal(10,2) NULL default 0,
			shipping_fee decimal(10,2) NULL default 0,
			status tinyint NOT NULL default 0,
			notified tinyint NOT NULL default 0,
			deal_state  VARCHAR(20) NULL default '',
			amount int(11) NOT NULL,
			winner VARCHAR(100) NULL,
			last_bids VARCHAR(2000) NULL,
			description TEXT NULL,
			winner_id int(11) NULL,
			owner_id int(11) NULL,
			hits int(11) NULL default 0,
			txn_id VARCHAR(100) NULL,
			PRIMARY KEY  (id)
			) $charset_collate;
			CREATE TABLE " . $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_ORDER . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			auction_id int(11) NOT NULL default 0,
			post_id int(11) NOT NULL default 0,
			title VARCHAR(200) NOT NULL,
			picture VARCHAR(1000) NULL,
			properties VARCHAR(500) NULL default NULL,
			bid_price decimal(10,2) NULL default 0,
			shipping_fee decimal(10,2) NULL default 0,
			deal_state  VARCHAR(20) NULL default '',
			winner_id int(11) NULL,
			owner_id int(11) NULL,
			txn_id VARCHAR(100) NULL,
			created datetime NOT NULL default '0000-00-00 00:00:00',
			paid_time datetime NULL,
			PRIMARY KEY  (id)
			) $charset_collate;
			CREATE TABLE " . $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_BID . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			auction_id int(11) NOT NULL,
			user_id int(11) NOT NULL default 0,
			user_name varchar(50) NOT NULL default '',
			user_email varchar(50) NULL default '',
			quantity int(11) NOT NULL,
			price decimal(10,2) NULL,
			created datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY  (id)
			) $charset_collate;
			CREATE TABLE " . $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			user_id int(11) NOT NULL default 0,
			auction_id int(11) NOT NULL,
			phone varchar(50) Null,
			contact_name VARCHAR(100) NOT NULL,
			gender Varchar(10) NULL,
			postal_code Varchar(10) NULL,
			house_number Varchar(10) NULL,
			street Varchar(100) NULL,
			city Varchar(100) null,
			province Varchar(100) null,
			country Varchar(100) null,
			PRIMARY KEY  (id)
			)$charset_collate";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public static function installDemoData()
        {
            self::insertDemoAuctions();
        }

        public static function deleteDamAuctionPosts()
        {
            global $wpdb;
            $table_name = $wpdb->posts;
            $sql = "DELETE FROM " . $table_name . " where post_type='" . DutchAuctionMasters::DAM_POST_TYPE . "'";
            $wpdb->query($sql);
        }

        public static function removeTables()
        {
            self::removeTable(DutchAuctionMasters::DAM_TABLE_AUCTION);
            self::removeTable(DutchAuctionMasters::DAM_TABLE_BID);
            self::removeTable(DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS);
            self::removeTable(DutchAuctionMasters::DAM_TABLE_ORDER);
        }

        public static function removeTable($table)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . $table;
            $sql = "DROP TABLE " . $table_name;
            $wpdb->query($sql);
        }

        public static function insertAuction($data)
        {
            global $wpdb;
            $result = $wpdb->insert($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION, $data);
            return $result;
        }

        public static function insertAddress($data)
        {
            global $wpdb;
            $result = $wpdb->insert($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS, $data);
            return $result;
        }

        public static function updateAddress($data, $where)
        {
            global $wpdb;
            $result = $wpdb->update($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS, $data, $where);
            return $result;
        }

        public static function deleteAuction($where)
        {
            global $wpdb;
            $result = $wpdb->delete($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION, $where);
            return $result;
        }

        public static function deleteAddress($where)
        {
            global $wpdb;
            $result = $wpdb->delete($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS, $where);
            return $result;
        }

        public static function updatePost($data, $where)
        {
            global $wpdb;
            $result = $wpdb->update($wpdb->posts, $data, $where);
            return $result;
        }

        public static function updateAuction($data, $where)
        {
            global $wpdb;
            $result = $wpdb->update($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION, $data, $where);
            return $result;
        }

        public static function updateDealState($auction_id, $new_deal_state, $old_deal_state, $user_id)
        {
            global $wpdb;
            $result = null;

            if (isset($user_id)) {
                $result = $wpdb->update(
                    $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION,
                    array("deal_state" => $new_deal_state),
                    array("id" => $auction_id, "deal_state" => $old_deal_state, "winner_id" => $user_id));
            } else if (current_user_can("activate_plugins")) {

                $result = $wpdb->update(
                    $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION,
                    array("deal_state" => $new_deal_state),
                    array("id" => $auction_id, "deal_state" => $old_deal_state));
            }

            return $result;
        }

        public static function updateDealStatePay($auction_id, $new_deal_state, $txn_id)
        {
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION,
                array("deal_state" => $new_deal_state, "txn_id" => $txn_id),
                array("id" => $auction_id));
            return $result;
        }

        public static function get_auctions_by_ids($auction_ids, $limit = 10, $auction_id)
        {
            global $wpdb;
            $str_ids = "0";
            if (!is_array($auction_ids)) {
                throw new Exception('Parameter is not array!');
            } else {
                foreach ($auction_ids as $ele) {
                    $id = intval($ele);
                    if ($id > 0) {
                        $str_ids .= ',' . $id;
                    }
                }
            }
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $where = " `id` in ($str_ids) and `status` =" . AuctionStatus::NORMAL . " and TIMEDIFF(`end`,'$now')>0";
            $query = "select * from `" . $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION . "` WHERE " . $where . " order by ( case when `BEGIN` > now() then 1 else 0 end ),`end` asc LIMIT 0 , %d";
            $sql = $wpdb->prepare($query, $limit);
            $result = $wpdb->get_results($sql);
            if (count($result) < $limit) {
                $auction = self::get_auction($auction_id);
                if (is_object($auction) && property_exists($auction, 'category'))
                    $category = $auction->category;
                else
                    $category = "";
                $auctions = self::get_supplement_auctions($category, $limit, $auction_id);
                $result = self::unique_merge($result, $auctions);
                if (count($result) > $limit) {
                    $result = array_slice($result, 0, $limit);
                }
            }
            return $result;
        }

        public static function unique_merge($auctions1, $auctions2)
        {
            $result = array();
            foreach ($auctions2 as $array) {
                if (!in_array($array, $auctions1)) {
                    $result[] = $array;
                }
            }
            $result = array_merge($auctions1, $result);
            return $result;
        }

        public static function get_supplement_auctions($category, $limit = 10, $auction_id)
        {
            global $wpdb;
            $categories = explode(',', trim($category, ','));
            $args = array();
            $case = "";
            foreach ($categories as $category) {
                $case .= "or  `category` like (%s) ";
                $args[] = '%,' . like_escape($category) . ',%';
            }
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            if (isset($auction_id) && $auction_id) {
                $where = " `id` !=$auction_id and `status` =" . AuctionStatus::NORMAL . " and TIMEDIFF(`end`,'$now')>0";
            } else {
                $where = " `status` =" . AuctionStatus::NORMAL . " and TIMEDIFF(`end`,'$now')>0";
            }
            $query = "select * from `" . $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION . "` WHERE "
                . $where . " order by ( case when " . trim($case, "or") . " then 0 else 1 end ),`end` asc LIMIT 0 ," . $limit;
            $sql = $wpdb->prepare($query, $args);
            $result = $wpdb->get_results($sql);
            return $result;
        }

        public static function get_address_by_auction_id($auction_id)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS;
            $where = " `auction_id`=%d ";
            $query = "select * from `" . $table . "` WHERE " . $where;
            $sql = $wpdb->prepare($query, $auction_id);
            $items = $wpdb->get_results($sql);
            if (count($items) > 0)
                return $items [0];
            return false;
        }

        public static function get_address($user_id, $auction_id = '')
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS;
            $args = compact("user_id", "auction_id");
            $args = array_filter($args);
            $list = self::queryEntity($table, $args);
            $entity = reset($list);
            return $entity;
        }

        public static function get_status($auctionId)
        {
            $auction = self::get_auction($auctionId);
            if (!empty ($auction)) {
                $result = new stdClass ();
                $last_bids = self::get_last_bids($auctionId);
                if (floatval($auction->bid_price) <= 0)
                    $price = $auction->start_price;
                else
                    $price = $auction->bid_price;
                $message = SC_functions::get_auction_closed_message($auction);
                $result->last_bids = SC_functions::formatBids($last_bids, true);
                $result->id = $auctionId;
                $result->bid_price = $price;
                $result->step_price = $auction->step_price;
                $result->message = $message;
                $result->closed = !empty($auction->notified);
                $result->ending = !empty($message);
                $result->bid_str_price = SC_functions::money_format($price);
                return $result;
            }
            return false;
        }

        public static function placeBid($auctionId, $price, $userId, $username, $email = null)
        {
            $auction = self::get_running_auction($auctionId);
            if (empty ($auction)) {
                $result = new stdClass ();
                $result->error = __("auction invalid to bid", "dam-auction-masters");
                return $result;
            }
            if ($auction->auction_type == AuctionType::DUTCH_AUCTION) {
                $canBid = self::canUserBidDutchAuction($auctionId, $price, $userId, $auction);
                if ($canBid === true) {
                    $result = self::insertBid($auctionId, $price, $userId, $username, $email, $auction);
                    return $result;
                } else if ($canBid !== false) {
                    return $canBid;
                }
            } else if (floatval($price) >= (floatval($auction->bid_price) + floatval($auction->step_price))) {
                $result = self::insertBid($auctionId, $price, $userId, $username, $email, $auction);
                return $result;
            }

            $result = new stdClass ();
            $result->error = __("bid too lower", "dam-auction-masters");
            return $result;
        }

        public static function update_last_bids($bids, $auctionId, $price, $auctionType = AuctionType::COMMON)
        {
            $json_string = mysql_real_escape_string($bids);
            if ($auctionType == AuctionType::COMMON)
                $data = array("last_bids" => $json_string, "bid_price" => $price);
            else
                $data = array("last_bids" => $json_string);
            self::updateAuction($data, array("id" => $auctionId));
        }

        public static function get_last_bids($auctionId, $limit = 3)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_BID;
            $bids = self::queryEntity($table, array(
                "auction_id" => $auctionId
            ), null, $limit, "price", true);
            return $bids;
        }

        public static function get_ending_auctions($limit = 10)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $status = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $where = " `notified`= 0 and `status`= $status and TIMEDIFF(`end`,'$now')<0 ";
            $sql = "select * from `" . $table . "` WHERE " . $where . " order by `end` asc LIMIT 0 , %d";
            $query = $wpdb->prepare($sql, $limit);
            $auctions = $wpdb->get_results($query);
            return $auctions;
        }

        public static function queryEntity($table, $where = null, $where_format = null, $limit = 30, $sortField = null, $desc = false)
        {
            global $wpdb;
            $sort = $desc ? " desc " : " asc ";
            $orderBy = isset($sortField) ? (" order by `" . $sortField . "`" . $sort) : "";
            if (!isset ($where) || !is_array($where)) {
                $sql = "select * from `$table` $orderBy LIMIT 0 , " . $limit;
                $result = $wpdb->get_results($sql);
                return $result;
            } else {
                $wheres = array();
                $where_formats = $where_format = ( array )$where_format;
                foreach (array_keys($where) as $field) {
                    if (!empty ($where_format)) {
                        $form = ($form = array_shift($where_formats)) ? $form : $where_format [0];
                    } elseif (isset ($wpdb->field_types [$field])) {
                        $form = $wpdb->field_types [$field];
                    } else {
                        $form = '%s';
                    }
                    $wheres [] = "$field = $form";
                }
                $sql = "select * from `" . $table . "` WHERE " . implode(' AND ', $wheres) . " " . $orderBy . " LIMIT 0 , " . $limit;
                $query = $wpdb->prepare($sql, $where);
                return $wpdb->get_results($query);
            }
        }

        public static function query($where = null, $where_format = null, $limit = 30)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            return self::queryEntity($table, $where, $where_format, $limit);
        }

        public static function getAuctionByPostIds($postIds)
        {
            global $wpdb;
            if (isset($postIds) && is_array($postIds)) {
                $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
                $ids = implode(',', $postIds);
                $where = " `post_id` in (" . $ids . ")";
                $sql = "select * from `" . $table . "` WHERE " . $where;
                $auctions = $wpdb->get_results($sql);
                if (count($auctions) > 0)
                    return $auctions;
            }
            return false;
        }

        public static function getPostIdsByStatus($status)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'dam_auction'; // do not forget about tables prefix
            $normal = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $status = strtoupper($status);

            if ($status == "RUNNING") {
                $where = " WHERE `status` = $normal and TIMEDIFF(`begin`,'$now')<0 and TIMEDIFF(`end`,'$now')>0";
            } else if ($status == "UPCOMING") {
                $where = " WHERE `status` = $normal and TIMEDIFF(`begin`,'$now')>0";
            } else if ($status == "CLOSED") {
                $where = " WHERE `status` = $normal and TIMEDIFF(`end`,'$now')<0";
            } else if ($status == "PAID") {
                $where = " WHERE `status` = $normal and (`deal_state`='" . DealState::PAID . "' or `deal_state`='" . DealState::DELIVERED . "' or  `deal_state`='" . DealState::ACCEPTED . "' )";
            } else {
                $where = " WHERE 1 ";
            }
            $items = $wpdb->get_results("SELECT `post_id` FROM $table_name $where");
            $result = array();
            if (count($items) > 0) {
                foreach ($items as $item) {
                    $result[] = $item->post_id;
                }
            }
            return $result;
        }

        public static function get_auction_by_post_id($post_id)
        {
            global $wpdb;
            if ($post_id) {
                $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
                $where = " `post_id`= %d ";
                $query = "select * from `" . $table . "` WHERE " . $where;
                $sql = $wpdb->prepare($query, $post_id);
                $auctions = $wpdb->get_results($sql);
                if (count($auctions) > 0)
                    return $auctions [0];
            }
            return false;
        }

        public static function get_auction($auction_id)
        {
            global $wpdb;
            if ($auction_id) {
                $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
                $where = " `id`= %d ";
                $query = "select * from `" . $table . "` WHERE " . $where;
                $sql = $wpdb->prepare($query, $auction_id);
                $auctions = $wpdb->get_results($sql);
                if (count($auctions) > 0)
                    return $auctions [0];
            }
            return false;
        }

        public static function get_auction_txn_id($txn_id)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $where = " `txn_id`= %s ";
            $query = "select * from `" . $table . "` WHERE " . $where;
            $sql = $wpdb->prepare($query, $txn_id);
            $auctions = $wpdb->get_results($sql);
            if (count($auctions) > 0)
                return $auctions [0];
            return false;
        }

        public static function get_running_auction($auction_id)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $status = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $where = " `id`= %d and `status`= $status and TIMEDIFF(`begin`,'$now')<0 and TIMEDIFF(`end`,'$now')>0 ";
            $query = "select * from `" . $table . "` WHERE " . $where . " order by `begin` asc ";
            $sql = $wpdb->prepare($query, $auction_id);
            $auctions = $wpdb->get_results($sql);
            if (count($auctions) > 0)
                return $auctions [0];
            return false;
        }

        public static function live_auctions($limit = 20)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $status = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $where = " `status`= $status and TIMEDIFF(`end`,'$now')>0 ";
            $query = "select * from `" . $table . "` WHERE " . $where . " order by `end` asc LIMIT 0 , %d";
            $sql = $wpdb->prepare($query, $limit);
            $auctions = $wpdb->get_results($sql);
            if (count($auctions) > 0)
                return $auctions;
            return false;
        }

        public static function get_auction_count($category = null, $statusStr = null, $keyword = null)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $status = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            if ($statusStr == AuctionStrStatus::RUNNING) {
                $where = " `status`= $status and TIMEDIFF(`begin`,'$now')<=0 and TIMEDIFF(`end`,'$now')>0";
            } else if ($statusStr == AuctionStrStatus::UPCOMING) {
                $where = " `status`= $status and TIMEDIFF(`begin`,'$now')>0";
            } else {
                $where = " `status`= $status and TIMEDIFF(`end`,'$now')>0 ";
            }
            if (isset($keyword) && !empty($keyword)) {
                $where .= " and (`title` LIKE (%s) or `properties` LIKE (%s)  or `tags` like (%s)) ";
            }

            if (isset($category) && !empty($category)) {
                $where .= " and `category` LIKE (%s)";
                $query = "SELECT COUNT(id) FROM `" . $table . "` WHERE " . $where . " order by `end` asc";

                if (isset($keyword) && !empty($keyword)) {
                    $sql = $wpdb->prepare($query, '%' . like_escape($keyword) . '%', '%' . like_escape('"value":"' . $keyword) . '%', '%,' . like_escape($category) . ',%');
                } else {
                    $sql = $wpdb->prepare($query, '%,' . like_escape($category) . ',%');
                }
            } else {
                $query = "SELECT COUNT(id) FROM `" . $table . "` WHERE " . $where . " order by `end` asc";
                if (isset($keyword) && !empty($keyword)) {
                    $sql = $wpdb->prepare($query, '%' . like_escape($keyword) . '%', '%' . like_escape('"value":"' . $keyword) . '%', '%' . like_escape($keyword) . '%');
                } else {
                    $sql = $wpdb->prepare($query, "");
                }
            }
            $total_items = $wpdb->get_var($sql);
            return $total_items;
        }

        public static function get_auction_list($category = null, $statusStr = null, $limit = 20, $offset = 0, $keyword = null)
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $status = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            if ($statusStr == AuctionStrStatus::RUNNING) {
                $where = " `status`= $status and TIMEDIFF(`begin`,'$now')<=0 and TIMEDIFF(`end`,'$now')>0";
            } else if ($statusStr == AuctionStrStatus::UPCOMING) {
                $where = " `status`= $status and TIMEDIFF(`begin`,'$now')>0";
            } else {
                $where = " `status`= $status and TIMEDIFF(`end`,'$now')>0 ";
            }
            if (isset($keyword) && !empty($keyword)) {
                $where .= " and (`title` LIKE (%s) or `properties` LIKE (%s) or `tags` LIKE (%s)) ";
            }
            if (isset($category) && !empty($category)) {
                $where .= " and `category` LIKE (%s)";
                $query = "select * from `" . $table . "` WHERE " . $where . " order by ( case when `BEGIN` > now() then 1 else 0 end ),`end` asc LIMIT %d OFFSET %d";
                if (isset($keyword) && !empty($keyword)) {
                    $sql = $wpdb->prepare($query, '%' . like_escape($keyword) . '%', '%' . like_escape('"value":"' . $keyword) . '%', '%,' . like_escape($category) . ',%', $limit, $offset);
                } else {
                    $sql = $wpdb->prepare($query, '%,' . like_escape($category) . ',%', $limit, $offset);
                }
            } else {
                $query = "select * from `" . $table . "` WHERE " . $where . " order by ( case when `BEGIN` > now() then 1 else 0 end ),`end` asc LIMIT %d OFFSET %d";
                if (isset($keyword) && !empty($keyword)) {
                    $sql = $wpdb->prepare($query, '%' . like_escape($keyword) . '%', '%' . like_escape('"value":"' . $keyword) . '%', '%' . like_escape($keyword) . '%', $limit, $offset);
                } else {
                    $sql = $wpdb->prepare($query, $limit, $offset);
                }
            }
            $auctions = $wpdb->get_results($sql);
            if (count($auctions) > 0)
                return $auctions;

            return false;
        }

        public static function query_single()
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $status = AuctionStatus::NORMAL;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $where = " `status`= $status and TIMEDIFF(`end`,'$now')>0 ";
            $limit = 1;
            $sql = "select * from `" . $table . "` WHERE " . $where . " order by `end` asc LIMIT 0 , " . $limit;
            $auctions = $wpdb->get_results($sql);
            if (count($auctions) > 0)
                return $auctions [0];
            return false;
        }

        public static function exist_page($content)
        {
            global $wpdb;
            $query = "select id from `" . $wpdb->posts . "` where `post_content`='$content'";
            $result = $wpdb->get_results($query);
            if ($result) {
                if (count($result)) {
                    $post = $result[0];
                    return $post->id;
                }
            }
            return false;
        }

        public static function reactivate($ids)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;
            $now = date("Y-m-d H:i:s", current_time('timestamp'));
            $status = AuctionStatus::NORMAL;
            $str = "date_add('$now', interval TIMEDIFF(end, begin) day_second)";
            $query = "Update $table_name set `end`=" . $str . " ,`begin`='" . $now . "' WHERE `last_bids`='' and TIMEDIFF(`end`,'$now')<0 and `status`=$status and id IN($ids)";
            $wpdb->query($query);
            $begin = " '" . $now . "' as `begin`";
            $end = " date_add('$now', interval TIMEDIFF(end, begin) day_second) as `end`";
            $cols = " (`title`, `brand`, `tags`, `picture`, `category`, `properties`, `begin`, `end`, `original_price`, `start_price`, `step_price`, `amount`, `description`,`status`) ";
            $selected_cols = " `title`, `brand`, `tags`, `picture`, `category`, `properties`, $begin, $end, `original_price`, `start_price`, `step_price`, `amount`, `description`, $status as `status` ";
            $query2 = "INSERT INTO $table_name $cols select $selected_cols from $table_name WHERE `last_bids`>'' and  TIMEDIFF(`end`,'$now')<0 and `status`=$status and id IN($ids)";
            $wpdb->query($query2);
        }

        public static function getUserBids($bids, $userId)
        {
            $array = array();
            foreach ($bids as $bid) {
                if ($bid->user_id == $userId) {
                    $array[] = $bid;
                }
            }
            return $array;
        }

        private static function insertBid($auctionId, $price, $userId, $username, $email, $auction)
        {
            global $wpdb;
            $bid = array(
                "auction_id" => $auctionId,
                "user_id" => $userId,
                "user_name" => $username,
                "user_email" => $email,
                "quantity" => 1,
                "price" => $price,
                "created" => date('Y-m-d H:i:s', current_time("timestamp"))
            );
            $isDutchAuction = $auction->auction_type == AuctionType::DUTCH_AUCTION;
            $wpdb->insert($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_BID, $bid);
            $result = new stdClass ();
            $bidCount = $isDutchAuction ? $auction->amount : self::BID_PAGE_SIZE;
            $last_bids = self::get_last_bids($auctionId, $bidCount, $bidCount);
            $bids = json_encode($last_bids);
            self::update_last_bids($bids, $auctionId, $price, $auction->auction_type);
            $bidPrice = $isDutchAuction ? $auction->bid_price : $price;
            $result->above_reserve_price = floatval($price) >= floatval($auction->reserve_price);
            $result->last_bids = $last_bids;
            $result->id = $auctionId;
            $result->last_bids = SC_functions::formatBids($last_bids, true);
            $result->bid_price = $bidPrice;
            $result->bid_str_price = SC_functions::money_format($price);
            $result->step_price = $auction->step_price;
            return $result;
        }

        private static function canUserBidDutchAuction($auctionId, $price, $userId, $auction)
        {
            $amount = $auction->amount;
            $bids = self::get_last_bids($auctionId, $amount);
            if (empty($bids))
                return true;

            $lowestBid = end($bids);
            if ($price > $lowestBid->price) {
                $lastUserBids = self::getUserBids($bids, $userId);
                if (empty($lastUserBids))
                    return true;
                else {
                    $result = new stdClass ();
                    $result->error = __("already bid", "dam-auction-masters");
                    return $result;
                }
            }
            return false;
        }

        private static function insertDemoAuctions()
        {
            global $wpdb;
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_AUCTION;


            $data = array(
                "title" => "Apple",
                "picture" => plugin_dir_url(__FILE__) . "../assets/images/apple.jpg",
                "begin" => date('Y-m-d H:i:s', current_time("timestamp") + 24 * 60 * 60),
                "end" => date('Y-m-d H:i:s', current_time("timestamp") + 48 * 60 * 60),
                "start_price" => 10,
                "step_price" => 1,
                "bid_price" => 10,
                "status" => AuctionStatus::NORMAL,
                "amount" => 10,
                "description" => "The apple is the pomaceous fruit of the apple tree, species Malus domestica in the rose family (Rosaceae). It is one of the most widely cultivated tree fruits, and ..."
            );


            $wpdb->insert($table, $data);

            // -------------------------------------------------------------------

            $data = array(
                "title" => "Bananas",
                "picture" => plugin_dir_url(__FILE__) . "../assets/images/bananas.jpg",
                "begin" => date('Y-m-d H:i:s', current_time("timestamp") - 24 * 60 * 60),
                "end" => date('Y-m-d H:i:s', current_time("timestamp") + 2 * 60 * 60),
                "start_price" => 5,
                "step_price" => 2,
                "bid_price" => 5,
                "status" => AuctionStatus::NORMAL,
                "amount" => 100,
                "description" => "A banana is an edible fruit produced by several kinds of large herbaceous flowering plants of the genus Musa. (In some countries, bananas used for cooking ..."
            );

            $wpdb->insert($table, $data);

            // -------------------------------------------------------------------

            $data = array(
                "title" => "Pineapple",
                "picture" => plugin_dir_url(__FILE__) . "../assets/images/pineapple.jpg",
                "begin" => date('Y-m-d H:i:s', current_time("timestamp")),
                "end" => date('Y-m-d H:i:s', current_time("timestamp") + 45 * 60),
                "start_price" => 10,
                "step_price" => 5,
                "bid_price" => 10,
                "original_price" => 300,
                "reserve_price" => 200,
                "status" => AuctionStatus::NORMAL,
                "amount" => 10,
                "description" => "The pineapple (Ananas comosus) is a tropical plant with edible multiple fruit consisting of coalesced berries."
            );


            $wpdb->insert($table, $data);

            // ------------------------------------------------------------------------

            $data = array(
                "title" => "Grapes",
                "picture" => plugin_dir_url(__FILE__) . "../assets/images/grapes.jpg",
                "begin" => date('Y-m-d H:i:s', current_time("timestamp") - 24 * 60 * 60),
                "end" => date('Y-m-d H:i:s', current_time("timestamp") - 60),
                "start_price" => 50,
                "step_price" => 10,
                "bid_price" => 50,
                "original_price" => 120,
                "reserve_price" => 200,
                "shipping_fee" => 5.5,
                "status" => AuctionStatus::NORMAL,
                "amount" => 10,
                "description" => "A grape is a fruiting berry of the deciduous woody vines of the botanical genus Vitis. Grapes can be eaten raw or they can be used for making wine, jam, juice."
            );
            $wpdb->insert($table, $data);
        }

        public static function deleteBids($auctionId)
        {
            global $wpdb;
            $result = $wpdb->delete($wpdb->prefix . DutchAuctionMasters::DAM_TABLE_BID, array("auction_id" => $auctionId));
            return $result;
        }

        public static function deleteBidsByPostId($postId)
        {
            $auction = self::get_auction_by_post_id($postId);
            if ($auction) {
                return self::deleteBids($auction->id);
            }
            return false;
        }

        public static function getProfileAddress($userId, $auctionId)
        {
            global $wpdb;
            /** @var  $wpdb wpdb */
            $table = $wpdb->prefix . DutchAuctionMasters::DAM_TABLE_SHIPPING_ADDRESS;
            $auctionId = isset($auctionId)?intval($auctionId):0;
            $where = " `user_id`= %d and `auction_id` in (0,%d) order by `auction_id` desc limit 0,1";
            $query = "select * from `" . $table . "` WHERE " . $where;
            $sql = $wpdb->prepare ( $query, $userId, $auctionId);
            $items = $wpdb->get_results ( $sql );
            if (count ( $items ) > 0)
                return $items [0];

            return false;
        }
    }
}