<?php
/*
3
Template Name: moishablon
4
*/
?>
<?php
// Sidebar code goes here

  //  echo docommon_wp_auctions();;

   ?>
<!--WP-Auction - End -->  

 


<?php
function p_wp_auctions($id) {
 
   global $wpdb;

   $options = get_option('wp_auctions');
   $style = $options['style'];
   $currencysymbol = $options['currencysymbol'];
   $title = $options['title'];
   $feedback = $options['feedback'];
   $noauction = $options['noauction'];
   $otherauctions = $options['otherauctions'];
   $showrss = $options['showrss'];
   
   $chunks = explode('<!--more-->', $noauction);
   $chunkno = mt_rand(0, sizeof($chunks) - 1);
   $noauctiontext = $chunks[$chunkno];

   // select a random record
   $table_name = $wpdb->prefix . "wpa_auctions";

   $auction_id = isset($_GET["auction_to_show"]) ? $_GET["auction_to_show"] : "";

   if(!is_numeric($auction_id)) {
      $cond = "'".current_time('mysql',"1")."' < date_end AND id=$id order by rand() limit 1";
     
   } else {
      $cond = "id=".$auction_id;
      
   }

   $strSQL = "SELECT  id,image_url, name, description, date_end, duration, BIN_price, start_price, current_price, staticpage FROM ".$table_name." WHERE ".$cond;
   $row = $wpdb->get_row ($strSQL);
   if (empty($row)) {
     return ;
   }
   // grab values we need
   $image_url = $row->image_url;
   $name = $row->name;
   $description = substr($row->description,0,75)."...";
   $end_date = get_date_from_gmt($row->date_end);
   $current_price = $row->current_price;
   $BIN_price = $row->BIN_price;
   $start_price = $row->start_price;
   $id = $row->id;

   // show default image if no image is specified
   if ($image_url == "") $image_url = get_bloginfo('wpurl').PLUGIN_EXTERNAL_PATH."requisites/default.png";

   // cater for no records returned
   if ($id == '') {
?>

<!--WP-Auction - Sidebar Presentation Section -->     
<div id="wp-container">
   
  <?php if ($noauctiontext != '') { ?>
  <div style="border: 1px solid #ccc; padding: 5px 2px; margin: 0px !important; background: none !important;">
      <?php echo $noauctiontext ?>
  </div>

  <?php } else { //noauctiontext is blank ?>
    <div id="wp-head"><?php echo $title ?></div>

    <div id="wp-body">
      <div id="wp-image"><img src="<?php echo wpa_resize($image_url,125) ?>" width="125" height="125" /></div>
      <div class="wp-heading"><?php _e("No auctions found",'WPAuctions'); ?></div>
      <div id="wp-desc"><?php _e("Sorry, we seem to have sold out of everything we had!",'WPAuctions'); ?></div>
    <div id="wp-other"></div>
    </div>
    <div id="wp-bidcontainer"></div>
  <!-- Main WP Container Ends -->  
  <?php } ?>
</div>
<!--WP-Auction - End -->     
<?php  
} else {

   // select "other" auctions
   $table_name = $wpdb->prefix . "wpa_auctions";

   $thelimit = "";
   if ($otherauctions != 'all' && $otherauctions > 0) {
      $thelimit = " limit ".$otherauctions;
   }

   $strSQL = "SELECT id, name, staticpage  FROM ".$table_name." WHERE '".current_time('mysql',"1")."' < date_end and id<>".$id." order by rand()".$thelimit;
   $rows = $wpdb->get_results ($strSQL);

   // prepare auction link
   $auctionlink = '<a href="'.get_bloginfo('wpurl').PLUGIN_EXTERNAL_PATH . 'auction.php?ID=' . $id .POPUP_SIZE.'" class="thickbox" title="Bid Now">';

?>
<!--WP-Auction - Sidebar Presentation Section -->     
  <!-- Main WP Container Starts -->
  <div id="wp-container">
    <div id="wp-head"><?php echo $title ?></div>

    <div id="wp-body">
      <div id="wp-image"><?php echo $auctionlink; ?><img src="<?php echo wpa_resize($image_url,125) ?>" width="125" height="125" /></a></div>
      <div class="wp-heading"><?php echo $name ?></div>

      <div id="wp-desc"><?php echo $description; ?><span class="wp-more"> - <?php echo $auctionlink; ?>more...</a></span> </div>

      <div id="wp-date"><?php _e('Ending','WPAuctions'); ?>: <?php echo date('dS M Y H:i:s',strtotime($end_date)) ?></div>

      <?php if ($feedback!=''): ?>      
         <div id="wp-date"><a href="<?php echo $feedback ?>" target="_blank"><?php _e("My eBay feedback",'WPAuctions'); ?></a></div>
      <?php endif ?>

      <div id="wp-other">

    <?php if (!empty($rows)): ?>      
        <div class="wp-heading"><?php _e("Other Auctions",'WPAuctions'); ?></div>
        <ul>
      <?php foreach ($rows as $row) {  
         echo "<li>";
         echo "- <a href='".get_bloginfo('wpurl')."?auction_to_show=".$row->id."'>";
         echo $row->name;
         echo "</a></li>";
      } ?>
        </ul>
   <?php endif; ?>

   <?php if ($showrss != "No") { ?>

        <div class="wp-rss"><a href="<?php echo get_bloginfo('wpurl').PLUGIN_EXTERNAL_PATH.PLUGIN_NAME?>?rss"><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGIN_STYLE_PATH.$style?>/rss.png" alt="Auctions RSS Feed" border="0" title="Grab My Auctions RSS Feed"/></a> <a href="<?php echo get_bloginfo('wpurl').PLUGIN_EXTERNAL_PATH.PLUGIN_NAME?>?rss" title="Grab My Auctions RSS Feed" >Auctions RSS Feed</a></div>

   <?php } ?>

      </div>
    </div>
    <div id="wp-bidcontainer">
      <div id="wp-bidcontainerleft"><?php echo get_price($current_price,$start_price,$BIN_price,$currencysymbol,"<br>") ?></div>

      <div id="wp-bidcontainerright"><?php echo $auctionlink; ?><img src="<?php echo get_bloginfo('wpurl').'/'.PLUGIN_STYLE_PATH.$style?>/bidnow.png" alt="Bid Now" width="75" height="32" border="0" /></a> </div>

    </div>
    
  </div>
  <!-- Main WP Container Ends -->
<!--WP-Auction - End -->     


<?php

}

// hook to terminate auction if needed (not strictly correct, but more efficient if it's here)
check_auction_end($id); 

}
?>

<?php
/**
 * Page Template
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a page ('page' post_type) unless another page template overrules this one.
 * @link http://codex.wordpress.org/Pages
 *
 * @package WooFramework
 * @subpackage Template
 */
  get_header();
  global $woo_options;
?>

<div id="content" class="page col-full">
<link type="text/css" rel="stylesheet" href="../css/theme.css" media="screen" />    
      <?php woo_main_before(); ?>
      
    <section id="wp-main" class="col-left">      

 <?php
          if ( have_posts() ) { $count = 0;
            while ( have_posts() ) { the_post(); $count++;
        ?>                                                           
            <article <?php post_class(); ?>>
        
        <div id="wp-content">
                <?php

 //echo p_wp_auctions(4);
  for ($i=0; $i < 4000; $i++) { 
   echo p_wp_auctions($i);
  }
  // foreach ($p_wp_auctions as $key ) {
  //   echo $key;
  // }

  
?>
        </div>
                <section class="entry">
                  <?php the_content(); ?>

          <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) ); ?>
                </section><!-- /.entry -->

        <?php edit_post_link( __( '{ Edit }', 'woothemes' ), '<span class="small">', '</span>' ); ?>
                
            </article><!-- /.post -->
            
            <?php
              // Determine wether or not to display comments here, based on "Theme Options".
              if ( isset( $woo_options['woo_comments'] ) && in_array( $woo_options['woo_comments'], array( 'page', 'both' ) ) ) {
                comments_template();
              }

        } // End WHILE Loop
      } else {
    ?>
      <article <?php post_class(); ?>>
              <p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->
        <?php } // End IF Statement ?>  
        
    </section><!-- /#main -->
    
    <?php woo_main_after(); ?>

        <div ip="wp-sidebar"><?php get_sidebar(); ?></div>

    </div><!-- /#content -->
    
<?php get_footer(); ?>


