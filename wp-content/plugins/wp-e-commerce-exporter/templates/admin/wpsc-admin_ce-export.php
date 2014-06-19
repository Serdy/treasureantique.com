<script type="text/javascript">
	function showProgress() {
		window.scrollTo(0,0);
		document.getElementById('progress').style.display = 'block';
		document.getElementById('content').style.display = 'none';
		document.getElementById('support-donate_rate').style.display = 'none';
	}
</script>

<div id="content">

	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php wpsc_ce_admin_active_tab( 'overview' ); ?>" href="<?php echo $wpsc_ce_url; ?>"><?php _e( 'Overview', 'wpsc_ce' ); ?></a>
		<a data-tab-id="export" class="nav-tab<?php wpsc_ce_admin_active_tab( 'export' ); ?>" href="<?php echo add_query_arg( array( 'tab' => 'export' ), $wpsc_ce_url ); ?>"><?php _e( 'Export', 'wpsc_ce' ); ?></a>
		<a data-tab-id="archive" class="nav-tab<?php wpsc_ce_admin_active_tab( 'archive' ); ?>" href="<?php echo add_query_arg( array( 'tab' => 'archive' ), $wpsc_ce_url ); ?>"><?php _e( 'Archives', 'wpsc_ce' ); ?></a>
		<a data-tab-id="tools" class="nav-tab<?php wpsc_ce_admin_active_tab( 'tools' ); ?>" href="<?php echo add_query_arg( array( 'tab' => 'tools' ), $wpsc_ce_url ); ?>"><?php _e( 'Tools', 'wpsc_ce' ); ?></a>
	</h2>
	<?php wpsc_ce_tab_template( $tab ); ?>

</div>
<!-- #content -->

<div id="progress" style="display:none;">
	<p><?php _e( 'Chosen WP e-Commerce details are being exported, this process can take awhile. Time for a beer?', 'wpsc_ce' ); ?></p>
	<img src="<?php echo plugins_url( '/templates/admin/images/progress.gif', WPSC_CE_RELPATH ); ?>" alt="" />
	<p><?php _e( 'When the download is complete, return to <a href="' . $url . '">WP e-Commerce Exporter</a>.', 'wpsc_ce' ); ?>
</div>
<!-- #progress -->