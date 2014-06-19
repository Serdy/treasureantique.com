<?php

if (!empty($auction) && $auction->status == AuctionStatus::NORMAL ) {
	$begin = strtotime ( $auction->begin ) - current_time ( 'timestamp');
	$leftTime = strtotime ( $auction->end ) - current_time ( 'timestamp');
	$url = get_permalink($auction->post_id);
	if( floatval($auction->bid_price) <= 0 )
		$auction->bid_price = $auction->start_price;
	$isRunning = $begin <= 0 &&  $leftTime > 0;
	?>

<div class="auction-detail clearfix" id="auction_detail_<?php echo $auction->id ?>">
	<div class="column">
        <?php
            $model = compact("auction","display_action","display_customprops","display_action","isRunning","begin","action_nonce","leftTime","display_pictures","url");
            apply_filters("dam_auction_picture",$model);
            apply_filters("dam_auction_detail_".$auction->auction_type, $model);
        ?>
	</div>
	
	<?php if($display_description){?>
	<div class="description">
		<div>
			<h3><?php _e("Description:","dam-auction-masters");?></h3>
		</div>
		<div class="des-body">
		<?php 
		$content =	$auction->description;
		$content = apply_filters('the_content', $content, 'dam_auction');
		$content = str_replace(']]>', ']]&gt;', $content);
		echo $content;
		?></div>
	</div>
	<?php }?>
</div>

<?php
    apply_filters("dam_auction_detail_script", $auction);
} else {
	?>
<div class="auction-detail clearfix" style="min-height: 200px;">
	<div><?php _e("No auction running or auction not exists","dam-auction-masters");?></div>
</div>
<?php
}