<?php
/*
 Plugin Name: Counterize plugin: IP addresses
 Plugin URI: http://www.gabsoftware.com/products/scripts/counterize/plugins/ip
 Description: Display some information about the IP addresses of your visitors for the Counterize plugin
 Version: 3.1.5
 Author: Gabriel Hautclocq
 Author URI: http://www.gabsoftware.com/
 License: ISC
*/


// Security check. We do not want to be able to access our plugin directly.
if( !defined( 'WP_PLUGIN_DIR') )
{
	die( "There is nothing to see here." );
}

//removes the plugin update notification
add_filter( 'site_transient_update_plugins', 'counterize_plugin_ip_disable_update_check' );

// Security check #2. We do not want to be able to load the plugin if Counterize isn't loaded before.
if( !defined( 'COUNTERIZE_CHECK') )
{
	add_action('admin_notices', 'counterize_plugin_ip_admin_notice');
	return;
}

/* constants */

// Counterize IP Addresses plugin text domain
define( 'COUNTERIZE_PLUGIN_IP_TD', COUNTERIZE_TD );

// Absolute path of the plugin from the server view
define( 'COUNTERIZE_PLUGIN_IP_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__) ) );

// Public URL of the plugin
define( 'COUNTERIZE_PLUGIN_IP_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__) ) );

/*
 * Prevents WordPress from displaying wrong update notifications because
 * this plugin is located in the same directory as Counterize
 */
function counterize_plugin_ip_disable_update_check( $value = '' )
{
	if( ( isset( $value->response ) ) && ( count( $value->response ) ) )
	{
		$my_plugin = plugin_basename( dirname( __FILE__) ) . '/' . basename( __FILE__ );

		// Get the list cut current active plugins
		$active_plugins = get_option('active_plugins');

		if( $active_plugins )
		{
			//  Here we start to compare the $value->response
			//  items checking each against the active plugins list.
			if( array_key_exists( $my_plugin, $value->response ) )
			{
				unset( $value->response[ $my_plugin ] );
			}
		}
	}
	return $value;
}

/*
 * This function will be called if Counterize was not found or was not activated.
 */
function counterize_plugin_ip_admin_notice()
{
	global $pagenow;
	if( current_user_can( 'install_plugins' ) && $pagenow == 'plugins.php' )
	{
		echo '<div class="error"><p>';
		echo 'The Counterize plugin "IP addresses" could not find Counterize. Please install and activate the <a target="_blank" href="plugin-install.php?tab=plugin-information&amp;plugin=counterize&amp;TB_iframe=false&amp;width=600&amp;height=550">Counterize plugin</a> first.';
		echo '</p></div>';
	}
}

class Counterize_Plugin_IP
{
	/*
	 * Our plugin constructor
	 */
	function __construct()
	{
		// Place your add_actions and add_filters here
		add_action( 'init', array( &$this, 'counterize_plugin_ip_init_callback' ) );

	} // end of constructor

	/*
	 * Place your plugin initialization code here
	 * For example, load your plugin translations here, not before.
	 */
	public function counterize_plugin_ip_init_callback()
	{
		//increment the plugin counter
		global $counterize_plugin_count;
		$counterize_plugin_count++;

		// Load the plugin localization (.mo files) located
		// in the plugin "lang" subdirectory
		if( function_exists( 'load_plugin_textdomain' ) )
		{
			load_plugin_textdomain(
				COUNTERIZE_PLUGIN_IP_TD,
				COUNTERIZE_PLUGIN_IP_DIR . '/lang',
				plugin_basename( dirname(__FILE__) ) . '/lang'
			);
		}

		//add a submenu page for our plugin
		add_filter( 'counterize_dashboard_add_submenu', array( &$this, 'counterize_dashboard_add_submenu_ip_callback' ), 10, 2 );
		add_action( 'counterize_show_data', array( &$this, 'counterize_show_data_ip' ), 10, 3 );
		add_filter( 'counterize_check_data', array( &$this, 'counterize_check_data_ip' ), 10, 1 );

		//add a submenu in the Counterize toolbar
		add_action( 'counterize_toolbar_add_submenu', array( &$this, 'counterize_toolbar_add_submenu_callback' ), 10, 1 );

		//add shortcodes
		add_filter( 'counterize_shortcodes', array( &$this, 'counterize_plugin_ip_shortcodes_callback' ), 10, 3 );

		//filters for the Counterize button in TinyMCE
		add_filter( 'counterize_mce_js_type_filter', array( &$this, 'counterize_mce_js_type_filter_callback' ), 10, 1 );

		//filter for the report option in Counterize settings
		add_filter( 'counterize_report_what_filter', array( &$this, 'counterize_report_what_filter_callback' ), 10, 1 );

		//generate the report
		add_filter( 'counterize_report', array( &$this, 'counterize_plugin_report_callback' ), 10, 2 );

	} // end of function

	public function counterize_dashboard_add_submenu_ip_callback( $counterize_dashboard_handles, $capability )
	{
		$counterize_dashboard_handles['ip'] = add_submenu_page
		(
			COUNTERIZE_MENU_SLUG,
			__( 'Counterize', COUNTERIZE_PLUGIN_IP_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_IP_TD ) . ' - ' . __( 'IP addresses', COUNTERIZE_PLUGIN_IP_TD ),
			__( 'IP addresses', COUNTERIZE_PLUGIN_IP_TD ),
			$capability,
			'counterize_dashboard_ip',
			array( &$this, 'counterize_display_dashboard_ip_page_callback' )
		);

		//load the help tabs
		add_action( 'load-' . $counterize_dashboard_handles['ip'], array( &$this, 'counterize_add_help_tabs_to_ip_callback' ) );

		//insert the stylesheet and script
		add_action( "admin_print_scripts-{$counterize_dashboard_handles['ip']}", 'counterize_javascript_head_callback' );
		add_action( "admin_print_styles-{$counterize_dashboard_handles['ip']}" , 'counterize_stylesheet_head_callback' );

		return $counterize_dashboard_handles;
	}

	public function counterize_toolbar_add_submenu_callback( $wp_admin_bar )
	{
		// add the All stats node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_ip',
			'title'  => '<span class="ab-icon" style="margin-top: 3px; margin-right: 5px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/ip_16x16.png" alt="Counterize icon" /></span>'
						. __( 'IP addresses' ),
			'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard_ip' ), // link to the related Counterize Dashboard entry
			'parent' => COUNTERIZE_TOOLBAR_STATS_GROUP_SLUG
		);
		$wp_admin_bar->add_node( $args );
	}

	/*
	 * display help tabs
	 */
	public function counterize_add_help_tabs_to_ip_callback()
	{
		$screen = get_current_screen();
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-ip-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most active IP addresses', COUNTERIZE_PLUGIN_IP_TD ),
				'content' => __( '<p>Displays a diagram of the most active IP addresses.</p>', COUNTERIZE_PLUGIN_IP_TD )
						. __( '<p>This is useful to check how many pages your visitors visit.</p>', COUNTERIZE_PLUGIN_IP_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-ip24hr-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most active IP addresses for the last 24 hours', COUNTERIZE_PLUGIN_IP_TD ),
				'content' => __( '<p>Displays a diagram of the most active IP addresses for the last 24 hours only.</p>', COUNTERIZE_PLUGIN_IP_TD )
						. __( '<p>This is useful to check how many pages your visitors visit.</p>', COUNTERIZE_PLUGIN_IP_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->set_help_sidebar
		(
			__( '<p>Choose a tab to learn more about the related diagram.</p>', COUNTERIZE_PLUGIN_IP_TD )
		);
	}

	public function counterize_display_dashboard_ip_page_callback()
	{
		// Amount to pass as option to the graphs...
		$amount2 = counterize_get_option( 'amount2' );
		if( $amount2 == '' || $amount2 == ' ' || ! is_numeric( $amount2 ) )
		{
			$amount2 = 10;
		}

		echo '
		<div id="icon-plugins" class="icon32"></div>
		<h1>' . __( 'Counterize', COUNTERIZE_PLUGIN_IP_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_IP_TD ) . ' - ' . __( 'IP addresses', COUNTERIZE_PLUGIN_IP_TD ) . '</h1>
		';

		$this->counterize_show_data_ip( true, $amount2, true );

		//Print the footer
		counterize_pagefooter();
	}

	//faster alternative to gethostbyaddr()
	private function gethost( $ip )
	{
		//Make sure the input is not going to do anything unexpected
		//IPs must be in the form x.x.x.x with each x as a number

		if( preg_match( '/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ip ) )
		{
			$host = `host -s -W 1 $ip`;
			$host = ( $host ? end( explode( ' ', trim( trim( $host ), '.' ) ) ) : $ip );
			if( in_array( $host, array( 'reached', 'record', '2(SERVFAIL)', '3(NXDOMAIN)' ) ) )
			{
				return sprintf( __( '(error fetching domain name for %s)', COUNTERIZE_PLUGIN_IP_TD ), $ip );
			}
			else
			{
				return $host;
			}
		}
		else
		{
			return __( '(invalid IP address)', COUNTERIZE_PLUGIN_IP_TD );
		}
	}

	public function counterize_show_data_ip( $admin = false, $amount2 = 10, $directcall = true )
	{
		if( $directcall )
		{
			?>

			<p><?php _e( 'Need help about this page? Click on <strong>Help</strong> on the top of this page!', COUNTERIZE_PLUGIN_IP_TD ); ?></p>

			<?php
			if( ! counterize_getamount() )
			{
				_e( "There's no data in the database - You can't see stats until you have data. ", COUNTERIZE_PLUGIN_IP_TD );
				return;
			}
		}
		$enable_hostname_lookup = counterize_get_option( 'enable_hostname_lookup' );
		?>

		<!-- IPs -->
		<div class="wrap">

			<?php $this->counterize_render_most_active_ips( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_active_ips24hrs( $amount2 ); ?>

		</div>

		<?php if( $enable_hostname_lookup ): ?><div class="wrap">

			<?php $this->counterize_render_most_active_hosts( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_active_hosts24hrs( $amount2 ); ?>

		</div><?php endif; ?>

		<?php
	}







	// get the most active IP addresses data feed
	public function counterize_feed_most_active_ips( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$geoip = $wpdb->prepare( "%s", counterize_get_option( 'geoip' ) );
		$sql = "SELECT COUNT(`IP`) AS `count`, `IP` AS `label`, CONCAT( {$geoip}, `IP` ) AS `url` "
			. " FROM `" . counterize_logTable() . "`"
			. " WHERE `IP` <> 'unavailable' "
			. " GROUP BY `IP` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most active IP addresses', COUNTERIZE_PLUGIN_IP_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'IP', COUNTERIZE_PLUGIN_IP_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->label, $row->url );
		}
		unset( $rows, $sql, $geoip );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most active IP addresses data feed
	public function counterize_render_most_active_ips( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_active_ips( $number, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}










	// get the most active IP addresses of the last 24 hours data feed
	public function counterize_feed_most_active_ips24hrs( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$number = $wpdb->prepare( "%d", $number );
		$geoip = $wpdb->prepare( "%s", counterize_get_option( 'geoip' ) );
		$sql = "SELECT COUNT(`IP`) AS `count`, `IP` AS `label`, CONCAT( {$geoip}, `IP` ) AS `url` "
			. " FROM `" . counterize_logTable() . "`"
			. " WHERE `IP` <> 'unavailable' "
			. " AND `timestamp` >= '{$onedayago}'"
			. " GROUP BY `IP` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most active IP addresses for the last 24 hours', COUNTERIZE_PLUGIN_IP_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'IP', COUNTERIZE_PLUGIN_IP_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->label, $row->url );
		}
		unset( $rows, $sql, $onedayago, $geoip );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most active IP addresses of the last 24 hours data feed
	public function counterize_render_most_active_ips24hrs( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_active_ips24hrs( $number, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}










	// get the most active hosts data feed
	public function counterize_feed_most_active_hosts( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$geoip = $wpdb->prepare( "%s", counterize_get_option( 'geoip' ) );
		$sql = "SELECT COUNT(`IP`) AS `count`, `IP` AS `label`, CONCAT( {$geoip}, `IP` ) AS `url` "
			. " FROM `" . counterize_logTable() . "`"
			. " WHERE `IP` <> 'unavailable' "
			. " GROUP BY `IP` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most active hosts', COUNTERIZE_PLUGIN_IP_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Hosts', COUNTERIZE_PLUGIN_IP_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $this->gethost( $row->label ), $row->url );
		}
		unset( $rows, $sql, $geoip );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most active hosts data feed
	public function counterize_render_most_active_hosts( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_active_hosts( $number, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}







	// get the most active hosts of the last 24 hours data feed
	public function counterize_feed_most_active_hosts24hrs( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$number = $wpdb->prepare( "%d", $number );
		$geoip = $wpdb->prepare( "%s", counterize_get_option( 'geoip' ) );
		$sql = "SELECT COUNT(`IP`) AS `count`, `IP` AS `label`, CONCAT( {$geoip}, `IP` ) AS `url` "
			. " FROM `" . counterize_logTable() . "`"
			. " WHERE `IP` <> 'unavailable' "
			. " AND `timestamp` >= '{$onedayago}'"
			. " GROUP BY `IP` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most active hosts for the last 24 hours', COUNTERIZE_PLUGIN_IP_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Hosts', COUNTERIZE_PLUGIN_IP_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $this->gethost( $row->label ), $row->url );
		}
		unset( $rows, $sql, $onedayago, $geoip );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most active hosts of the last 24 hours data feed
	public function counterize_render_most_active_hosts24hrs( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_active_hosts24hrs( $number, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}






	public function counterize_check_data_ip( $data )
	{
		$enable_hostname_lookup = counterize_get_option( 'enable_hostname_lookup' );

		//<!-- counterize_stats_ip_nb --> : Shows a list of the nb most active ips
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_ip_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_active_ips' ), 1
		);

		//<!-- counterize_stats_ip_24hrs_nb --> : Shows a list of the nb most active ips during the last 24 hours
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_ip_24hrs_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_active_ips24hrs' ), 1
		);

		if( $enable_hostname_lookup )
		{
			//<!-- counterize_stats_ip_nb --> : Shows a list of the nb most active ips
			$data = counterize_check_data
			(
				$data, '/(\<\!\-|\#)\-\s*counterize_stats_hosts_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_active_hosts' ), 1
			);

			//<!-- counterize_stats_ip_24hrs_nb --> : Shows a list of the nb most active ips during the last 24 hours
			$data = counterize_check_data
			(
				$data, '/(\<\!\-|\#)\-\s*counterize_stats_hosts_24hrs_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_active_hosts24hrs' ), 1
			);
		}

		return $data;
	}

	public function counterize_plugin_ip_shortcodes_callback( $output, $attr, $content = null )
	{
		extract( shortcode_atts( array
		(
			'type'         => 'copyright',
			'items'        => 10,
			'subitems'     => 15,
			'version'      => 'yes',
			'collapsible'  => 'no',
			'print_header' => 'yes',
			'header'       => '',
			'period'       => '24h',
			'tn_width'     => 50,
			'tn_height'    => 50
		), $attr ) );

		ob_start();
		if( $type == 'ip' )
		{
			if( $period == '24h' )
			{
				$this->counterize_render_most_active_ips24hrs( $items, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_active_ips( $items, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'hosts' && counterize_get_option( 'enable_hostname_lookup' ) )
		{
			if( $period == '24h' )
			{
				$this->counterize_render_most_active_hosts24hrs( $items, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_active_hosts( $items, $print_header === 'yes', $header );
			}
		}

		$tmp = ob_get_contents();
		if( ! empty( $tmp ) )
		{
			$output = $tmp;
		}
		ob_end_clean();
		return $output;
	}

	public function counterize_mce_js_type_filter_callback( $content )
	{
		return $content . '										<option value=\"ip\">' . __( 'IP addresses', COUNTERIZE_PLUGIN_IP_TD ) . '</option>\
										<option value=\"hosts\">' . __( 'Hostnames', COUNTERIZE_PLUGIN_IP_TD ) . '</option>\
';
	}

	public function counterize_report_what_filter_callback( $what )
	{
		$what['ip'] = __( 'IP addresses', COUNTERIZE_PLUGIN_IP_TD );
		$what['hosts'] = __( 'Hostnames', COUNTERIZE_PLUGIN_IP_TD );
		return $what;
	}

	public function counterize_plugin_report_callback( $output, $what )
	{
		$items = counterize_get_option( 'amount2' );
		ob_start();
		if( in_array( 'ip', $what ) )
		{
			$this->counterize_render_most_active_ips( $items );
			$this->counterize_render_most_active_ips24hrs( $items );
		}
		if( in_array( 'hosts', $what ) )
		{
			$this->counterize_render_most_active_hosts( $items );
			$this->counterize_render_most_active_hosts24hrs( $items );
		}
		$tmp = ob_get_contents();
		if( ! empty( $tmp ) )
		{
			$output .= $tmp;
		}
		ob_end_clean();
		return $output;
	}

}

/*
 * Instanciate a new instance of Counterize_Plugin_IP
 */
$counterize_plugins['ip'] = new Counterize_Plugin_IP();

?>