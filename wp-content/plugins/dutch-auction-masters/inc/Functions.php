<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

if (!class_exists('SC_functions')) {

    class SC_functions
    {

        public static function initPlugin()
        {
            SC_DataProvider::initDatabase();
            $dbVersion = get_option(DutchAuctionMasters::PLUGIN_VERSION_KEY);
            if (empty($dbVersion)) {
                //SC_DataProvider::installDemoData();
                $list_id = SC_DataProvider::exist_page('[auction-list]');
                if (empty($list_id)) {
                    $list_id = SC_functions::addDemoPage("overview", '[auction-list]');
                }
                update_option(DutchAuctionMasters::DAM_AUCTION_LIST_PAGE, $list_id);
                update_option(DutchAuctionMasters::DAM_USER_ROLE, "subscriber");
                update_option(DutchAuctionMasters::DAM_AUCTION_AUTHENTICATED, true);
            }
        }

        public static function removeDATA()
        {
            SC_DataProvider::removeTables();
            SC_DataProvider::deleteDamAuctionPosts();
            delete_option(DutchAuctionMasters::PLUGIN_VERSION_KEY);
            delete_option(DutchAuctionMasters::DAM_AUCTION_AUTHENTICATED);
            delete_option(DutchAuctionMasters::DAM_USER_ROLE);
            delete_option(DutchAuctionMasters::DAM_AUCTION_LIST_PAGE);
        }

        public static function cronProcession()
        {
            dam_cron_job::checkAuctionEnding();
        }

        public static function frontTimeFormat($time)
        {
            return $time; //TODO
        }

        public static function displayLastBids($auction)
        {
            $result = "";
            $last_bids = $auction['last_bids'];
            $auction_id = $auction['id'];
            if (!empty ($last_bids)) {
                $last_bids_array = json_decode(stripslashes($last_bids));
                $text = __('View all bids', DutchAuctionMasters::TEXT_DOMAIN);
                $result .= "<a href='./admin.php?page=dam_auction_masters_bids&auctionid=$auction_id'>$text</a>";
                $result .= "<ul>";
                foreach ($last_bids_array as $bid) {
                    if (property_exists($bid, "user_name") && property_exists($bid, "created") && property_exists($bid, "price")) {
                        $result .= "<li> <em>$bid->created</em> &nbsp; <span>" . $bid->price . " </span> <span>$bid->user_name </span> </li>";
                    }
                }
                $result .= "</ul>";
            }
            return $result;
        }

        public static function displayStatus($auction)
        {
            $mapping = array(
                0 => __("draft", DutchAuctionMasters::TEXT_DOMAIN),
                10 => __("publish", DutchAuctionMasters::TEXT_DOMAIN),
                20 => __("trash", DutchAuctionMasters::TEXT_DOMAIN),
                30 => __("pending", DutchAuctionMasters::TEXT_DOMAIN),
            );
            $begin = strtotime($auction ['begin']);
            $end = strtotime($auction ['end']);
            $status = $auction ['status'];
            $current_time = current_time('timestamp');
            $display_name = null;
            if ($status == AuctionStatus::NORMAL) {
                if ($begin > $current_time) {
                    $display_name = __("Upcoming", DutchAuctionMasters::TEXT_DOMAIN);
                } else if ($begin <= $current_time && $current_time <= $end) {
                    $display_name = __("Running", DutchAuctionMasters::TEXT_DOMAIN);
                } else if ($end < $current_time) {
                    $display_name = __("Closed", DutchAuctionMasters::TEXT_DOMAIN);
                }
            } else {
                $display_name = $mapping[$status];
            }
            return '<b>' . $display_name . '</b>';
        }

        public static function showMainPicture($columnName, $auction)
        {
            $picture = $auction->$columnName;
            if (strpos($picture, ',') !== false) {
                $pictures = explode(',', $picture);
                $picture = $pictures[0];
            }
            $img = "<img style='max-width:50px' src ='$picture' />";
            return $img;
        }

        public static function getRelatedPostIdsByTag($auction, $limit = 5)
        {
            if (empty($auction))
                return null;

            $tags = explode(',', $auction->tags);
            $tags = array_filter($tags);

            $categories = explode(',', $auction->category);
            $categories = array_filter($categories);

            $args = array(
                'numberposts' => $limit,
                'post_type' => 'dam_auction',
                'exclude' => $auction->post_id,
                'tax_query' => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'post_tag',
                        'field' => 'slug',
                        'terms' => $tags
                    ),
                    array(
                        'taxonomy' => 'category',
                        'field' => 'id',
                        'terms' => $categories,
                    ),
                ),
                'fields' => 'ids',
            );
            $postIds = get_posts($args);
            return $postIds;
        }

        public static function getRelatedAuction($auction_id, $limit = null)
        {
            if (!$auction_id)
                return false;
            $auction = SC_DataProvider::get_auction($auction_id);
            $postIds = self::getRelatedPostIdsByTag($auction, $limit);
            if (count($postIds) > 0) {
                $auctions = SC_DataProvider::getAuctionByPostIds($postIds);
                return $auctions;
            } else {
                return false;
            }
            // return SC_DataProvider::get_auctions_by_ids($auction_ids, $limit, $auction_id ); //TODO the status should be running;
        }

        public static function addDemoPage($title, $content)
        {
            $post = array(
                'ping_status' => 'open',
                'post_author' => 1,
                'post_content' => $content,
                'post_date' => date('Y-m-d H:i:59', current_time("timestamp")),
                'post_date_gmt' => date('Y-m-d H:i:59', current_time("timestamp") - 160379),
                'post_name' => $title,
                'post_status' => 'future',
                'post_title' => ucfirst($title),
                'post_type' => 'page',
            );
            $page_id = wp_insert_post($post);
            return $page_id;
        }

        public static function getMainPictureUrl($picture)
        {
            if (strpos($picture, ',') !== false) {
                $pictures = explode(',', $picture);
                $picture = $pictures[0];
            }
            return $picture;
        }

        public static function confirmReceiving($auction_id)
        {
            global $current_user;
            get_current_user();

            $new_state = SC_DataProvider::updateDealState($auction_id, DealState::ACCEPTED, DealState::DELIVERED, $current_user->ID);
            return $new_state;
        }

        public static function deliverGoods($auction_id)
        {
            $new_state = SC_DataProvider::updateDealState($auction_id, DealState::DELIVERED, DealState::PAID, null);
            return $new_state;
        }

        public static function paying($auction_id)
        {
            global $current_user;
            get_current_user();

            $new_state = SC_DataProvider::updateDealState($auction_id, DealState::PAYING, null, $current_user->ID);
            return $new_state;
        }

        public static function pay($auction_id, $txn_id)
        {
            $result = SC_DataProvider::updateDealStatePay($auction_id, DealState::PAID, $txn_id);
            return $result;
        }

        public static function is_active_auction_page($page)
        {
            $current = isset($_REQUEST["state"]) ? $_REQUEST["state"] : '';
            if ($page == $current) {
                return "active";
            }
            return "";
        }

        public static function add_property_key($key)
        {
            $keys = get_option("dam_property_keys");
            $new_key = '"' . $key . '",';
            if (!$keys) {
                update_option("dam_property_keys", ',' . $new_key);
            } else if (!strpos($keys, $new_key)) {
                update_option("dam_property_keys", $keys . $new_key);
            }
        }

        public static function get_property_keys()
        {
            return trim(get_option("dam_property_keys"), ',');
        }

        public static function getFormattedBids($auctionId, $hidePartialName = false, $bidCount = 3)
        {
            $bids = SC_DataProvider::get_last_bids($auctionId, $bidCount);
            return self::formatBids($bids, $hidePartialName);
        }

        public static function formatBids($bids, $hidePartialName = false)
        {
            if (count($bids) > 0) {
                foreach ($bids as $bid) {
                    $bid->price = self::money_format($bid->price);
                    if ($hidePartialName)
                        $bid->user_name = substr_replace($bid->user_name, '***', 1, 3);
                }
            }
            return $bids;
        }

        public static function money_format($number)
        {
            $decimal_point = get_option('dam_currency_decimal_point', ',');
            $thousands_sep = get_option('dam_currency_thousands_step', '.');
            $symbol = get_option('dam_currency_symbol', '&euro;');

            $money = number_format($number, 2, $decimal_point, $thousands_sep);
            return $symbol . ' ' . $money;
        }

        public static function get_auction_winnerId($auction)
        {
            if (!empty ($auction) && !empty ($auction->winner)) {
                $winner = json_decode($auction->winner);
                return $winner->id;
            }
            return false;
        }

        public static function Is_winner($auction)
        {
            global $current_user;
            get_current_user();

            $winner_id = self::get_auction_winnerId($auction);

            if ($winner_id == $current_user->ID) {
                return true;
            }
            return false;
        }

        public static function get_auction_closed_message($auction)
        {
            global $current_user;
            get_current_user();
            $left_time = strtotime($auction->end) - current_time('timestamp');
            $closed = $left_time < 0;
            if ($closed) {
                if ($current_user->ID && !empty ($auction->winner)) {
                    $is_winner = self::Is_winner($auction);
                    if ($is_winner) {
                        $defaultMessage = __("You've won the auction, an email will be sent to you shortly, please refer to it to know more details.", "dam-auction-masters");
                        $message = apply_filters("dam_auction_won_message", $defaultMessage, $auction->id);
                    } else {
                        $message = __("Sorry, the auction is closed and you are not the winner.", "dam-auction-masters");
                    }
                } else {
                    $message = __("The auction is closed", "dam-auction-masters");
                }
            }
            $message = isset($message) ? $message : "";
            return $message;
        }


        public static function getPayUrl($auctionId)
        {
            $defaultUrl = admin_url('admin.php?page=shipping-address');
            $payUrlSetting = get_option("dam_pay_url");
            $payUrlSetting = empty($payUrlSetting) ? $defaultUrl : $payUrlSetting;
            $url = add_query_arg(array("auction_id" => $auctionId), $payUrlSetting);
            return $url;
        }

        public static function get_auction_detail_url($auction_id)
        {
            $page_id = get_option("dam_auction_detail_page");

            if (empty ($page_id)) {
                return "javascript:nonepage();";
            } else {

                $detail_page_url = get_permalink($page_id);
                $detail_url = add_query_arg(array("id" => $auction_id), $detail_page_url);
                return $detail_url;
            }
        }

        public static function get_auction_list_url($category_name)
        {
            $page_id = get_option("dam_auction_list_page");

            if (empty ($page_id)) {
                return "javascript:nonepage();";
            } else {

                $page_url = get_permalink($page_id);

                $result = add_query_arg(array("category" => $category_name), $page_url);

                return $result;
            }
        }

        public static function get_adjusted_auctions($items)
        {

            if ($items && count($items) > 0) {

                $auctions = array();
                foreach ($items as $auction) {
                    $item = new stdClass ();
                    $item->title = (strlen($auction->title) > 18) ? substr($auction->title, 0, 16) . '...' : $auction->title;
                    $item->alttitle = $auction->title;
                    $item->auction_link = get_permalink($auction->post_id);
                    $item->pic = self::getMainPictureUrl($auction->picture);
                    $item->description = $auction->description;
                    $item->auction_ID = $auction->id;
                    $item->end = strtotime($auction->end) - current_time('timestamp');
                    $item->originalPrice = self::money_format($auction->original_price);
                    $item->advicePrice = self::money_format($auction->start_price);
                    $item->biddingPrice = self::money_format(floatval($auction->bid_price) > 0 ? $auction->bid_price : $auction->start_price);
                    $item->category = $auction->category;
                    $item->start_time = date('Y-m-d H:i', strtotime($auction->begin));
                    $item->started = strtotime($auction->begin) <= current_time('timestamp');
                    $auctions [] = $item;
                }

                return $auctions;
            }

            return false;
        }

        public static function get_auctions($status, $category = null, $keyword = null)
        {

            $total = SC_DataProvider::get_auction_count($category, $status, $keyword);

            $per_page = 20;

            $items = SC_DataProvider::get_auction_list($category, $status, $per_page, 0, $keyword);

            $result = new stdClass ();

            $auctions = self::get_adjusted_auctions($items);

            $result->auctions = $auctions;

            $result->total = $total;

            $result->islast = $total <= $per_page;

            return $result;
        }

        public static function get_auction_list($status, $category = null, $start = 0, $keyword = null)
        {
            $total = SC_DataProvider::get_auction_count($category, $status, $keyword);

            $per_page = 20;

            $offset = $start + $per_page; //alway start from second page.

            $items = SC_DataProvider::get_auction_list($category, $status, $per_page, $offset, $keyword);

            $result = new stdClass ();

            $auctions = self::get_adjusted_auctions($items);

            if ($auctions) {

                if ($status == AuctionStrStatus::RUNNING) {
                    $result->running = $auctions;
                } else if ($status == AuctionStrStatus::UPCOMING) {
                    $result->upcoming = $auctions;
                } else {
                    $result->all = $auctions;
                }
            }

            $result->total = $total;

            $result->islast = $total <= ($offset + $per_page);

            return $result;
        }

        public static function notify_to_admin($email, $auction, $email_template)
        {

            if (!empty ($email_template)) {

                $user_name = $auction->winner;
                $last_bids_array = json_decode(stripslashes($auction->last_bids));
                $admin_name = $last_bids_array [0]->user_name;
                $auction_name = $auction->title;

                $auction_detail_url = get_permalink($auction->post_id);
                $data = array(
                    "UserName" => $user_name,
                    "AdminName" => $admin_name,
                    "AuctionName" => $auction_name,
                    "SiteName" => get_option('blogname'),
                    "SiteUrl" => get_option('siteurl'),
                    "Logo" => "",
                    "AuctionDate" => $auction->end,
                    "AuctionUrl" => $auction_detail_url,
                    "Email" => $email
                );

                $email_template = self::apply_template($data, $email_template);
                $subject = $email_template['subject'];
                $email_body = $email_template['content'];

                $email = array(
                    "to" => $email,
                    "from" => get_option('dam_auction_admin'),
                    "from_name" => get_option('dam_auction_admin_name'),
                    "subject" => $subject,
                    "body" => $email_body
                );

                return self::sendEmail($email);
            }
            return false;
        }

        public static function notify_to_user($email, $username, $auction, $email_template)
        {
            require_once(ABSPATH . WPINC . '/class-phpmailer.php');

            if (!empty ($email_template)) {

                $user_name = $username;
                $auction_name = $auction->title;
                $auction_detail_url = get_permalink($auction->post_id);

                $data = array(
                    "UserName" => $user_name,
                    "AdminName" => get_option('dam_auction_admin_name'),
                    "AuctionName" => $auction_name,
                    "SiteName" => get_option('blogname'),
                    "SiteUrl" => get_option('siteurl'),
                    "Logo" => "",
                    "AuctionDate" => $auction->end,
                    "AuctionUrl" => $auction_detail_url,
                    "Email" => $email
                );
                $email_template = self::apply_template($data, $email_template);
                $subject = $email_template['subject'];
                $email_body = $email_template['content'];
                $email = array(
                    "to" => $email,
                    "from" => get_option('dam_auction_admin'),
                    "from_name" => get_option('dam_auction_admin_name'),
                    "subject" => $subject,
                    "body" => $email_body
                );

                return self::sendEmail($email);
            }
            return false;
        }

        public static function apply_template($data, $template)
        {
            if (!empty ($template)) {
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        $template['subject'] = str_replace("[$key]", $value, $template['subject']);
                        $template['content'] = str_replace("[$key]", $value, $template['content']);
                    } else {
                    }
                }
            }
            return $template;
        }

        public static function sendEmail($email)
        {
            require_once(ABSPATH . WPINC . '/class-phpmailer.php');
            $mail = new PHPMailer ();
            $mail->AddAddress($email ["to"]);
            $mail->IsMail();
            $mail->IsHTML();
            $mail->From = $email ["from"];
            $mail->FromName = $email ["from_name"];
            $mail->Subject = $email ["subject"];
            $mail->Body = $email ["body"];
            $rst = $mail->Send();
            return $rst;
        }

        public static function can_user_bid($user = null)
        {
            global $current_user;

            if (isset($user))
                $current_user = $user;
            else
                get_currentuserinfo();

            if (empty($current_user) || empty($current_user->ID) || empty($current_user->roles))
                return false;

            $allow_bid_roles = get_option("dam_user_role", "subscriber");
            $current_roles = $current_user->roles;

            if (is_array($allow_bid_roles)) {
                $has_role = count(array_intersect($current_roles, $allow_bid_roles)) > 0;
            } else {
                $has_role = in_array($allow_bid_roles, $current_roles);
            }

            return $has_role;
        }

        public static function authenticated_place_bid($auction_id, $bid_price)
        {
            global $current_user;
            get_currentuserinfo();
            $result = new stdClass ();
            $can_user_bid = self::can_user_bid();
            if ($can_user_bid && $current_user->display_name) {
                $name = $current_user->display_name;
                $userid = $current_user->ID;
                $email = $current_user->user_email;
                $result = SC_DataProvider::placeBid($auction_id, $bid_price, $userid, $name, $email);
            } else {
                $result->error = "unsign";
            }
            return $result;
        }

        public static function get_plugin_url()
        {
            return plugin_dir_url(__FILE__);
        }

        public static function unauthenticated_place_bid($auction_id, $bid_price)
        {
            global $dam_cookie;

            $user_name = $dam_cookie->value("user_name");
            $user_email = $dam_cookie->value("user_email");
            $result = new stdClass ();
            if (!empty ($user_name) && !empty ($user_email)) {
                $result = SC_DataProvider::placeBid($auction_id, $bid_price, 0, $user_name, $user_email);
            } else {
                $result->error = "unactived";
            }
            return $result;
        }

        public static function UpdateCurrentUserShippingAddress($data, $auction_id)
        {
            $user_id = get_current_user_id();
            $address = SC_DataProvider::get_address($user_id, $auction_id);
            if ($address) {
                SC_DataProvider::updateAddress($data, compact('user_id', 'auction_id'));
            } else {
                $data['user_id'] = $user_id;
                $data['auction_id'] = $auction_id;
                SC_DataProvider::insertAddress($data);
            }
        }
    }
}