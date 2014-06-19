<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

class dam_cron_job
{
    public static function checkAuctionEnding()
    {
        $ending_auctions = SC_DataProvider::get_ending_auctions();
        if (count($ending_auctions) > 0) {
            foreach ($ending_auctions as $ending_auction) {
                $last_bids = $ending_auction->last_bids;
                if (!empty ($last_bids)) {
                    $last_bids_array = json_decode(stripslashes($last_bids));
                    $last_bid = $last_bids_array[0];
                    if (floatval($ending_auction->bid_price) >= floatval($ending_auction->reserve_price)) {
                        @self::sendWonEmails($last_bid, $ending_auction);
                        $winner = array("id" => $last_bid->user_id, "email" => $last_bid->user_email, "name" => $last_bid->user_name);
                        $str_winner = json_encode($winner);
                        SC_DataProvider::updateAuction(array(
                            "winner" => $str_winner,
                            "winner_id" => $last_bid->user_id
                        ), array(
                            "id" => $ending_auction->id
                        ));
                    }
                }
                SC_DataProvider::updateAuction(array(
                    "notified" => 1
                ), array(
                    "id" => $ending_auction->id
                ));
            }
        }
    }

    public static function sendWonEmails($latest_bid, $auction)
    {
        $admin_email = get_option("dam_auction_admin_email");

        $default = SC_DefaultTemplate::getEmailTemplate(SC_DefaultTemplate::WON_NOTIFY_TO_ADMIN);
        $template = apply_filters("dam_text_template" , $default, SC_DefaultTemplate::WON_NOTIFY_TO_ADMIN);
        SC_functions::notify_to_admin($admin_email, $auction, $template);

        $default = SC_DefaultTemplate::getEmailTemplate(SC_DefaultTemplate::WON_NOTIFY_TO_USER);
        $template = apply_filters("dam_text_template" , $default, SC_DefaultTemplate::WON_NOTIFY_TO_USER);

        SC_functions::notify_to_user($latest_bid->user_email, $latest_bid->user_name, $auction, $template);
    }
}