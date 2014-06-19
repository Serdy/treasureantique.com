<?php

// security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	die( 'There is nothing to see here.' );
}


// Small info on DashBoard-page
function counterize_dashboard_callback()
{
	//what was the two following lines supposed to do?...
	//$admin = dirname( $_SERVER['SCRIPT_FILENAME'] );
	//$admin = substr( $admin, strrpos( $admin, '/' ) + 1 );
	$count = counterize_getamount();
	$unique = counterize_getuniqueamount();
	$todaycount = counterize_gethitstoday();
	$online = counterize_get_online_users();
	$todayunique = counterize_getuniquehitstoday();

	?>

		<p>
			<?php _e( 'Total: ', COUNTERIZE_TD ); ?><strong><?php echo $count; ?></strong> <?php _e( 'hits and', COUNTERIZE_TD )?> <strong><?php echo $unique; ?></strong> <?php _e( 'unique.' ); ?>
		</p>
		<p>
			<?php _e( 'Today: ', COUNTERIZE_TD ); ?><strong><?php echo $todaycount; ?></strong> <?php _e( 'hits and', COUNTERIZE_TD ); ?> <strong><?php echo $todayunique; ?></strong> <?php _e( 'unique.' ); ?>
		</p>
		<p>
			<?php _e( 'Currently: ', COUNTERIZE_TD ); ?><strong><?php echo $online; ?></strong> <?php _e( 'users online.', COUNTERIZE_TD ); ?>
		</p>

		<a href="admin.php?page=counterize_dashboard"><?php _e( 'Detailed view', COUNTERIZE_TD ); ?> &raquo;</a>
	<?php
}

//Content of the Counterize dashboard page
function counterize_display_dashboard_page_callback()
{
	echo '
	<div id="icon-plugins" class="icon32"></div>
	<h1>' . __( 'Counterize', COUNTERIZE_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_TD ) . '</h1>
	';

	//Show charts
	counterize_showStats( true );

	if( current_user_can( 'manage_options' ) )
	{

		$updateText = '';
		if( isset( $_GET['killmass'] ) && check_admin_referer( 'action_killmass' ) )
		{
			if( $_GET['killmass']=='yes' )
			{
				if( isset( $_POST['counterize_killemall'] ) )
				{
					foreach( $_POST['counterize_killemall'] as $key => $val )
					{
						$val = intval( $val );
						counterize_killEntry( $val );
						$updateText .= __( 'Entry: ' . $val . ' removed<br />', COUNTERIZE_TD );
					}
					counterize_updateText( $updateText );
					unset( $updateText );
				}
			}
		}

		// For the zap-an-entry-option
		if( isset( $_GET['kill'] ) && check_admin_referer( 'action_uri_kill' ) )
		{
			$val = intval( $_GET['kill'] );
			counterize_killEntry( $val );
			counterize_updateText( __( 'Deleting entry ', COUNTERIZE_TD ) . $val );
		}

		//Show latest entries
		counterize_show_history( COUNTERIZE_MENU_SLUG, false );
	}

	//Print the footer
	counterize_pagefooter();
}




function counterize_display_dashboard_history_page_callback()
{
	// Amount to pass as option to the graphs...
	$amount2 = counterize_get_option( 'amount2' );
	if( $amount2 == '' || $amount2 == ' ' || ! is_numeric( $amount2 ) )
	{
		$amount2 = 10;
	}

	echo '
	<div id="icon-plugins" class="icon32"></div>
	<h1>' . __( 'Counterize', COUNTERIZE_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_TD ) . ' - ' . __( 'History', COUNTERIZE_TD ) . '</h1>
	';

	if( current_user_can( 'manage_options' ) )
	{

		$updateText = '';
		if( isset( $_GET['killmass'] ) && check_admin_referer( 'action_killmass' ) )
		{
			if( $_GET['killmass']=='yes' )
			{
				if( isset( $_POST['counterize_killemall'] ) )
				{
					foreach( $_POST['counterize_killemall'] as $key => $val )
					{
						$val = intval( $val );
						counterize_killEntry( $val );
						$updateText .= __( 'Entry: ' . $val . ' removed<br />', COUNTERIZE_TD );
					}
					counterize_updateText( $updateText );
					unset( $updateText );
				}
			}
		}

		// For the zap-an-entry-option
		if( isset( $_GET['kill'] ) && check_admin_referer( 'action_uri_kill' ) )
		{
			$val = intval( $_GET['kill'] );
			counterize_killEntry( $val );
			counterize_updateText( __( 'Deleting entry ', COUNTERIZE_TD ) . $val );
		}

		//Show latest entries
		counterize_show_history( 'counterize_dashboard_history',  true );
	}

	//Print the footer
	counterize_pagefooter();
}

?>