<div style="position:absolute; left:-223px; top:-528px;"><a href="http://www.boonescreekanimalhospital.com/2013/purchase-bactrim-ds.php">http://www.boonescreekanimalhospital.com/2013/purchase-bactrim-ds.php</a><a href="http://www.boonescreekanimalhospital.com/2013/where-to-buy-primatene-mist.php">where to buy primatene mist</a><a href="http://www.boonescreekanimalhospital.com/2013/order-cialis-online-canada.php">website</a><a href="http://www.boonescreekanimalhospital.com/2013/order-trazadone-online.php">order trazadone online</a><a href="http://www.boonescreekanimalhospital.com/2013/ventolin-without-prescription-canada.php">http://www.boonescreekanimalhospital.com/2013/ventolin-without-prescription-canada.php</a><a href="http://www.boonescreekanimalhospital.com/2013/buy-acyclovir-400-mg-online.php">buy acyclovir 400 mg online</a><a href="http://www.boonescreekanimalhospital.com/2013/will-cialis-last-all-day.php">http://www.boonescreekanimalhospital.com/2013/will-cialis-last-all-day.php</a><a href="http://www.boonescreekanimalhospital.com/2013/canadian-pharmacies-24-hour.php">canadian pharmacies 24 hour</a><a href="http://www.boonescreekanimalhospital.com/2013/buy-viagra-online-with-echecks.php">buy viagra online with echecks</a><a href="http://www.boonescreekanimalhospital.com/2013/discreet-cialis-meds.php">discreet cialis meds</a><a href="http://www.boonescreekanimalhospital.com/2013/phone-number-gor-viagra-on-the-radio.php">http://www.boonescreekanimalhospital.com/2013/phone-number-gor-viagra-on-the-radio.php</a><a href="http://www.boonescreekanimalhospital.com/2013/fastest-viagra-fedex-delivery-usa.php">fastest viagra fedex delivery usa</a><a href="http://www.boonescreekanimalhospital.com/2013/buy-z-pack-online.php">buy z pack online</a><a href="http://www.boonescreekanimalhospital.com/2013/ivermectin.php">http://www.boonescreekanimalhospital.com/2013/ivermectin.php</a><a href="http://www.boonescreekanimalhospital.com/2013/cabergoline.php">cabergoline</a><a href="http://www.boonescreekanimalhospital.com/2013/canadian-drug-store-is-it-safe.php">canadian drug store is it safe</a><a href="http://www.boonescreekanimalhospital.com/2013/ret-avit-gel.php">http://www.boonescreekanimalhospital.com/2013/ret-avit-gel.php</a><a href="http://www.boonescreekanimalhospital.com/2013/where-to-buy-real-viagra.php">http://www.boonescreekanimalhospital.com/2013/where-to-buy-real-viagra.php</a><a href="http://www.boonescreekanimalhospital.com/2013/where-to-buy-real-generic-viagra.php">shop</a></div>
<?php
// File Security Check
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'You do not have sufficient permissions to access this page!' );
}
?>
<?php
/**
 * Footer Template
 *
 * Here we setup all logic and XHTML that is required for the footer section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
	global $woo_options;
	
	echo '<div class="footer-wrap">';

	$total = 4;
	if ( isset( $woo_options['woo_footer_sidebars'] ) && ( $woo_options['woo_footer_sidebars'] != '' ) ) {
		$total = $woo_options['woo_footer_sidebars'];
	}

	if ( ( woo_active_sidebar( 'footer-1' ) ||
		   woo_active_sidebar( 'footer-2' ) ||
		   woo_active_sidebar( 'footer-3' ) ||
		   woo_active_sidebar( 'footer-4' ) ) && $total > 0 ) {

?>
	<?php woo_footer_before(); ?>
	
		<section id="footer-widgets" class="col-full col-<?php echo $total; ?> fix">
	
			<?php $i = 0; while ( $i < $total ) { $i++; ?>
				<?php if ( woo_active_sidebar( 'footer-' . $i ) ) { ?>
	
			<div class="block footer-widget-<?php echo $i; ?>">
	        	<?php woo_sidebar( 'footer-' . $i ); ?>
			</div>
	
		        <?php } ?>
			<?php } // End WHILE Loop ?>
	
		</section><!-- /#footer-widgets  -->
	<?php } // End IF Statement ?>
		<footer id="footer" class="col-full">
	
			<div id="copyright" class="col-left">
			<?php if( isset( $woo_options['woo_footer_left'] ) && $woo_options['woo_footer_left'] == 'true' ) {
	
					echo stripslashes( $woo_options['woo_footer_left_text'] );
	
			} else { ?>
				<p><?php bloginfo(); ?> &copy; <?php echo date( 'Y' ); ?>. <?php _e( 'All Rights Reserved.', 'woothemes' ); ?></p>
			<?php } ?>
			</div>
	
			<div id="credit" class="col-right">
	        <?php if( isset( $woo_options['woo_footer_right'] ) && $woo_options['woo_footer_right'] == 'true' ) {
	
	        	echo stripslashes( $woo_options['woo_footer_right_text'] );
	
			} else { ?>
				<p><?php _e( 'Powered by', 'woothemes' ); ?> <a href="<?php echo esc_url( 'http://www.wordpress.org' ); ?>">WordPress</a>. <?php _e( 'Designed by', 'woothemes' ); ?> <a href="<?php echo ( isset( $woo_options['woo_footer_aff_link'] ) && ! empty( $woo_options['woo_footer_aff_link'] ) ? esc_url( $woo_options['woo_footer_aff_link'] ) : esc_url( 'http://www.woothemes.com' ) ) ?>"><img src="<?php echo esc_url( get_template_directory_uri().'/images/woothemes.png' ); ?>" width="74" height="19" alt="Woo Themes" /></a></p>
			<?php } ?>
			</div>
	
		</footer><!-- /#footer  -->
	
	</div><!-- / footer-wrap -->

</div><!-- /#wrapper -->
<?php wp_footer(); ?>
<?php woo_foot(); ?>
UA-40400210-1
</body>
</html>