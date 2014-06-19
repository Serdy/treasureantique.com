<?php

if (!class_exists('SC_DefaultViews')) {

    class SC_DefaultViews
    {
        const TEXT_DOMAIN = "dam-auction-masters";

        public function __construct()
        {
            add_filter("dam_auction_picture", array($this, "renderPicture"), 10, 1);
            add_filter("dam_auction_detail_" . AuctionType::COMMON, array($this, "render"), 10, 1);
            add_filter("dam_auction_detail_script", array($this, "renderScript"), 10, 1);
            add_filter("dam_auction_bid_list_view", array($this, "renderBidList"), 10, 1);
            add_filter("dam_auction_address_list_view", array($this, "renderAddressView"), 10, 1);
            add_action('wp_footer', array($this, 'renderLogin'));
        }

        public function render($model)
        {
            echo "<div class='wrap column'>";
            $this->renderHtml($model);
            echo "</div>";
        }

        public function renderAddressView()
        {
            ?>
            <div class="wrap">
                <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                <h2> <?php _e("Delivery address", "dam-auction-masters"); ?> </h2>

                <?php if (!empty($notice)): ?>
                    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
                <?php endif; ?>

                <?php if (!empty($message)): ?>
                    <div id="message" class="updated"><p><?php echo $message ?></p></div>
                <?php endif; ?>

                <form id="form" method="POST">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce($action_nonce) ?>"/>
                    <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                    <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                    <div class="metabox-holder" id="poststuff">
                        <div id="post-body">
                            <div id="post-body-content">
                                <?php do_meta_boxes($form_id, 'normal', $item); ?>
                                <input type="submit" value="<?php _e('Save', 'dam-auction-masters') ?>" id="save"
                                       class="button-primary" name="submit"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php
        }

        public function renderBidList($table)
        {
            ?>
            <div class="wrap">
                <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
                <h2><?php _e('Bids', 'dam-auction-masters') ?>
                </h2>

                <form id="bids-table" method="GET">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                    <?php $table->display() ?>
                </form>
            </div>
        <?php
        }

        public function renderScript($auction)
        {
            ?>
            <script type="text/javascript">
                <!--
                jQuery(document).ready(function ($) {
                    <?php
                        echo "var bids = [];";
                        $bids = SC_functions::getFormattedBids ( $auction->id ,true);
                        $json = json_encode ( $bids );
                        echo " bids = " . $json . ";";
                    ?>
                    var locked = false;
                    var lockPolling = false;

                    var showLogin = function () {
                        $(".sign-form-inputs").show();
                        $(".validate-message").hide();
                        $("#login_model").reveal();
                    };

                    var showClosedUi = function (message) {
                        $('#auction_detail_<?php echo $auction->id; ?>').find(".action-on-close").hide();
                        $('#auction_detail_<?php echo $auction->id; ?>').find(".status-message").html(message);
                    };
                    var auctionView = {
                        bids: ko.observableArray(bids),
                        lowestPrice: ko.observable(<?php echo floatval($auction->bid_price); ?>),
                        currentPrice: ko.observable(<?php echo floatval($auction->bid_price); ?>),
                        currentPriceStr: ko.observable('<?php echo SC_functions::money_format($auction->bid_price); ?>'),
                        inputPrice: ko.observable(<?php echo floatval($auction->bid_price)+ floatval($auction->step_price); ?>),
                        stepPrice: <?php echo floatval($auction->step_price);?>,
                        closed: ko.observable(false),
                        addPrice: function () {
                            var input = (this.stepPrice * 100 + this.inputPrice() * 100) / 100;
                            if (input > this.lowestPrice())
                                this.inputPrice(input);
                        },
                        reducePrice: function () {
                            var input = (this.inputPrice() * 100 - this.stepPrice * 100) / 100;
                            if (input > this.lowestPrice())
                                this.inputPrice(input);
                        },
                        placeBid: function (form) {
                            var input = this.inputPrice();
                            var self = this;
                            if (locked)
                                return;
                            locked = !locked;
                            $.ajax({
                                type: "POST",
                                url: dam_ajax.ajaxUrl,
                                data: $(form).serialize(),
                                dataType: 'json',
                                timeout: 10000,
                                success: function (res) {
                                    if (res.last_bids) {
                                        self.bids(res.last_bids);
                                        self.lowestPrice(res.bid_price);
                                        self.currentPrice(res.bid_price);
                                        self.inputPrice(parseFloat(res.bid_price) + parseFloat(self.stepPrice));
                                        self.currentPriceStr(res.bid_str_price);
                                    } else if (res.error == "unsign") {
                                        showLogin();
                                    }
                                    locked = false;
                                },
                                error: function (res) {
                                    locked = false;
                                }
                            });
                        },
                        polling: function () {
                            var self = this;
                            var closed = self.closed();
                            if (closed || lockPolling)
                                return;
                            lockPolling = !lockPolling;

                            $.ajax({
                                type: "POST",
                                url: dam_ajax.ajaxUrl,
                                data: {'action': 'ajaxHandle', 'act': 'status', 'id': <?php echo $auction->id ?> },
                                dataType: 'json',
                                timeout: 10000,
                                success: function (res) {
                                    if (res.last_bids) {
                                        self.bids(res.last_bids);
                                        self.lowestPrice(res.bid_price);
                                        self.currentPrice(res.bid_price);
                                        self.inputPrice(parseFloat(res.bid_price) + parseFloat(self.stepPrice));
                                        self.currentPriceStr(res.bid_str_price);
                                        if (res.closed == true) {
                                            self.closed(true);
                                            showClosedUi(res.message);
                                        } else if (res.ending == true) {
                                            showClosedUi(res.message);
                                        }
                                    }
                                    lockPolling = false;
                                },
                                error: function (res) {
                                    lockPolling = false;
                                }
                            });
                        }
                    };
                    ko.applyBindings(auctionView, document.getElementById("auction_detail_<?php echo $auction->id; ?>"));
                    function polling() {
                        auctionView.polling();
                    }
                    setInterval(polling, 5000);
                });
                //-->
            </script>

            <script type="text/javascript">
                jQuery(document).ready(function($){
                    var options = {
                        zoomType: 'standard',
                        lens:true,
                        preloadImages: true,
                        alwaysOn:false,
                        zoomWidth: 420,
                        zoomHeight:420,
                        xOffset:8,
                        yOffset:0,
                        position:'right'
                        //...MORE OPTIONS
                    };
                    $('.zoom-a').jqzoom(options);
                });
            </script>
        <?php
        }

        public function renderPicture($model)
        {
            extract($model);
            if(!isset($auction))
                return;
            $display_pictures = isset($display_pictures)?$display_pictures:true;
            $display_action = isset($display_action)?$display_action:true;
            $disablePicture = $display_pictures? '':'no-pics';
            $displayLink = $display_action?'':'show-link';

            if(strpos($auction->picture, ',') !== false)
            {
                $pictures = explode(',',$auction->picture);
                $picture = reset($pictures);
            }
            else
            {
                $picture = $auction->picture;
            }
            $src = preg_replace('/-\d+x\d+/', '', $picture);
            $title = $auction->title;
            echo "<div class='picture column'>";
            echo "<div class='main-pic-wrap $disablePicture $displayLink'><a rel='gal1' href='$src' title='$title' class='main-pic-wrap-a zoom-a'><img class='main-picture' src='$src' /></a></div>";

            if(isset($pictures) && $display_pictures)
            {
                $first = true;
                echo "<ul>";
                foreach($pictures as $picture)
                {
                    $largePic = preg_replace('/-\d+x\d+/', '', $picture);
                    $class = $first?"class='zoomThumbActive'":"";
                    echo "<li><a $class href='javascript:void(0);' rel=\"{gallery: 'gal1', smallimage:'$largePic',largeimage: '$largePic' }\" >";
                    echo "<img src='$picture' style='max-height: 50px;max-width: 50px;' /></a></li>";
                    $first = false;
                }
                echo "</ul>";
            }
            echo  "</div>";
        }

        public function renderHtml($model)
        {
            extract($model);
            $auction = isset($auction) ? $auction : null;
            $auctionTitle = isset($auction) ? $auction->title : "";
            $isRunning = isset($isRunning) ? $isRunning : false;
            $leftTime = isset($leftTime) ? $leftTime : 0;
            $begin = isset($begin) ? $begin : false;
            $action_nonce = isset($action_nonce) ? $action_nonce : "";
            $displayCustomProperties = isset($display_customprops) ? $display_customprops : false;
            $display_action = isset($display_action) ? $display_action : false;
            $this->renderTitle($auctionTitle);
            $this->renderProperties($auction, $displayCustomProperties, $isRunning, $leftTime, $begin);
            $this->renderActionArea($display_action, $isRunning, $auction, $action_nonce);
            $this->renderBiddingList();
        }

        public function renderTitle($auctionTitle)
        {
            echo "<h3 class='auction-title'>";
            echo $auctionTitle;
            echo "</h3>";
        }

        public function renderTimer($isRunning, $leftTime, $begin, $auction)
        {
            if ($isRunning) {
                $this->renderRunningTimer($leftTime);
            } else if ($begin > 0) {
                $this->renderUpcoming($auction->begin, $begin);
            }
        }

        public function renderActionArea($display_action, $is_running, $auction, $action_nonce)
        {
            if ($display_action) {
                ?>
                <div class="status-message"> <?php echo SC_functions::get_auction_closed_message($auction); ?> </div>
                <div id="msg-below-reserve-price" style="display:none;width:300px; font-weight:bold;">
                    <?php _e('Your bidding price has not reached the reserve price yet!', self::TEXT_DOMAIN); ?>
                </div>

                <div class="action-form-wrap action-on-close">
                    <?php if ($is_running) { ?>
                        <form class="auction-action-form form-inline" action=""
                              name="form-auction-<?php echo $auction->id ?>" data-bind="submit:placeBid">
                            <input type="hidden" name="action" value="ajaxHandle"/>
                            <input type="hidden" name="action_type" class="type"
                                   value="<?php echo $auction->auction_type ?>"/>
                            <input type="hidden" name="act" value="place_bid"/>
                            <input type="hidden" name="formurl" value="<?php echo $action_nonce; ?>"/>
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce($action_nonce) ?>"/>
                            <input type="hidden" id="auction_id" name="id" value="<?php echo $auction->id; ?>"/>
                            <input type="hidden" class="step-price" name="step_price"
                                   value="<?php echo $auction->step_price; ?>"/>

                            <div class="input-prepend input-append">
                                <button type="button" class="btn reduce" data-bind="click:reducePrice">-</button>
                                <input
                                    id="auction_<?php echo $auction->id; ?>"
                                    name="bid_price"
                                    type="text"
                                    class="bid-price-input appendedPrependedInput span1"
                                    data-bind="value:inputPrice"
                                    />
                                <button type="button" class="btn increase" data-bind="click:addPrice">+</button>
                            </div>
                            <button type="submit"
                                    class="btn btn-bid"><?php _e("Bid it now", self::TEXT_DOMAIN) ?></button>
                        </form>
                    <?php } ?>
                </div>
            <?php
            }
        }

        public function renderBiddingList()
        {
            ?>
            <div class="bidding-list">
                <ul class="bids" data-bind="foreach: bids">
                    <li class="bid">
						<span> <em data-bind="text:created"></em>
                                    &nbsp;&nbsp;<?php _e("by", self::TEXT_DOMAIN); ?>&nbsp;&nbsp;
                                    <em data-bind="text:user_name"></em>
					    </span>
                        <span class="bid-price"><b data-bind="html:price"></b></span>&nbsp;&nbsp;
                    </li>
                </ul>
            </div>
        <?php
        }

        public function renderPrices($auction)
        {
            $fields = array(
                "original_price" => __("Original", self::TEXT_DOMAIN),
                "shipping_fee" => __("Shipping fee", self::TEXT_DOMAIN),
                "bid_price" => __("Current", self::TEXT_DOMAIN)
            );

            foreach ($fields as $key => $text) {
                if (isset($auction->$key) && floatval($auction->$key)) {
                    echo "<dt>$text:</dt>";
                    $data = $auction->$key;
                    $price = SC_functions::money_format($auction->$key);
                    if ('original_price' == $key)
                        echo "<dd><del>$price</del></dd>";
                    else if ('bid_price' == $key)
                        echo "<dd><span class='lowest-price $key' data-bind='text:currentPriceStr' data-price='$data' >$price</span></dd>";
                    else
                        echo "<dd><span class='$key' data-price='$data'>$price</span></dd>";
                }
            }
        }

        public function renderCustomProperties($auction, $displayCustomProperties)
        {
            if (!empty($auction->properties) && $displayCustomProperties) {
                $props = json_decode($auction->properties);
                ?>
                <dl class="properties dl-horizontal">
                    <?php
                    foreach ($props as $prop) {
                        ?>
                        <dt><?php echo ucfirst($prop->key); ?>:</dt>
                        <dd><?php echo $prop->value; ?></dd>
                    <?php
                    }
                    ?>
                </dl>
            <?php
            }
        }

        public function renderRunningTimer($leftTime)
        {
            ?>
            <dt class="timer action-on-close"><?php _e("Time left:", self::TEXT_DOMAIN); ?></dt>
            <dd class="timer auction-timer action-on-close"><input type="hidden"
                                                                   value="<?php echo $leftTime; ?>"
                                                                   name="time" class="time-value"><b
                    data-name="hour"></b> h <b
                    data-name="min"></b> m <b data-name="sec"></b> s
            </dd>
        <?php
        }

        public function renderUpcoming($upcomingTime, $begin)
        {
            ?>
            <dt><?php _e("Time start:", self::TEXT_DOMAIN); ?></dt>
            <dd>
                <?php echo $upcomingTime; ?>
                <input type="hidden" class="upcoming_time"
                       value="<?php echo $begin; ?>"/>
            </dd>
        <?php
        }

        public function renderProperties($auction, $displayCustomProperties, $isRunning, $leftTime, $begin)
        {
            echo "<dl class='properties dl-horizontal'>";
            $this->renderTimer($isRunning, $leftTime, $begin, $auction);
            $this->renderPrices($auction);
            if ($auction->tags && trim($auction->tags, ',')) {
                ?>
                <dt><?php _e("Tags", self::TEXT_DOMAIN) ?>:</dt>
                <dd><?php echo trim($auction->tags, ','); ?></dd>
            <?php
            }
            $this->renderCustomProperties($auction, $displayCustomProperties);
            echo "</dl>";
        }

        public function renderLogin()
        {
            ?>
            <div id="login_model" class="pop-modal" tabindex="-1"
                 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <form name="sign_validation_email" class="sign_validation_email">
                    <input type="hidden" name="action" value="ajaxHandle" />
                    <input type="hidden" name="act" value="login" />
                    <div class="modal-header">
                        <h3 id="myModalLabel"><?php _e("User log in", "dam-auction-masters"); ?></h3>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="ajaxHandle"/>
                        <dl class="dl-horizontal sign-form-inputs">
                            <dt><?php _e("Username", "dam-auction-masters"); ?></dt>
                            <dd>
                                <input name="username" type="text"/>
                            </dd>
                            <dt><?php _e("Password", "dam-auction-masters"); ?></dt>
                            <dd>
                                <input name="password" type="password"/>
                            </dd>
                        </dl>
                    </div>
                    <div class="modal-footer">
                        <div class="validate-message hide column"></div>
                        <button type="button" class="btn btn-register" data-dismiss="modal"
                                aria-hidden="true"><?php _e("Register", "dam-auction-masters") ?></button>
                        <button type="submit"
                                class="btn btn-primary"><?php _e("Log in", "dam-auction-masters") ?></button>
                    </div>
                </form>
            </div>
        <?php
        }
    }

    new SC_DefaultViews();
}