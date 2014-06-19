<div class="overview-left">

	<h3><a href="<?php echo add_query_arg( 'tab', 'export' ); ?>"><?php _e( 'Export', 'wpsc_ce' ); ?></a></h3>
	<p><?php _e( 'Export store details out of WP e-Commerce into a CSV-formatted file.', 'wpsc_ce' ); ?></p>
	<ul class="ul-disc">
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-products"><?php _e( 'Export Products', 'wpsc_ce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-categories"><?php _e( 'Export Categories', 'wpsc_ce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-tags"><?php _e( 'Export Tags', 'wpsc_ce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-orders"><?php _e( 'Export Orders', 'wpsc_ce' ); ?></a>
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
			<span class="description">(<?php echo sprintf( __( 'available in %s', 'wpsc_ce' ), $wpsc_cd_link ); ?>)</span>
<?php } ?>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-customers"><?php _e( 'Export Customers', 'wpsc_ce' ); ?></a>
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
			<span class="description">(<?php echo sprintf( __( 'available in %s', 'wpsc_ce' ), $wpsc_cd_link ); ?>)</span>
<?php } ?>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-coupons"><?php _e( 'Export Coupons', 'wpsc_ce' ); ?></a>
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
			<span class="description">(<?php echo sprintf( __( 'available in %s', 'wpsc_ce' ), $wpsc_cd_link ); ?>)</span>
<?php } ?>
		</li>
	</ul>

	<h3><a href="<?php echo add_query_arg( 'tab', 'archive' ); ?>"><?php _e( 'Archives', 'wpsc_ce' ); ?></a></h3>
	<p><?php _e( 'Download copies of prior store exports.', 'wpsc_ce' ); ?></p>

	<h3><a href="<?php echo add_query_arg( 'tab', 'tools' ); ?>"><?php _e( 'Tools', 'wpsc_ce' ); ?></a></h3>
	<p><?php _e( 'Export tools for WP e-Commerce.', 'wpsc_ce' ); ?></p>
</div>
<!-- .overview-left -->
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
<div class="welcome-panel overview-right">
	<h3>
		<!-- <span><a href="#"><attr title="<?php _e( 'Dismiss this message', 'wpsc_ce' ); ?>"><?php _e( 'Dismiss', 'wpsc_ce' ); ?></attr></a></span> -->
		<?php _e( 'Upgrade to Pro', 'wpsc_ce' ); ?>
	</h3>
	<p class="clear"><?php _e( 'Upgrade to Store Exporter Deluxe to unlock business focused e-commerce features within Store Exporter, including:', 'wpsc_ce' ); ?></p>
	<ul class="ul-disc">
		<li><?php _e( 'Select export date ranges', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Export Orders', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Select Order fields to export', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Export Customers', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Select Customer fields to export', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Export Coupons', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Select Coupon fields to export', 'wpsc_ce' ); ?></li>
		<li><?php _e( 'Premium Support', 'wpsc_ce' ); ?></li>
	</ul>
	<p>
		<a href="<?php echo $wpsc_cd_url; ?>" target="_blank" class="button"><?php _e( 'More Features', 'wpsc_ce' ); ?></a>&nbsp;
		<a href="<?php echo $wpsc_cd_url; ?>" target="_blank" class="button button-primary"><?php _e( 'Buy Now', 'wpsc_ce' ); ?></a>
	</p>
</div>
<?php } ?>
<!-- .overview-right -->