<?php

// security check
if( !defined( 'WP_PLUGIN_DIR' ) || ! current_user_can( 'manage_options' ) )
{
	die( 'There is nothing to see here...' );
}


//Content of the Counterize options page
function counterize_options_page_callback()
{
	global $wpdb;

	//Not admin? Go away.
	if( ! current_user_can( 'manage_options' ) )
	{
		die ( __( "You don't have sufficient privileges to display this page", COUNTERIZE_TD ) );
	}

	$MajorVersion	= counterize_get_version( 'major' );
	$MinorVersion	= counterize_get_version( 'minor' );
	$Revision		= counterize_get_version( 'revision' );

	?>

	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>

		<h2><?php echo __( 'Counterize configuration - Version ', COUNTERIZE_TD ) . $MajorVersion . '.' . $MinorVersion . '.' . $Revision; ?></h2>

		<form method="post" action="options.php">

			<?php
			settings_fields( 'counterize_options_group' );
			do_settings_sections( 'counterize_options_page_id' );
			?>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', COUNTERIZE_TD ) ?>" />
			</p>
		</form>

	</div>

	<?php

	counterize_pagefooter();
}

//Executed when WordPress initialize the administration section.
//Inside we register our javascript ans stylesheet files.
function counterize_admin_init_callback()
{
	wp_register_script( 'counterize_javascript', COUNTERIZE_PLUGIN_URL . '/counterize.js.php', __FILE__ );
	wp_register_style( 'counterize_stylesheet', COUNTERIZE_PLUGIN_URL . '/counterize.css.php', __FILE__ );


	//register option group
	register_setting( 'counterize_options_group', 'counterize_options', 'counterize_options_validate_callback' );

	//add sections
	add_settings_section( 'counterize_options_section_appearance', __( 'Appearance'         , COUNTERIZE_TD ), 'counterize_options_display_section_appearance_callback', 'counterize_options_page_id' );
	add_settings_section( 'counterize_options_section_exclusions', __( 'Exclusions'         , COUNTERIZE_TD ), 'counterize_options_display_section_exclusions_callback', 'counterize_options_page_id' );
	add_settings_section( 'counterize_options_section_ip'        , __( 'IP-related'         , COUNTERIZE_TD ), 'counterize_options_display_section_ip_callback'        , 'counterize_options_page_id' );
	add_settings_section( 'counterize_options_section_db'        , __( 'Database operations', COUNTERIZE_TD ), 'counterize_options_display_section_db_callback'        , 'counterize_options_page_id' );
	add_settings_section( 'counterize_options_section_email'     , __( 'Email reports'      , COUNTERIZE_TD ), 'counterize_options_display_section_email_callback'     , 'counterize_options_page_id' );

	//section Appearance
	add_settings_field( 'counterize_setting_amount'  , __( 'Rows to show in latest entries', COUNTERIZE_TD ) , 'counterize_options_display_amount_callback'  , 'counterize_options_page_id', 'counterize_options_section_appearance' );
	add_settings_field( 'counterize_setting_amount2' , __( 'Rows to show in vertical charts', COUNTERIZE_TD ), 'counterize_options_display_amount2_callback' , 'counterize_options_page_id', 'counterize_options_section_appearance' );
	add_settings_field( 'counterize_setting_maxwidth', __( 'Maximum width for labels', COUNTERIZE_TD )       , 'counterize_options_display_maxwidth_callback', 'counterize_options_page_id', 'counterize_options_section_appearance' );
	add_settings_field( 'counterize_setting_display_dashboard_capability', __( 'Capability needed to display the Counterize dashboard', COUNTERIZE_TD )      , 'counterize_options_display_display_dashboard_capability_callback', 'counterize_options_page_id', 'counterize_options_section_appearance' );

	//section Exclusions
	add_settings_field( 'counterize_setting_excludedusers', __( 'Select users that may not be counted &#x202A;(press Ctrl for multi-selection)&#x202C;', COUNTERIZE_TD ), 'counterize_options_display_excludedusers_callback', 'counterize_options_page_id', 'counterize_options_section_exclusions' );
	add_settings_field( 'counterize_setting_excludedip'   , __( 'Comma-separated list of IP to exclude from the statistics', COUNTERIZE_TD )                            , 'counterize_options_display_excludedip_callback'   , 'counterize_options_page_id', 'counterize_options_section_exclusions' );
	add_settings_field( 'counterize_setting_logbots'      , __( 'Enable <a href="http://en.wikipedia.org/wiki/Internet_bot">bots</a> logging', COUNTERIZE_TD )          , 'counterize_options_display_logbots_callback'      , 'counterize_options_page_id', 'counterize_options_section_exclusions' );


	//section IP
	add_settings_field( 'counterize_setting_enableip', __( 'My country allows to collect IPs', COUNTERIZE_TD ), 'counterize_options_display_enableip_callback', 'counterize_options_page_id', 'counterize_options_section_ip' );
	add_settings_field( 'counterize_setting_hostname', __( 'Enable hostname lookup in IP stats (<strong>can make your website very slow!!!</strong>)', COUNTERIZE_TD ), 'counterize_options_display_hostname_callback', 'counterize_options_page_id', 'counterize_options_section_ip' );
	add_settings_field( 'counterize_setting_whois'   , __( 'Whois URL string'                , COUNTERIZE_TD ), 'counterize_options_display_whois_callback'   , 'counterize_options_page_id', 'counterize_options_section_ip' );
	add_settings_field( 'counterize_setting_geoip'   , __( 'Geo IP tool URL string'          , COUNTERIZE_TD ), 'counterize_options_display_geoip_callback'   , 'counterize_options_page_id', 'counterize_options_section_ip' );

	//section DB
	add_settings_field( 'counterize_setting_flushdb'   , __( 'Flush the Counterize Database', COUNTERIZE_TD ), 'counterize_options_display_flushdb_callback'   , 'counterize_options_page_id', 'counterize_options_section_db' );
	add_settings_field( 'counterize_setting_refreshua' , __( 'Refresh the user-agent table' , COUNTERIZE_TD ), 'counterize_options_display_refreshua_callback' , 'counterize_options_page_id', 'counterize_options_section_db' );
	add_settings_field( 'counterize_setting_deletebots', __( 'Manually delete the bots'     , COUNTERIZE_TD ), 'counterize_options_display_deletebots_callback', 'counterize_options_page_id', 'counterize_options_section_db' );

	//section Email reports
	add_settings_field( 'counterize_setting_enableemailreports' , __( 'Enable email reports'                                                                               , COUNTERIZE_TD ), 'counterize_options_display_enableemailreports_callback'       , 'counterize_options_page_id', 'counterize_options_section_email' );
	add_settings_field( 'counterize_setting_recipientlist'      , __( 'Recipient list &#x202A;(comma-separated list of email addresses)&#x202C;'                           , COUNTERIZE_TD ), 'counterize_options_display_recipientlist_callback'            , 'counterize_options_page_id', 'counterize_options_section_email' );
	add_settings_field( 'counterize_setting_reportperiod'       , __( 'Send a report'                                                                                      , COUNTERIZE_TD ), 'counterize_options_display_reportperiod_callback'             , 'counterize_options_page_id', 'counterize_options_section_email' );
	add_settings_field( 'counterize_setting_customperiod'       , __( 'Set a custom period &#x202A;(in seconds)&#x202C;'                                                   , COUNTERIZE_TD ), 'counterize_options_display_customperiod_callback'             , 'counterize_options_page_id', 'counterize_options_section_email' );
	add_settings_field( 'counterize_setting_mailsubjectoverride', __( 'Mail subject override &#x202A;(leave empty for default)&#x202C;'                                    , COUNTERIZE_TD ), 'counterize_options_display_mailsubjectoverride_callback'      , 'counterize_options_page_id', 'counterize_options_section_email' );
	add_settings_field( 'counterize_setting_reportwhat'         , __( 'Select the items you want to include in the report &#x202A;(press CTRL for multi-selection)&#x202C;', COUNTERIZE_TD ), 'counterize_options_display_reportwhat_callback'               , 'counterize_options_page_id', 'counterize_options_section_email' );
	add_settings_field( 'counterize_setting_sendreportnow'      , __( 'Send report now'                                                                                    , COUNTERIZE_TD ), 'counterize_options_display_sendreportnow_callback'            , 'counterize_options_page_id', 'counterize_options_section_email' );
}

//display the Appearance section
function counterize_options_display_section_appearance_callback()
{
	echo '<p>' . __( 'This section concerns the look and feel of Counterize.', COUNTERIZE_TD ) . '</p>';
}

//display the Exclusions section
function counterize_options_display_section_exclusions_callback()
{
	echo '<p>' . __( 'This section allows you to set some exclusions.', COUNTERIZE_TD ) . '</p>';
}

//display the IP-related section
function counterize_options_display_section_ip_callback()
{
	echo '<p>' . __( 'This section is about IP-related options.', COUNTERIZE_TD ) . '</p>';
}

//display the Database operations section
function counterize_options_display_section_db_callback()
{
	echo '<p>' . __( 'Database maintenance operations are in this section.', COUNTERIZE_TD ) . '</p>';
}

//display the Email reports section
function counterize_options_display_section_email_callback()
{
	$options = get_option( 'counterize_options' );
	echo '<p>' . __( 'Configure email reports in this section.', COUNTERIZE_TD ) . '</p>';
	if( $options['enableemailreports'] > 0 && $options['reportperiod'] != 'never' )
	{
		echo '<p>' . sprintf( __( 'Next report will be sent on <strong>%s</strong>.', COUNTERIZE_TD ), strftime( '%c', $options['next_report_timestamp'] ) ) . '</p>';
	}
	else
	{
		echo '<p>' . __( 'Email reports are currently <strong>disabled</strong>.', COUNTERIZE_TD ) . '</p>';
	}
}

function counterize_options_display_amount_callback()
{
	$options = get_option( 'counterize_options' );

	//set a default value if no value is set
	if( ! isset( $options['amount'] ) || empty( $options['amount'] ) )
	{
		$options['amount'] = 50;
	}
	echo "<input id='counterize_setting_amount' name='counterize_options[amount]' size='10' type='number' min='0' max='999999999' value='{$options['amount']}' />";
	echo '<br /><small>&#x202A;<strong>0</strong>' . __( ' to view all (not recommended because this exposes you to get an "out of memory" error)', COUNTERIZE_TD ) . '&#x202C;</small>';
}
function counterize_options_display_amount2_callback()
{
	$options = get_option( 'counterize_options' );
	//set a default value if no value is set
	if(! isset( $options['amount2'] ) || empty( $options['amount2'] ) )
	{
		$options['amount2'] = 50;
	}
	echo "<input id='counterize_setting_amount2' name='counterize_options[amount2]' size='10' type='number' min='1' max='999999999' value='{$options['amount2']}' />";
}
function counterize_options_display_maxwidth_callback()
{
	$options = get_option( 'counterize_options' );
	//set a default value if no value is set
	if( ! isset( $options['maxwidth'] ) || empty( $options['maxwidth'] ) )
	{
		$options['maxwidth'] = 50;
	}
	echo "<input id='counterize_setting_maxwidth' name='counterize_options[maxwidth]' size='10' type='number' min='1' max='999999999' value='{$options['maxwidth']}' />";
}

function counterize_options_display_display_dashboard_capability_callback()
{
	global $wp_roles;
	$options = get_option( 'counterize_options' );
	//set a default value if no value is set
	if( ! isset( $options['display_dashboard_capability'] ) || empty( $options['display_dashboard_capability'] ) )
	{
		$options['display_dashboard_capability'] = 'manage_options';
	}
	?>

	<select id='counterize_setting_display_dashboard_capability' name='counterize_options[display_dashboard_capability]'>
		<optgroup label="<?php echo __( 'Roles', COUNTERIZE_TD ); ?>">

			<?php
			$already_selected = false;

			/*
			 * Retrieve all the roles and add them on top of the dropdown listitem.
			 */

			//get the instance of WP_Roles
			global $wp_roles;

			// get a list of values, containing pairs of: $role_name => $display_name
			$roles = $wp_roles->get_names();

			//reverse the array so that roles with less capabilities come first
			$reversed_roles = array_reverse( $roles );

			//declare an array where we will store the capabilities specific to each role
			$caps_per_role = array();

			//for each role
			foreach( $reversed_roles as $role_name=>$display_name )
			{
				//get an instance of the WP_Role class using the role name
				$role = $wp_roles->get_role( $role_name );

				//if the $capabilities array is set, then its not the first iteration
				if( isset( $capabilities ) )
				{
					//get this role capabilities
					$this_role_capabilities = $role->capabilities;

					//for each capability
					foreach( $this_role_capabilities as $cap => $hasit )
					{
						//if the key doesn't exist, then we found a capability specific to $role, stored in $cap
						if( ! array_key_exists( $cap, $capabilities ) )
						{
							//we store the capability in the $caps_per_role array under the $role_name key
							$caps_per_role[ $role_name ][$cap] = $cap;
						}
					}

					//we sort the capabilities of this role by key
					ksort( $caps_per_role[ $role_name ] );

					//place the pointer to the beginning of the array
					reset( $caps_per_role[ $role_name ] );

					//set the first capability variable
					$first_capability = key( $caps_per_role[ $role_name ] );

					//update the $capabilities array for the next iteration
					$capabilities = $this_role_capabilities;
				}
				else
				{
					//first iteration, we set the $capabilities array
					$capabilities = $role->capabilities;

					//sort the capabilities by key
					ksort( $capabilities );

					//we store the capabilities in the $caps_per_role array under the $role_name key
					$caps_per_role[ $role_name ] = array_keys( $capabilities );

					//place the pointer to the beginning of the array
					reset( $capabilities );

					// then we take the first capability we find in this array
					$first_capability = key( $capabilities );
				}

				//whether the item should be selected or not
				$selected = $options['display_dashboard_capability'] === $first_capability;

				//if selected, then we set $already_selected to TRUE, so that later we do not mark
				// a capability as selected if the capability name is the same
				if( $selected )
				{
					$already_selected = TRUE;
				}

				//output the option item
				echo "
				<option value='{$first_capability}'" . ( $selected ? ' selected="selected"' : '' ) . ">{$display_name}</option>
				";
			}
			?>

		</optgroup>

		<?php
		foreach( $roles as $role_name => $display_name )
		{
			?>

			<optgroup label="<?php printf( __( '%s capabilities' , COUNTERIZE_TD ), $display_name ); ?>">

				<?php
				$already_selected2 = $already_selected;
				foreach( $caps_per_role[ $role_name ] as $cap )
				{
					//whether the item should be selected or not
					$selected = $options['display_dashboard_capability'] === $cap;

					if( $selected )
					{
						$already_selected2 = TRUE;
					}

					//output the option item
					echo "
					<option value='{$cap}'" . ( $selected && ! $already_selected ? ' selected="selected"' : '' ) . ">{$cap}</option>
					";
				}
				?>

			</optgroup>

			<?php
		}
		?>

		<optgroup label="<?php echo __( 'Current user capabilities', COUNTERIZE_TD ); ?>">

			<?php
			/*
			 * retrieve all the capabilities of the current user (usually an administrator) for finer access control
			 */

			 //get current user
			$current_user = wp_get_current_user();

			//set the user allcaps property
			$current_user->get_role_caps();

			//get the current user capabilities
			$capabilities = $current_user->allcaps;

			//sort the array by key
			ksort( $capabilities );

			//for each capability
			foreach( $capabilities as $cap => $hasit )
			{
				//output the option item
				echo "
				<option value='{$cap}'" . ( $options['display_dashboard_capability'] === $cap && ! $already_selected && ! $already_selected2 ? ' selected="selected"' : '' ) . ">{$cap}</option>
				";
			}
			?>

		</optgroup>
	</select>

	<?php
}

function counterize_options_display_excludedusers_callback()
{
	global $wpdb;
	$options = get_option( 'counterize_options' );
	$excluded_users	= explode( ',', counterize_get_option( 'excludedusers' ) );

	$sql = "SELECT * FROM {$wpdb->users}";
	$users = $wpdb->get_results( $sql );

	?>

	<select id='counterize_setting_excludedusers' name='counterize_options[excludedusers][]' multiple='multiple' size='10' style='height: auto;'>

	<?php
	foreach( $users as $user )
	{
		?>

		<option value="<?php echo $user->ID; ?>" <?php if( in_array( $user->ID, $excluded_users ) ) { echo 'selected="selected"'; } ?>><?php echo $user->display_name . '&nbsp;&nbsp;&nbsp;[' . $user->user_login . ']'; ?></option>

		<?php
	}
	unset( $users );
	?>

	</select>

	<?php
}

function counterize_options_display_excludedip_callback()
{
	$options = get_option( 'counterize_options' );
	echo "<textarea id='counterize_setting_excludedip' name='counterize_options[excludedip]' cols='50' rows='3'>{$options['excludedip']}</textarea>";
}

function counterize_options_display_logbots_callback()
{
	$options = get_option( 'counterize_options' );
	echo "<input id='counterize_setting_logbots' name='counterize_options[logbots]' type='checkbox' value='1'" . ( $options['logbots'] > 0 ? ' checked="checked"' : '' ) . ' />';
}

function counterize_options_display_enableip_callback()
{
	$options = get_option( 'counterize_options' );
	echo "<input id='counterize_setting_enableip' name='counterize_options[enableip]' type='checkbox' value='1'" . ( $options['enableip'] > 0 ? ' checked="checked"' : '' ) . ' />';
}

function counterize_options_display_hostname_callback()
{
	$options = get_option( 'counterize_options' );
	echo "<input id='counterize_setting_hostname' name='counterize_options[enable_hostname_lookup]' type='checkbox' value='1'" . ( $options['enable_hostname_lookup'] > 0 ? ' checked="checked"' : '' ) . ' />';
}

function counterize_options_display_whois_callback()
{
	$options = get_option( 'counterize_options' );
	echo "<input id='counterize_setting_whois' name='counterize_options[whois]' size='60' type='text' value='{$options['whois']}' />";
	echo '<br /><small>' . __( 'Example:', COUNTERIZE_TD ) . ' &#x202A;http://www.ripe.net/whois?form_type=simple&amp;searchtext=&#x202C;</small>';
}

function counterize_options_display_geoip_callback()
{
	$options = get_option( 'counterize_options' );
	echo "<input id='counterize_setting_geoip' name='counterize_options[geoip]' size='60' type='text' value='{$options['geoip']}' />";
	echo '<br /><small>' . __( 'Example:', COUNTERIZE_TD ) . ' &#x202A;http://whatismyipaddress.com/ip/&#x202C;</small>';
}

/* these options don't have any value stored in the database */
function counterize_options_display_flushdb_callback()
{
	$options = get_option( 'counterize_options' );
	?>
	<input id='counterize_setting_flushdb' name='counterize_options[flushdb]' type='checkbox' value='wantflushdb' /><br />
	<small>
		<strong>
			<?php _e( "&#x202A;(This means deleting all records and all stats! Don't forget to backup your database before if your data is important)&#x202C;", COUNTERIZE_TD ); ?>
		</strong>
	</small>
	<?php
	if( isset( $_SESSION['counterize_flusheddb'] ) )
	{
		if( $_SESSION['counterize_flusheddb']== 'flushed' )
		{
			counterize_updateText( __( 'Database flushed!', COUNTERIZE_TD ) );
			unset( $_SESSION['counterize_flusheddb'] );
		}
	}
}

function counterize_options_display_refreshua_callback()
{
	$options = get_option( 'counterize_options' );
	?>
	<input id='counterize_setting_refreshua' name='counterize_options[refreshua]' type='checkbox' value='wantrefreshua' /><br />
	<small>
		<?php _e( '&#x202A;(You may do this if you just upgraded from a previous version)&#x202C;', COUNTERIZE_TD ); ?>
	</small>
	<?php
	if( isset( $_SESSION['counterize_refreshedua'] ) )
	{
		if( $_SESSION['counterize_refreshedua'] == 'refreshed' )
		{
			counterize_updateText( __( 'User-agent table refreshed!', COUNTERIZE_TD ) );
			unset( $_SESSION['counterize_refreshedua'] );
		}
	}
}

function counterize_options_display_deletebots_callback()
{
	$options = get_option( 'counterize_options' );
	?>
	<input id='counterize_setting_deletebots' name='counterize_options[deletebots]' type='checkbox' value='wantdeletebots' /><br />
	<small>
		<?php _e( '&#x202A;(You should do this if you modified <strong>botlist.txt</strong>)&#x202C;', COUNTERIZE_TD ); ?>
	</small>
	<small>
		<strong>
			<?php _e( 'This could make your website really slow or even unresponsive! Do not delete bots if your database contains million of records and you are on a shared hosting service.', COUNTERIZE_TD ); ?>
		</strong>
	</small>
	<?php
	if( isset( $_SESSION['counterize_deletedbots'] ) )
	{
		if( $_SESSION['counterize_deletedbots'] == 'deleted' )
		{
			counterize_updateText( __( 'Bots have been deleted!', COUNTERIZE_TD ) );
			unset( $_SESSION['counterize_deletedbots'] );
		}
	}
}

function counterize_options_display_enableemailreports_callback()
{
	$options = get_option( 'counterize_options' );

	//set a default value if no value is set
	if( ! isset( $options['enableemailreports'] ) )
	{
		$options['enableemailreports'] = 0;
	}

	echo "<input id='counterize_setting_enableemailreports' name='counterize_options[enableemailreports]' type='checkbox' value='1'" . ( $options['enableemailreports'] > 0 ? ' checked="checked"' : '' ) . ' />';
}

function counterize_options_display_recipientlist_callback()
{
	$options = get_option( 'counterize_options' );

	//set a default value if no value is set
	if( empty( $options['recipientlist'] ) )
	{
		$options['recipientlist'] = get_bloginfo( 'admin_email' );
	}

	echo "<textarea id='counterize_setting_recipientlist' name='counterize_options[recipientlist]' cols='50' rows='3'>{$options['recipientlist']}</textarea>";
}

function counterize_options_display_reportperiod_callback()
{
	$options = get_option( 'counterize_options' );

	//set a default value if no value is set
	if( ! isset( $options['reportperiod'] ) )
	{
		$options['reportperiod'] = 'never';
	}

	$i = 0;
	$choices = array
	(
		'never'   => 'Never',
		'daily'   => 'Daily',
		'weekly'  => 'Weekly',
		'15days'  => 'Every 15 days',
		'monthly' => 'Monthly',
		'3months' => 'Every 3 months',
		'custom'  => 'Custom'
	);
	foreach( $choices as $value => $label )
	{
		$checked = '';
		if ( $options['reportperiod'] == $value )
		{
			$checked = ' checked="checked"';
		}
		echo "
		<input type='radio' name='counterize_options[reportperiod]' id='reportperiod{$i}' value='{$value}'{$checked} />
		<label for='reportperiod{$i}'>{$label}</label>
		<br />
		";
		$i++;
	}
}

function counterize_options_display_customperiod_callback()
{
	$options = get_option( 'counterize_options' );
	//set a default value if no value is set
	if( ! isset( $options['customperiod'] ) )
	{
		$options['customperiod'] = 0;
	}
	echo "<input id='counterize_setting_customperiod' name='counterize_options[customperiod]' min='0' max='999999999' type='number' value='{$options['customperiod']}' />";
}

function counterize_options_display_mailsubjectoverride_callback()
{
	$options = get_option( 'counterize_options' );
	//set a default value if no value is set
	if( empty( $options['mailsubjectoverride'] ) )
	{
		$options['mailsubjectoverride'] = '';
	}
	echo "<input id='counterize_setting_mailsubjectoverride' name='counterize_options[mailsubjectoverride]' size='50' type='text' value='{$options['mailsubjectoverride']}' />";
}

function counterize_options_display_reportwhat_callback()
{
	global $wpdb;
	$options = get_option( 'counterize_options' );
	$report_what = explode( ',', counterize_get_option( 'reportwhat' ) );

	$stats = array
	(
		'all' => __( 'Complete report', COUNTERIZE_TD )
	);

	if( empty( $report_what ) || count( $report_what ) == 0 || empty( $report_what[0] ) )
	{
		$report_what = array( 'all' );
	}

	$stats = apply_filters( 'counterize_report_what_filter', $stats );

	?>

	<select id='counterize_setting_reportwhat' name='counterize_options[reportwhat][]' multiple='multiple' size='10' style='height: auto;'>

	<?php
	foreach( $stats as $value => $name )
	{
		?>

		<option value="<?php echo $value; ?>" <?php if( in_array( $value, $report_what ) ) { echo 'selected="selected"'; } ?>><?php echo $name; ?></option>

		<?php
	}
	unset( $stats );
	?>

	</select>

	<?php
}

function counterize_options_display_sendreportnow_callback()
{
	echo "<input id='counterize_setting_sendreportnow' name='counterize_options[sendreportnow]' type='checkbox' value='wantsendreportnow' />";

	if( isset( $_SESSION['counterize_report_status'] ) )
	{
		if( $_SESSION['counterize_report_status'] == 'sent' )
		{
			counterize_updateText( __( 'Report sent!', COUNTERIZE_TD ) );
			unset( $_SESSION['counterize_report_status'] );
		}
		elseif( $_SESSION['counterize_report_status'] == 'error' )
		{
			counterize_updateText( __( 'Error. The report could not be sent! Possible causes:
			<ol>
				<li>Create the email address wordpress@yoursite.com</li>
				<li>Settings SMTP and smtp_port need to be set in your php.ini</li>
				<li>Either set the sendmail_from setting in php.ini</li>
			</ol>', COUNTERIZE_TD ) );
			unset( $_SESSION['counterize_report_status'] );
		}
	}
}

//validate the input data
function counterize_options_validate_callback( $input )
{
	//load the current options
	$newinput = get_option( 'counterize_options' );

	if( isset( $input['amount'] ) )
	{
		$newinput['amount'] = intval( trim( $input['amount'] ) );
		if( ! preg_match( '/^[0-9]+$/i', $input['amount'] ) )
		{
			$newinput['amount'] = 50;
		}
	}

	if( isset( $input['amount2'] ) )
	{
		$newinput['amount2'] = intval( trim( $input['amount2'] ) );
		if( ! preg_match( '/^[0-9]+$/i', $input['amount2'] ) )
		{
			$newinput['amount2'] = 50;
		}
	}

	if( isset( $input['maxwidth'] ) )
	{
		$newinput['maxwidth'] = intval( trim( $input['maxwidth'] ) );
		if( ! preg_match( '/^[0-9]+$/i', $input['maxwidth'] ) )
		{
			$newinput['maxwidth'] = 50;
		}
	}

	if( isset( $input['display_dashboard_capability'] ) )
	{
		global $wp_roles;
		$current_user = wp_get_current_user();
		$current_user->get_role_caps();
		$capabilities = $current_user->allcaps;
		foreach( $capabilities as $cap=>$whatever )
		{
			if( $input['display_dashboard_capability'] == $cap )
			{
				$newinput['display_dashboard_capability'] = $input['display_dashboard_capability'];
			}
		}
	}


	if( isset( $input['excludedusers'] ) )
	{
		$newinput['excludedusers'] = implode( ',', $input['excludedusers'] );
	}
	else
	{
		$newinput['excludedusers'] = '';
	}

	//ip exclusion list
	$target_ip_array = array();
	if( isset( $input['excludedip'] ) )
	{
		$input_ip_array = explode( ',', str_replace( ' ', '', trim( $input['excludedip'] ) ) );
		foreach( $input_ip_array as $ip )
		{
			if( preg_match( '/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/', $ip ) )
			{
				$target_ip_array[] = $ip;
			}
		}
		unset( $input_ip_array );
	}
	$newinput['excludedip'] = implode( ',', $target_ip_array );

	if( isset( $input['logbots'] ) )
	{
		$newinput['logbots'] = intval( trim( $input['logbots'] ) );
		if( ! preg_match( '/^(0|1)+$/i', $input['logbots'] ) )
		{
			$newinput['logbots'] = 0;
		}
	}
	else
	{
		$newinput['logbots'] = 0;
	}

	if( isset( $input['whois'] ) )
	{
		$newinput['whois'] = trim( $input['whois'] );
		if( ! preg_match( '#^http[a-zA-Z0-9-\?\.=_:;/]+$#i', $newinput['whois'] ) )
		{
			$newinput['whois'] = 'http://www.ripe.net/whois?form_type=simple&amp;searchtext=';
		}
	}

	if( isset( $input['geoip'] ) )
	{
		$newinput['geoip'] = trim( $input['geoip'] );
		if( ! preg_match( '#^http[a-zA-Z0-9-\?\.=_:;/]+$#i', $newinput['geoip'] ) )
		{
			$newinput['geoip'] = 'http://whatismyipaddress.com/ip/';
		}
	}

	if( isset( $input['enableip'] ) )
	{
		$newinput['enableip'] = trim( $input['enableip'] );
		if( ! preg_match( '/^(0|1)+$/i', $newinput['enableip'] ) )
		{
			$newinput['enableip'] = 0;
		}
	}
	else
	{
		$newinput['enableip'] = 0;
	}

	if( isset( $input['enable_hostname_lookup'] ) )
	{
		$newinput['enable_hostname_lookup'] = intval( trim( $input['enable_hostname_lookup'] ) );
		if( ! preg_match( '/^(0|1)+$/i', $input['enable_hostname_lookup'] ) )
		{
			$newinput['enable_hostname_lookup'] = 0;
		}
	}
	else
	{
		$newinput['enable_hostname_lookup'] = 0;
	}

	if( isset( $input['flushdb'] ) )
	{
		if( $input['flushdb'] == 'wantflushdb' )
		{
			counterize_flush();
			$_SESSION['counterize_flusheddb'] = 'flushed';
		}
	}
	$newinput['flushdb'] = '';

	if( isset( $input['refreshua'] ) )
	{
		if( $input['refreshua'] == 'wantrefreshua' )
		{
			counterize_update_all_userAgents();
			$_SESSION['counterize_refreshedua'] = 'refreshed';
		}
	}
	$newinput['refreshua'] = '';

	if( isset( $input['deletebots'] ) )
	{
		if( $input['deletebots'] == 'wantdeletebots' )
		{
			//counterize_delete_bots();
			counterize_delete_bots_new();
			$_SESSION['counterize_deletedbots'] = 'deleted';
		}
	}
	$newinput['deletebots'] = '';


	/*
	 * Email report section
	 */

	//recipient list textarea
	$target_recipient_array = array();
	if( isset( $input['recipientlist'] ) )
	{
		$input_recipient_array = explode( ',', str_replace( ' ', '', trim( $input['recipientlist'] ) ) );
		foreach( $input_recipient_array as $recipient )
		{
			$target_recipient_array[] = $recipient;
		}
		unset( $input_recipient_array );
	}
	$newinput['recipientlist'] = implode( ',', $target_recipient_array );

	//report period radio
	if( isset( $input['reportperiod'] ) )
	{
		$newinput['reportperiod'] = trim( $input['reportperiod'] );
		if( ! preg_match( '/^(never|daily|weekly|15days|monthly|3months|custom)$/i', $input['reportperiod'] ) )
		{
			$newinput['reportperiod'] = 'never';
		}
	}
	else
	{
		$newinput['reportperiod'] = 'never';
	}

	//custom period input
	if( isset( $input['customperiod'] ) )
	{
		$newinput['customperiod'] = intval( trim( $input['customperiod'] ) );
		if( ! preg_match( '/^[0-9]+$/i', $input['customperiod'] ) )
		{
			$newinput['customperiod'] = 0;
		}
	}
	else
	{
		$newinput['customperiod'] = 0;
	}

	//mail subject override input
	if( isset( $input['mailsubjectoverride'] ) )
	{
		$newinput['mailsubjectoverride'] = trim( $input['mailsubjectoverride'] );
		if( empty( $newinput['mailsubjectoverride'] ) )
		{
			$newinput['mailsubjectoverride'] = '';
		}
	}
	else
	{
		$newinput['customperiod'] = '';
	}

	if( isset( $input['reportwhat'] ) )
	{
		$newinput['reportwhat'] = implode( ',', $input['reportwhat'] );
	}
	else
	{
		$newinput['reportwhat'] = '';
	}

	if( isset( $input['sendreportnow'] ) )
	{
		if( $input['sendreportnow'] == 'wantsendreportnow' )
		{
			if( counterize_send_report_by_email( $newinput ) )
			{
				$_SESSION['counterize_report_status'] = 'sent';
			}
			else
			{
				$_SESSION['counterize_report_status'] = 'error';
			}
		}
	}
	$newinput['sendreportnow'] = '';

	//enable checkbox
	if( isset( $input['enableemailreports'] ) )
	{
		$newinput['enableemailreports'] = intval( trim( $input['enableemailreports'] ) );
		if( ! preg_match( '/^(0|1){1}$/i', $input['enableemailreports'] ) )
		{
			$newinput['enableemailreports'] = 0;
		}
		else
		{
			$newinput = counterize_update_next_report_date( $newinput, $newinput['reportperiod'], $newinput['customperiod'] );
		}
	}
	else
	{
		$newinput['enableemailreports'] = 0;
	}

	return $newinput;
}








// flushes the db and reset the auto_increment to 1 - be careful
function counterize_flush()
{
	global $wpdb;

	$sql = 'DELETE FROM `' . counterize_logTable() . '`';
	$num = $wpdb->query( $sql );
	$sql = 'ALTER IGNORE TABLE `' . counterize_logTable() . '` AUTO_INCREMENT=1';
	$num = $wpdb->query( $sql );

	$sql = 'DELETE FROM `' . counterize_pageTable() . '`';
	$num = $wpdb->query( $sql );
	$sql = 'ALTER IGNORE TABLE `' . counterize_pageTable() . '` AUTO_INCREMENT=1';
	$num = $wpdb->query( $sql );

	$sql = 'DELETE FROM `' . counterize_agentsTable() . '`';
	$num = $wpdb->query( $sql );
	$sql = 'ALTER IGNORE TABLE `' . counterize_agentsTable() . '` AUTO_INCREMENT=1';
	$num = $wpdb->query( $sql );

	$sql = 'DELETE FROM `' . counterize_refererTable() . '`';
	$num = $wpdb->query( $sql );
	$sql = 'ALTER IGNORE TABLE `' . counterize_refererTable() . '` AUTO_INCREMENT=1';
	$num = $wpdb->query( $sql );

	$sql = 'DELETE FROM `' . counterize_keywordTable() . '`';
	$num = $wpdb->query( $sql );
	$sql = 'ALTER IGNORE TABLE `' . counterize_keywordTable() . '` AUTO_INCREMENT=1';
	$num = $wpdb->query( $sql );

	$sql = 'UPDATE IGNORE `' . counterize_countryTable() . '` SET `count`=0';
	$num = $wpdb->query( $sql );
}


function counterize_update_all_userAgents()
{
	global $wpdb;
	$agents = $wpdb->get_results( 'SELECT agentID, name FROM `' . counterize_agentsTable() . '`' );
	foreach( $agents as $agent )
	{
		list(
			$browser_name, $browser_code, $browser_ver, $browser_url,
			$os_name, $os_code, $os_ver, $os_url,
			$pda_name, $pda_code, $pda_ver, $pda_url
		) = counterize_detect_browser( $agent->name );

		$query = $wpdb->prepare( 'UPDATE IGNORE `' . counterize_agentsTable() . '` SET '
			. ' `browserName`    = %s, '
			. ' `browserCode`    = %s, '
			. ' `browserVersion` = %s, '
			. ' `browserURL`     = %s, '
			. ' `osName`         = %s, '
			. ' `osCode`         = %s, '
			. ' `osVersion`      = %s, '
			. ' `osURL`          = %s '
			. ' WHERE `agentID` = %d ',
			$browser_name,
			$browser_code,
			$browser_ver,
			$browser_url,
			$os_name,
			$os_code,
			$os_ver,
			$os_url,
			$agent->agentID );

		$wpdb->query( $query );
	}
	unset( $agents );
}

/*
//Delete the bots from the database. There is probably a more efficient way to do this (especially, I don't like those two imbricated foreach).
function counterize_delete_bots()
{
	global $wpdb;

	//load the bot array
	$botarray = array();
	if( file_exists( COUNTERIZE_PLUGIN_DIR . '/botlist.txt' ) )
	{
		$botarray = file( COUNTERIZE_PLUGIN_DIR . '/botlist.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES );
	}

	//load the user-agents
	$agents = $wpdb->get_results( 'SELECT `agentID`, `name` FROM `' . counterize_agentsTable() . '` ORDER BY `agentID` DESC' );


	//for each agent, search if it contains a bot string
	foreach( $agents as $agent )
	{

		// Run through bot-array and see if there's anything we don't like...
		foreach( $botarray as $entry )
		{
			//Check for special parameters to allow complex bot search without
			// blocking the user agent completely.
			//Formats are :
			// "complexfilter:Suspicious user-agent string###/page_url_without_domain_name###Complete_referer_string" without quotes
			// "regexp:regular_expression" without quotes
			//Desired effect of the first format is: remove bot entries without deleting legitimate
			// entries, in case the bot is faking a real browser user agent but
			// always use the same referer and page, which real users do not.
			$is_regexp  = ( strpos( $entry, 'regexp:' ) !== FALSE );
			$is_complex = ( strpos( $entry, 'complexfilter:' ) !== FALSE );

			if( $is_complex )
			{
				$entry = str_replace( 'complexfilter:', '', $entry );
				$temp = explode( '###', $entry );
				$bot = $temp[0];
				if( isset( $temp[1] ) && isset( $temp[2] ) )
				{
					$page = $temp[1];
					$referer = $temp[2];
					if( $referer == "%HTTP_HOST%" )
					{
						$referer = 'http://' . $_SERVER['HTTP_HOST'] . '/';
					}
				}
				else
				{
					//by security
					$is_complex = false;
				}
				unset( $temp );
				$condition = stristr( $agent->name, $bot ) !== FALSE;
			}
			elseif( $is_regexp )
			{
				$bot = str_replace( 'regexp:', '', $entry );
				$referer = '';
				$page = '';
				$condition = preg_match( $bot, $agent->name );
			}
			else
			{
				$bot = $entry;
				$referer = '';
				$page = '';
				$condition = stristr( $agent->name, $bot ) !== FALSE;
			}

			//if we found a bot then
			if( $condition )
			{
				//if we have a complex filter
				if( $is_complex && !empty( $referer ) && !empty( $page ) )
				{
					if( $page != '*' )
					{
						//retrieve the pageID to decount
						$pageid = $wpdb->get_var( 'SELECT `pageID` FROM `' . counterize_pageTable() . "` WHERE `url`=" . $wpdb->prepare( "%s", $page ) . " LIMIT 1" );
					}

					//retrieve the refererID to decount
					$refererid = $wpdb->get_var( 'SELECT `refererID` FROM `' . counterize_refererTable() . "` WHERE `name`=" . $wpdb->prepare( "%s", $referer ) . " LIMIT 1" );

					//get the number of records to decount from page and referer tables
					if( $page != '*' )
					{
						$sql = "SELECT COUNT(`id`) "
							. " FROM `" . counterize_logTable() . "`"
							. " WHERE `agentID`=%d "
							. " AND `refererID`=%d "
							. " AND `pageID`=%d";
						$query = $wpdb->prepare( $sql, $agent->agentID, $pageid, $refererid );
					}
					else
					{
						$sql = "SELECT COUNT(`id`) "
							. " FROM `" . counterize_logTable() . "`"
							. " WHERE `agentID`=%d "
							. " AND `refererID`=%d ";
						$query = $wpdb->prepare( $sql, $agent->agentID, $refererid );
					}
					$cnt = intval( $wpdb->get_var( $query ) );
					unset( $sql, $query );

					//if the number is > 0 we have to decount this number from some tables
					if( $cnt > 0 )
					{
						//substract the visit count of the bot from the agent table
						$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_agentsTable() . "` SET `count`=`count` - %d WHERE `agentID`=%d", $cnt, $agent->agentID );
						$wpdb->query( $query );
						unset( $query );

						if( $page != '*' )
						{
							//substract the visit count of the bot from the page table
							$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_pageTable() . "` SET `count`=`count` - %d WHERE `pageID`=%d", $cnt, $pageid );
							$wpdb->query( $query );
						}
						else
						{
							//we have to search in all the records the one who match the agentID and refererID, and decount them
							$query = $wpdb->prepare( "SELECT COUNT(`id`) AS NB, `pageID` FROM `" . counterize_logTable() . "` WHERE `agentID`=%d AND `refererID`=%d GROUP BY `pageID`", $agent->agentID, $refererid );
							$page_entries = $wpdb->get_results( $query );
							unset( $query );
							if( count( $page_entries ) > 0 )
							{
								foreach( $page_entries as $page_entry )
								{
									//substract the visit count of the bot from the page table
									$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_pageTable() . "` SET `count`=`count` - %d WHERE `pageID`=%d", $page_entry->NB, $page_entry->pageID );
									$wpdb->query( $query );
									unset( $query );
								}
								unset( $page_entries );
							}
						}

						//substract the visit count of the bot from the referer table
						$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_refererTable() . "` SET `count`=`count` - %d WHERE `refererID`=%d", $cnt, $refererid );
						$wpdb->query( $query );
						unset( $query );

						//substract it from the countries table
						if( $page != '*' )
						{
							$query = $wpdb->prepare( "SELECT COUNT(`id`) AS NB, `countryID` FROM `" . counterize_logTable() . "` WHERE `agentID`=%d AND `refererID`=%d AND `pageID`=%d GROUP BY `countryID`", $agent->agentID, $refererid, $pageid );
						}
						else
						{
							$query = $wpdb->prepare( "SELECT COUNT(`id`) AS NB, `countryID` FROM `" . counterize_logTable() . "` WHERE `agentID`=%d AND `refererID`=%d GROUP BY `countryID`", $agent->agentID, $refererid );
						}
						$country_entries = $wpdb->get_results( $query );
						unset( $query );
						if( count( $country_entries ) > 0 )
						{
							foreach( $country_entries as $country_entry )
							{
								$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_countryTable() . "` SET `count`=`count` - %d WHERE `countryID`=%d", $country_entry->NB, $country_entry->countryID );
								$wpdb->query( $query );
								unset( $query );
							}
							unset( $country_entries );
						}

						//delete the bot entries in the logtable
						if( $page != '*' )
						{
							$query = $wpdb->prepare( "DELETE FROM `" . counterize_logTable() . "` WHERE `agentID`=%d AND `pageID`=%d AND `refererID`=%d", $agent->agentID, $pageid, $refererid );
						}
						else
						{
							$query = $wpdb->prepare( "DELETE FROM `" . counterize_logTable() . "` WHERE `agentID`=%d AND `refererID`=%d", $agent->agentID, $refererid );
						}
						$wpdb->query( $query );
						unset( $query );

						//delete invalid entries from the log table
						$query = "DELETE FROM `" . counterize_logTable() . "` WHERE `agentID`=0";
						$wpdb->query( $query );
						unset( $query );
					}
				}
				else //we just have to delete this bot and substract its hit count
				{
					//substract it from the pages table
					$query = $wpdb->prepare( "SELECT COUNT(`id`) AS NB, `pageID` FROM `" . counterize_logTable() . "` WHERE `agentID`= %d GROUP BY `pageID`", $agent->agentID );
					$page_entries = $wpdb->get_results( $query );
					unset( $query );
					if( count( $page_entries ) > 0 )
					{
						foreach( $page_entries as $page_entry )
						{
							$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_pageTable() . "` SET `count`=`count` - %d WHERE `pageID`=%d", $page_entry->NB, $page_entry->pageID );
							$wpdb->query( $query );
							unset( $query );
						}
						unset( $page_entries );
					}

					//substract it from the referers table
					$query = $wpdb->prepare( "SELECT COUNT(`id`) AS NB, `refererID` FROM `" . counterize_logTable() . "` WHERE `agentID`= %d GROUP BY `refererID`", $agent->agentID );
					$referer_entries = $wpdb->get_results( $query );
					unset( $query );
					if( count( $referer_entries ) > 0 )
					{
						foreach( $referer_entries as $referer_entry )
						{
							$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_refererTable() . "` SET `count`=`count` - %d WHERE `refererID`=%d", $referer_entry->NB, $referer_entry->refererID );
							$wpdb->query( $query );
						}
						unset( $referer_entries );
					}

					//substract it from the countries table
					$query = $wpdb->prepare( "SELECT COUNT(`id`) AS NB, `countryID` FROM `" . counterize_logTable() . "` WHERE `agentID`=%d GROUP BY `countryID`", $agent->agentID );
					$country_entries = $wpdb->get_results( $query );
					unset( $query );
					if( count( $country_entries ) > 0 )
					{
						foreach( $country_entries as $country_entry )
						{
							$query = $wpdb->prepare( "UPDATE IGNORE `" . counterize_countryTable() . "` SET `count`=`count` - %d WHERE `countryID`=%d", $country_entry->NB, $country_entry->countryID );
							$wpdb->query( $query );
							unset( $query );
						}
						unset( $country_entries );
					}

					//delete it from the log table
					$query = $wpdb->prepare( "DELETE FROM `" . counterize_logTable() . "` WHERE `agentID`=%d OR `agentID`=0", $agent->agentID );
					$num = $wpdb->query( $query );
					unset( $query );

					//delete it from the user-agent table
					$query = $wpdb->prepare( "DELETE FROM `" . counterize_agentsTable() . "` WHERE `agentID`=%d", $agent->agentID );
					$num = $wpdb->query( $query );
					unset( $query );
				}
				break;
			}
		}
	}
	unset( $agents );
	unset( $botarray );

	//we need to do some cleaning...
	counterize_delete_broken_entries();
}
*/

/*
 * Filters an array and return only the elements containing or matching needle
 */
function counterize_array_match( $needle, $haystack, $case_sensitive = false, $use_regex = false )
{
	$array = array();
	foreach( $haystack as $key => $value )
	{
		if( strpos( $value, $needle ) !== FALSE || ( $case_sensitive && stripos( $value, $needle ) !== FALSE ) || ( $use_regex && preg_match( $needle, $value ) > 0 ) )
		{
			$array[ $key ] = $value;
		}
	}
	return $array;
}

/*
 * The new function to delete bots, should be much faster
 * because there are no queries inside the loops
 */
function counterize_delete_bots_new()
{
	global $wpdb;

	//load the bot array
	$botarray = array();
	if( file_exists( COUNTERIZE_PLUGIN_DIR . '/botlist.txt' ) )
	{
		$botarray = apply_filters( 'counterize_bot_array', file( COUNTERIZE_PLUGIN_DIR . '/botlist.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES ) );
	}
	//load the user bot file
	if( file_exists( COUNTERIZE_PLUGIN_DIR . '/user_botlist.txt' ) )
	{
		$botarray = $botarray + file( COUNTERIZE_PLUGIN_DIR . '/user_botlist.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES );
	}

	//load the user-agents
	$agents = $wpdb->get_results( 'SELECT `agentID`, `name` FROM `' . counterize_agentsTable() . '` ORDER BY `agentID` DESC' );

	//we make a new array more efficient stored
	$agents_array = array();
	foreach( $agents as $agent )
	{
		$agents_array[ $agent->agentID ] = $agent->name;
	}

	//search for all the agentID to delete and put them into an array
	$agents_to_del     = array();
	$agents_to_decount = array();

	foreach( $botarray as $entry )
	{
		//Check for special parameters to allow complex bot search without
		// blocking the user agent completely.
		//Formats are :
		// "complexfilter:Suspicious user-agent string###/page_url_without_domain_name###Complete_referer_string" without quotes
		// "regexp:regular_expression" without quotes
		//Desired effect of the first format is: remove bot entries without deleting legitimate
		// entries, in case the bot is faking a real browser user agent but
		// always use the same referer and page, which real users do not.
		$is_regexp  = ( strpos( $entry, 'regexp:' ) !== FALSE );
		$is_complex = ( strpos( $entry, 'complexfilter:' ) !== FALSE );

		if( $is_complex )
		{
			$entry = str_replace( 'complexfilter:', '', $entry );
			$temp = explode( '###', $entry );
			$bot = $temp[0];
			if( isset( $temp[1] ) && isset( $temp[2] ) )
			{
				$page = $temp[1];
				$referer = $temp[2];
				if( $referer == "%HTTP_HOST%" )
				{
					$referer = 'http://' . $_SERVER['HTTP_HOST'] . '/';
				}

				$arr_tmp = array();
				$regex = '#^' . strtr( addcslashes( $bot, '.+^$(){}=!<>|#' ), array( '*' => '.*', '?' => '.?' ) ) . '$#i';
				$arr_matches = counterize_array_match( $regex, $agents_array, FALSE, TRUE );
				foreach( $arr_matches as $agentID => $name )
				{
					$arr_tmp[ $agentID ]['name']    = $name;
					$arr_tmp[ $agentID ]['page']    = $page;
					$arr_tmp[ $agentID ]['referer'] = $referer;
				}

				$agents_to_decount = $agents_to_decount + $arr_tmp;
			}
			else
			{
				//by security
				$is_complex = false;
			}
			unset( $temp );
		}
		elseif( $is_regexp )
		{
			$bot = str_replace( 'regexp:', '', $entry );
			$referer = '';
			$page = '';
			$agents_to_del = $agents_to_del + counterize_array_match( $bot, $agents_array, FALSE, TRUE );
		}
		else
		{
			$bot = $entry;
			$referer = '';
			$page = '';
			$agents_to_del = $agents_to_del + counterize_array_match( $bot, $agents_array, TRUE );
		}
	}

	/*
	 * at this point, $agents_to_del contains the agents( id => name ) to delete
	 * and $agents_to_decount contains the agents( id => array( name, page, referer ) ) to maybe decount
	 */

	/*
	 * we generate the SQL commands for normal and regex
	 */
	$sql_pages = 'UPDATE IGNORE `' . counterize_pageTable() . '` AS P'
				. ' JOIN `' . counterize_logTable() . '` AS L ON P.pageID = L.pageID'
				. ' SET P.`count` = P.`count` - 1'
				. ' WHERE L.agentID IN ( ';
	$sql_referers = 'UPDATE IGNORE `' . counterize_refererTable() . '` AS R'
				. ' JOIN `' . counterize_logTable() . '` AS L ON R.refererID = L.refererID'
				. ' SET R.`count` = R.`count` - 1'
				. ' WHERE L.agentID IN ( ';
	$sql_countries = 'UPDATE IGNORE `' . counterize_countryTable() . '` AS C'
				. ' JOIN `' . counterize_logTable() . '` AS L ON C.countryID = L.countryID'
				. ' SET C.`count` = C.`count` - 1'
				. ' WHERE L.agentID IN ( ';
	$sql_agents = 'DELETE FROM `' . counterize_agentsTable() . '` WHERE agentID IN( ';
	$sql_logtable = 'DELETE FROM `' . counterize_logTable() . '` WHERE agentID IN( 0, ';


	if( ! empty( $agents_to_del ) )
	{
		$last_key = end( array_keys( $agents_to_del ) );
		foreach( $agents_to_del as $id => $agent )
		{
			$sql_pages     .= $id;
			$sql_referers  .= $id;
			$sql_countries .= $id;
			$sql_agents    .= $id;
			$sql_logtable  .= $id;
			if( $id !== $last_key )
			{
				$sql_pages     .= ', ';
				$sql_referers  .= ', ';
				$sql_countries .= ', ';
				$sql_agents    .= ', ';
				$sql_logtable  .= ', ';
			}
		}
		$sql_pages     .= ' ) ;';
		$sql_referers  .= ' ) ;';
		$sql_countries .= ' ) ;';
		$sql_agents    .= ' ) ;';
		$sql_logtable  .= ' ) ;';

		/*
		 * Execute the queries
		 */
		$wpdb->query( $sql_pages );
		$wpdb->query( $sql_referers );
		$wpdb->query( $sql_countries );
		$wpdb->query( $sql_agents );
		$wpdb->query( $sql_logtable );

	}

	/*
	 * we generate the SQL commands for complex filters
	 */
	$sql_pages_1_1 = 'UPDATE IGNORE `' . counterize_pageTable() . '` AS P'
				. ' JOIN `' . counterize_logTable()       . '` AS L ON L.pageID    = P.pageID'
				. ' JOIN `' . counterize_agentsTable()    . '` AS A ON L.agentID   = A.agentID'
				. ' JOIN `' . counterize_refererTable()   . '` AS R ON L.refererID = R.refererID'
				. ' JOIN `' . counterize_countryTable()   . '` AS C ON L.countryID = C.countryID'
				. ' SET P.`count` = P.`count` - 1, '
				. '     A.`count` = A.`count` - 1, '
				. '     R.`count` = R.`count` - 1, '
				. '     C.`count` = C.`count` - 1 '
				. ' WHERE L.agentID IN ( ';
	$sql_pages_1_2 = ' AND P.url IN ( ';
	$sql_pages_1_3 = ' AND R.name IN ( ';

	$sql_logtable_1_1 = 'DELETE FROM `' . counterize_logTable() . '` AS L'
				. ' USING L'
				. ' JOIN `' . counterize_refererTable() . '` AS R ON L.refererID = R.refererID'
				. ' JOIN `' . counterize_pageTable()    . '` AS P ON L.pageID    = P.pageID'
				. ' WHERE L.agentID IN ( ';
	$sql_logtable_1_2 = ' AND P.url IN ( ';
	$sql_logtable_1_3 = ' AND R.name IN ( ';

	$sql_pages_2_1 = 'UPDATE IGNORE `' . counterize_logTable()  . '` AS L'
				. ' JOIN `' . counterize_agentsTable()    . '` AS A ON L.agentID   = A.agentID'
				. ' JOIN `' . counterize_refererTable()   . '` AS R ON L.refererID = R.refererID'
				. ' JOIN `' . counterize_countryTable()   . '` AS C ON L.countryID = C.countryID'
				. ' SET A.`count` = A.`count` - 1, '
				. '     R.`count` = R.`count` - 1, '
				. '     C.`count` = C.`count` - 1 '
				. ' WHERE L.agentID IN ( ';
	$sql_pages_2_2 = ' AND R.name IN ( ';

	$sql_logtable_2_1 = 'DELETE FROM `' . counterize_logTable() . '` AS L'
				. ' USING L'
				. ' JOIN `' . counterize_refererTable() . '` AS R ON L.refererID = R.refererID'
				. ' WHERE L.agentID IN ( ';
	$sql_logtable_2_2 = ' AND R.name IN ( ';

	if( ! empty( $agents_to_decount ) )
	{


		$page_cnt = 0;
		$nopage_cnt = 0;
		foreach( $agents_to_decount as $id => $agent )
		{
			if( $agent['page'] != '*' )
			{
				if( $page_cnt > 0 )
				{
					$sql_pages_1_1 .= ',';
					$sql_pages_1_2 .= ',';
					$sql_pages_1_3 .= ',';

					$sql_logtable_1_1 .= ',';
					$sql_logtable_1_2 .= ',';
					$sql_logtable_1_3 .= ',';
				}

				$page_cnt++;

				$sql_pages_1_1 .= $wpdb->prepare( "%s", $id );
				$sql_pages_1_2 .= $wpdb->prepare( "%s", $agent['page'] );
				$sql_pages_1_3 .= $wpdb->prepare( "%s", $agent['referer'] );

				$sql_logtable_1_1 .= $wpdb->prepare( "%s", $id );
				$sql_logtable_1_2 .= $wpdb->prepare( "%s", $agent['page'] );
				$sql_logtable_1_3 .= $wpdb->prepare( "%s", $agent['referer'] );
			}
			else
			{
				if( $nopage_cnt > 0)
				{
					$sql_pages_2_1 .= ',';
					$sql_pages_2_2 .= ',';

					$sql_logtable_2_1 .= ',';
					$sql_logtable_2_2 .= ',';
				}

				$nopage_cnt++;

				$sql_pages_2_1 .= $wpdb->prepare( "%s", $id );
				$sql_pages_2_2 .= $wpdb->prepare( "%s", $agent['referer'] );

				$sql_logtable_2_1 .= $wpdb->prepare( "%s", $id );
				$sql_logtable_2_2 .= $wpdb->prepare( "%s", $agent['referer'] );
			}
		}
		$sql_pages_1_1 .= ' )'
					   . $sql_pages_1_2 . ' )'
					   . $sql_pages_1_3 . ' ) ;';

		$sql_logtable_1_1 .= ' )'
					   . $sql_logtable_1_2 . ' )'
					   . $sql_logtable_1_3 . ' ) ;';

		$sql_pages_2_1 .= ' )' . $sql_pages_2_2 . ' ) ;';

		$sql_logtable_2_1 .= ' )' . $sql_logtable_2_2 . ' ) ;';

		/*
		 * Execute the queries
		 */
		if( $page_cnt > 0 )
		{
			$wpdb->query( $sql_pages_1_1 );
			$wpdb->query( $sql_logtable_1_1 );
		}
		if( $nopage_cnt > 0 )
		{
			$wpdb->query( $sql_pages_2_1 );
			$wpdb->query( $sql_logtable_2_1 );
		}
	}

	unset( $agents );
	unset( $agents_array );
	unset( $agents_to_del );
	unset( $agents_to_decount );
	unset( $botarray );

	//we need to do some cleaning...
	counterize_delete_broken_entries();
}

// deletes a entry from the database
function counterize_killEntry( $entryID )
{
	global $wpdb;
	$entries_tmp = counterize_getentries_sql( 1, $entryID );
	$entries = $wpdb->get_results( $entries_tmp[0] );

	foreach( $entries as $entry )
	{
		$sql = "DELETE FROM `" . counterize_logTable() . "` WHERE `id`={$entry->id}";
		$num = $wpdb->query( $sql );
		unset( $sql );

		$sql = "UPDATE IGNORE `" . counterize_pageTable() . "` SET `count` = `count` - 1 WHERE `pageID`={$entry->pageID}";
		$num = $wpdb->query( $sql );
		unset( $sql );

		$sql = "UPDATE IGNORE `" . counterize_refererTable() . "` SET `count` = `count` - 1 WHERE `refererID`={$entry->refererID}";
		$num = $wpdb->query( $sql );
		unset( $sql );

		$sql = "UPDATE IGNORE `" . counterize_agentsTable() . "` SET `count` = `count` - 1 WHERE `agentID`={$entry->agentID}";
		$num = $wpdb->query( $sql );
		unset( $sql );

		$sql = "UPDATE IGNORE `" . counterize_keywordTable() . "` SET `count` = `count` - 1 WHERE `keywordID`={$entry->keywordID}";
		$num = $wpdb->query( $sql );
		unset( $sql );

		$sql = "UPDATE IGNORE `" . counterize_countryTable() . "` SET `count` = `count` - 1 WHERE `countryID`={$entry->countryID}";
		$num = $wpdb->query( $sql );
		unset( $sql );
	}
	unset( $entries_tmp, $entries );
}

/*
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();

$time_end = microtime_float();
$time = $time_end - $time_start;
echo "Did nothing in $time seconds\n";
die();
*/

/*
 * delete broken entries from the log table and decount them from the other tables
 */
function counterize_delete_broken_entries()
{
	global $wpdb;



	//delete entries related to inexistant agentID
	$sql = "SELECT `agentID`, `pageID`, `refererID`, `countryID` "
		. " FROM `" . counterize_logTable() . "` "
		. " WHERE `agentID` NOT IN ( SELECT `agentID` FROM `" . counterize_agentsTable() . "` )";
	$entries = $wpdb->get_results( $sql );

	unset( $sql );
	if( count( $entries ) > 0 )
	{
		foreach( $entries as $entry )
		{
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_pageTable()    . "` SET `count`=`count` - 1 WHERE `pageID`=%d"   , $entry->pageID    ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_refererTable() . "` SET `count`=`count` - 1 WHERE `refererID`=%d", $entry->refererID ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_countryTable() . "` SET `count`=`count` - 1 WHERE `countryID`=%d", $entry->countryID ) );
		}
		unset( $entries );
		$sql = "DELETE FROM `" . counterize_logTable() . "` "
			. " WHERE `agentID` NOT IN ( SELECT `agentID` FROM `" . counterize_agentsTable() . "` )";
		$wpdb->query( $sql );
		unset( $sql );
	}

	//delete entries related to inexistant refererID
	$sql = "SELECT `agentID`, `pageID`, `refererID`, `countryID` "
		. " FROM `" . counterize_logTable() . "` "
		. " WHERE `refererID` NOT IN ( SELECT `refererID` FROM `" . counterize_refererTable() . "` )";
	$entries = $wpdb->get_results( $sql );
	unset( $sql );
	if( count( $entries ) > 0 )
	{
		foreach( $entries as $entry )
		{
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_agentsTable()  . "` SET `count`=`count` - 1 WHERE `agentID`=%d"  , $entry->agentID   ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_pageTable()    . "` SET `count`=`count` - 1 WHERE `pageID`=%d"   , $entry->pageID    ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_countryTable() . "` SET `count`=`count` - 1 WHERE `countryID`=%d", $entry->countryID ) );
		}
		unset( $entries );
		$sql = "DELETE FROM `" . counterize_logTable() . "` "
			. " WHERE `refererID` NOT IN ( SELECT `refererID` FROM `" . counterize_refererTable() . "` )";
		$wpdb->query( $sql );
		unset( $sql );
	}

	//delete entries related to inexistant pageID
	$sql = "SELECT `agentID`, `pageID`, `refererID`, `countryID` "
		. " FROM `" . counterize_logTable() . "` "
		. " WHERE `pageID` NOT IN ( SELECT `pageID` FROM `" . counterize_pageTable() . "` )";
	$entries = $wpdb->get_results( $sql );
	unset( $sql );
	if( count( $entries ) > 0 )
	{
		foreach( $entries as $entry )
		{
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_agentsTable()  . "` SET `count`=`count` - 1 WHERE `agentID`=%d"  , $entry->agentID   ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_refererTable() . "` SET `count`=`count` - 1 WHERE `refererID`=%d", $entry->refererID ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_countryTable() . "` SET `count`=`count` - 1 WHERE `countryID`=%d", $entry->countryID ) );
		}
		unset( $entries );
		$sql = "DELETE FROM `" . counterize_logTable() . "` "
			. " WHERE `pageID` NOT IN ( SELECT `pageID` FROM `" . counterize_pageTable() . "` )";
		$wpdb->query( $sql );
		unset( $sql );
	}

	//delete entries related to inexistant countryID
	$sql = "SELECT `agentID`, `pageID`, `refererID`, `countryID` "
		. " FROM `" . counterize_logTable() . "` "
		. " WHERE `countryID` NOT IN ( SELECT `countryID` FROM `" . counterize_countryTable() . "` )";
	$entries = $wpdb->get_results( $sql );
	unset( $sql );
	if( count( $entries ) > 0 )
	{
		foreach( $entries as $entry )
		{
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_agentsTable()  . "` SET `count`=`count` - 1 WHERE `agentID`=%d"  , $entry->agentID   ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_refererTable() . "` SET `count`=`count` - 1 WHERE `refererID`=%d", $entry->refererID ) );
			$wpdb->query( $wpdb->prepare( "UPDATE IGNORE `" . counterize_pageTable()    . "` SET `count`=`count` - 1 WHERE `pageID`=%d"   , $entry->pageID    ) );
		}
		unset( $entries );
		$sql = "DELETE FROM `" . counterize_logTable() . "` "
			. " WHERE `countryID` NOT IN ( SELECT `countryID` FROM `" . counterize_countryTable() . "` )";
		$wpdb->query( $sql );
		unset( $sql );
	}

	//delete orphan keywords records (not linked to any referer entry)
	$sql = "DELETE FROM `" . counterize_keywordTable() . "` "
		. " WHERE `keywordID` NOT IN ( SELECT `keywordID` FROM `" . counterize_refererTable() . "` )";
	$wpdb->query( $sql );
	unset( $sql );
}


?>