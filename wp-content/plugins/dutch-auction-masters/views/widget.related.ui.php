<?php

$auctions = SC_functions::getRelatedAuction($auction_id, $total);
if (!$auctions) {
    _e("No auctions", "dam-auction-masters");
    return;
}

foreach ($auctions as $auction) {

    $picture = SC_functions::getMainPictureUrl($auction->picture);
    $item = new stdClass ();
    $item->title = (strlen($auction->title) > 18) ? substr($auction->title, 0, 16) . '...' : $auction->title;
    $item->alttitle = $auction->title;
    $item->auction_link = get_permalink($auction->post_id);
    $item->pic = SC_functions::getMainPictureUrl($auction->picture);
    $item->description = $auction->description;
    $item->auction_ID = $auction->id;
    $item->end = strtotime($auction->end) - current_time('timestamp');
    $item->originalPrice = SC_functions::money_format($auction->original_price);
    $item->advicePrice = SC_functions::money_format($auction->start_price);
    $item->biddingPrice = SC_functions::money_format(floatval($auction->bid_price) > 0 ? $auction->bid_price : $auction->start_price);
    $item->category = $auction->category;
    $item->start_time = date('Y-m-d H:i', strtotime($auction->begin));
    $item->started = strtotime($auction->begin) <= current_time('timestamp');

    ?>

    <div class="auction clearBoth related-items">
        <div class="picture">
            <a href="<?php echo $item->auction_link; ?>"> <img src="<?php echo $picture; ?> "/></a>
        </div>
        <h5 class="title"> <?php echo $item->title; ?> </h5>

        <div class="price">
            <div>
                <del>
                    <span> <?php echo $item->originalPrice; ?> </span>
                </del>
                / <span> <?php echo $item->biddingPrice; ?> </span>
            </div>
        </div>
        <div class="bar clearfix">
            <?php if ($item->started && $item->end > 0) {
                ?>
                <div class="auction-timer">
                    <input class="time-value" name='time'
                           value="<?php echo $item->end; ?>" type="hidden"><b
                        data-name="hour"></b><?php _e("h", "dam-auction-masters") ?> <b
                        data-name="min"></b><?php _e("m", "dam-auction-masters") ?> <b
                        data-name="sec"></b><?php _e("s", "dam-auction-masters") ?> <b
                        class="arrow-right"></b>
                </div>
            <?php } else if ($item->end > 0) { ?>

                <div class="auction-start-time">
                    <strong><?php _e("Start", "dam-auction-masters"); ?></strong>
                    &nbsp;<span> <?php echo $item->start_time; ?></span>
                </div>

            <?php } ?>
            <?php if ($item->end > 0) { ?>
                <a href="<?php echo $item->auction_link; ?>"
                   class="submit buttons"><?php _e("Bid me", "dam-auction-masters") ?></a>
            <?php } ?>
        </div>
    </div>
<?php }