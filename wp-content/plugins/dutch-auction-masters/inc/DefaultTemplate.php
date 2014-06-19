<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */
if (!class_exists("SC_DefaultTemplate")) {
    class SC_DefaultTemplate
    {
        const ACTIVATION = "activation";
        const END_NOTIFY_TO_USER = "end_notify_to_user";
        const WON_NOTIFY_TO_USER = "won_notify_to_user";
        const END_NOTIFY_TO_ADMIN = "end_notify_to_admin";
        const WON_NOTIFY_TO_ADMIN = "won_notify_to_admin";
        const OVER_BID = "over_bid";

        public function __construct()
        {
            $this->emailTemplate = array(
                self::ACTIVATION => array(
                    "subject" => "Account activation",
                    "content" => "Dear [UserName],

You requested to validate your account, please click <a href='[AuctionUrl]'>here</a> to validate, if it's not clickable, please copy the URL below and paste it to your browser.

[AuctionUrl]

Regards,
Dutch auction masters team.<br/>"),
                self::WON_NOTIFY_TO_USER => array(
                    "subject" => "You have won an auction",
                    "content" => "Dear [UserName],

Congratulations, you've won an auction, click <a href='[AuctionUrl]'>here</a> for more details, if it's not clickable, please copy the URL below and paste it to your browser.

[AuctionUrl]

Regards,
Dutch auction masters team.<br/>"),
                self::END_NOTIFY_TO_USER => array(
                    "subject" => "Auction has ended",
                    "content" => "Dear [UserName],

The auction is ended, click <a href='[AuctionUrl]'>here</a> for more details, if it's not clickable, please copy the URL below and past it on your browser.

[AuctionUrl]

Regards,
Dutch auction masters team.<br/>"),
                self::WON_NOTIFY_TO_ADMIN => array(
                     "subject" => "Auction has ended",
                    "content" => "",),  //TODO
                self::END_NOTIFY_TO_ADMIN => array(
                    "subject" => "Auction has ended",
                    "content" => "",), //TODO

            );
        }

        public function getTemplate($type)
        {
            if (array_key_exists($type, $this->emailTemplate))
                return $this->emailTemplate[$type];
            else
                return $type;
        }

        public static function getEmailTemplate($type)
        {
            $instance = new SC_DefaultTemplate();
            return $instance->getTemplate($type);
        }
    }
}