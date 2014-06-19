<?php
/*
 Plugin Name: Counterize plugin: Referers
 Plugin URI: http://www.gabsoftware.com/products/scripts/counterize/plugins/referers
 Description: Display some information about the referers for the Counterize plugin
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
add_filter( 'site_transient_update_plugins', 'counterize_plugin_referers_disable_update_check' );

// Security check #2. We do not want to be able to load the plugin if Counterize isn't loaded before.
if( !defined( 'COUNTERIZE_CHECK') )
{
	add_action('admin_notices', 'counterize_plugin_referers_admin_notice');
	return;
}

/* constants */

// Counterize Referers plugin text domain
define( 'COUNTERIZE_PLUGIN_REFERERS_TD', COUNTERIZE_TD );

// Absolute path of the plugin from the server view
define( 'COUNTERIZE_PLUGIN_REFERERS_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__) ) );

// Public URL of the plugin
define( 'COUNTERIZE_PLUGIN_REFERERS_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__) ) );


/*
 * Prevents WordPress from displaying wrong update notifications because
 * this plugin is located in the same directory as Counterize
 */
function counterize_plugin_referers_disable_update_check( $value = '' )
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
function counterize_plugin_referers_admin_notice()
{
	global $pagenow;
	if( current_user_can( 'install_plugins' ) && $pagenow == 'plugins.php' )
	{
		echo '<div class="error"><p>';
		echo 'The Counterize plugin "Referers" could not find Counterize. Please install and activate the <a target="_blank" href="plugin-install.php?tab=plugin-information&amp;plugin=counterize&amp;TB_iframe=false&amp;width=600&amp;height=550">Counterize plugin</a> first.';
		echo '</p></div>';
	}
}

class Counterize_Plugin_Referers
{
	/*
	 * Our plugin constructor
	 */
	function __construct()
	{
		// Place your add_actions and add_filters here
		add_action( 'init', array( &$this, 'counterize_plugin_referers_init_callback' ) );

	} // end of constructor

	/*
	 * Place your plugin initialization code here
	 * For example, load your plugin translations here, not before.
	 */
	public function counterize_plugin_referers_init_callback()
	{
		//increment the plugin counter
		global $counterize_plugin_count;
		$counterize_plugin_count++;

		// Load the plugin localization (.mo files) located
		// in the plugin "lang" subdirectory
		if( function_exists( 'load_plugin_textdomain' ) )
		{
			load_plugin_textdomain(
				COUNTERIZE_PLUGIN_REFERERS_TD,
				COUNTERIZE_PLUGIN_REFERERS_DIR . '/lang',
				plugin_basename( dirname(__FILE__) ) . '/lang'
			);
		}

		//add a submenu page for our plugin
		add_filter( 'counterize_dashboard_add_submenu', array( &$this, 'counterize_dashboard_add_submenu_referers_callback' ), 10, 2 );
		add_action( 'counterize_show_data', array( &$this, 'counterize_show_data_referers' ), 10, 3 );
		add_filter( 'counterize_check_data', array( &$this, 'counterize_check_data_referers' ), 10, 1 );

		//add a submenu in the Counterize toolbar
		add_action( 'counterize_toolbar_add_submenu', array( &$this, 'counterize_toolbar_add_submenu_callback' ), 10, 1 );

		//add shortcodes
		add_filter( 'counterize_shortcodes', array( &$this, 'counterize_plugin_referers_shortcodes_callback' ), 10, 3 );

		//filters for the Counterize button in TinyMCE
		add_filter( 'counterize_mce_js_type_filter', array( &$this, 'counterize_mce_js_type_filter_callback' ), 10, 1 );

		//filter for the report option in Counterize settings
		add_filter( 'counterize_report_what_filter', array( &$this, 'counterize_report_what_filter_callback' ), 10, 1 );

		//generate the report
		add_filter( 'counterize_report', array( &$this, 'counterize_plugin_report_callback' ), 10, 2 );

	} // end of function


	public function counterize_dashboard_add_submenu_referers_callback( $counterize_dashboard_handles, $capability )
	{
		$counterize_dashboard_handles['referers'] = add_submenu_page
		(
			COUNTERIZE_MENU_SLUG,
			__( 'Counterize', COUNTERIZE_PLUGIN_REFERERS_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_REFERERS_TD ) . ' - ' . __( 'Referers', COUNTERIZE_PLUGIN_REFERERS_TD ),
			__( 'Referers', COUNTERIZE_PLUGIN_REFERERS_TD ),
			$capability,
			'counterize_dashboard_referers',
			array( &$this, 'counterize_display_dashboard_referers_page_callback' )
		);

		//load the help tabs
		add_action( 'load-' . $counterize_dashboard_handles['referers'], array( &$this, 'counterize_add_help_tabs_to_referers_callback' ) );

		//insert the stylesheet and script
		add_action( "admin_print_scripts-{$counterize_dashboard_handles['referers']}", 'counterize_javascript_head_callback' );
		add_action( "admin_print_styles-{$counterize_dashboard_handles['referers']}" , 'counterize_stylesheet_head_callback' );

		return $counterize_dashboard_handles;
	}

	public function counterize_toolbar_add_submenu_callback( $wp_admin_bar )
	{
		// add the All stats node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_referers',
			'title'  => '<span class="ab-icon" style="margin-top: 3px; margin-right: 5px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/referers_16x16.png" alt="Counterize icon" /></span>'
						. __( 'Referers' ),
			'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard_referers' ), // link to the related Counterize Dashboard entry
			'parent' => COUNTERIZE_TOOLBAR_STATS_GROUP_SLUG
		);
		$wp_admin_bar->add_node( $args );
	}

	/*
	 * display help tabs
	 */
	public function counterize_add_help_tabs_to_referers_callback()
	{
		$screen = get_current_screen();
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-referers-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most seen referers', COUNTERIZE_PLUGIN_REFERERS_TD ),
				'content' => __( '<p>Displays a diagram of the referers.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
						. __( '<p>A referer is a URL from where a visitor comes from. By such, it means that there is a link to your website on the referer website.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
						. __( '<p>This is useful to find where your website is the most linked.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-referers24hr-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most seen referers for the last 24 hours', COUNTERIZE_PLUGIN_REFERERS_TD ),
				'content' => __( '<p>Displays a diagram of the referers for the last 24 hours only.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
						. __( '<p>A referer is a URL from where a visitor comes from. By such, it means that there is a link to your website on the referer website.</p>', COUNTERIZE_PLUGIN_KEYWORDS_TD )
						. __( '<p>This is useful to find where your website is the most linked.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-domains-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most seen refering domains', COUNTERIZE_PLUGIN_REFERERS_TD ),
				'content' => __( '<p>Displays a diagram of the refering domains.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
						. __( '<p>A referer is a URL from where a visitor comes from. By such, it means that there is a link to your website on the referer website.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
						. __( '<p>This is useful to find where your website is the most linked.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-domains24hr-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most seen refering domains for the last 24 hours', COUNTERIZE_PLUGIN_REFERERS_TD ),
				'content' => __( '<p>Displays a diagram of the refering domains for the last 24 hours only.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
						. __( '<p>A referer is a URL from where a visitor comes from. By such, it means that there is a link to your website on the referer website.</p>', COUNTERIZE_PLUGIN_KEYWORDS_TD )
						. __( '<p>This is useful to find where your website is the most linked.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->set_help_sidebar
		(
			__( '<p>Choose a tab to learn more about the related diagram.</p>', COUNTERIZE_PLUGIN_REFERERS_TD )
		);
	}

	public function counterize_display_dashboard_referers_page_callback()
	{
		// Amount to pass as option to the graphs...
		$amount2 = counterize_get_option( 'amount2' );
		if( $amount2 == '' || $amount2 == ' ' || ! is_numeric( $amount2 ) )
		{
			$amount2 = 10;
		}

		echo '
		<div id="icon-plugins" class="icon32"></div>
		<h1>' . __( 'Counterize', COUNTERIZE_PLUGIN_REFERERS_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_REFERERS_TD ) . ' - ' . __( 'Referers', COUNTERIZE_PLUGIN_REFERERS_TD ) . '</h1>
		';

		$this->counterize_show_data_referers( true, $amount2, true );

		//Print the footer
		counterize_pagefooter();
	}

	public function counterize_show_data_referers( $admin = false, $amount2 = 10, $directcall = true )
	{
		if( $directcall )
		{
			?>

			<p><?php _e( 'Need help about this page? Click on <strong>Help</strong> on the top of this page!', COUNTERIZE_PLUGIN_REFERERS_TD ); ?></p>

			<?php
			if( ! counterize_getamount() )
			{
				_e( "There's no data in the database - You can't see stats until you have data. ", COUNTERIZE_PLUGIN_REFERERS_TD );
				return;
			}
		}
		?>

		<!-- Referers -->
		<div class="wrap">

			<?php $this->counterize_render_most_seen_referers( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_seen_referers24hrs( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_seen_referers_domains( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_seen_referers_domains24hrs( $amount2 ); ?>

		</div>

		<?php
	}







	// get the most seen referers data feed
	public function counterize_feed_most_seen_referers( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$sql = "SELECT `count`, `name` AS label, `name` AS url "
			. " FROM `" . counterize_refererTable() . "`"
			. " WHERE `name` <> 'unknown' "
			. " AND `name` NOT LIKE " . $wpdb->prepare( "%s", get_option( 'home' ) . "%%" ) . " "
			. " AND `name` NOT LIKE " . $wpdb->prepare( "%s", get_option( 'siteurl' ) . "%%" ) . " "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most seen referers', COUNTERIZE_PLUGIN_REFERERS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'URL', COUNTERIZE_PLUGIN_REFERERS_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->label, $row->url );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most seen referers data feed
	public function counterize_render_most_seen_referers( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_seen_referers( $number, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}










	// get the most seen referers data feed for the last 24 hours
	public function counterize_feed_most_seen_referers24hrs( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$sql = "SELECT COUNT( m.id ) AS `count`, r.name AS label, r.name AS url "
			. " FROM `" . counterize_logTable() . "` m, `" . counterize_refererTable() . "` r "
			. " WHERE m.`refererID` = r.`refererID` "
			. " AND r.`name` <> 'unknown' "
			. " AND r.`name` NOT LIKE " . $wpdb->prepare( "%s", get_option( "home" ) . "%%" ) . " "
			. " AND r.`name` NOT LIKE " . $wpdb->prepare( "%s", get_option( "siteurl" ) . "%%" ) . " "
			. " AND m.`timestamp` >= '{$onedayago}'"
			. " GROUP BY r.`name` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most seen referers for the last 24 hours', COUNTERIZE_PLUGIN_REFERERS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'URL', COUNTERIZE_PLUGIN_REFERERS_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->label, $row->url );
		}
		unset( $rows, $sql, $onedayago );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most seen referers data feed for the last 24 hours
	public function counterize_render_most_seen_referers24hrs( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_seen_referers24hrs( $number, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}










	// get the most seen referers domains data feed
	public function counterize_feed_most_seen_referers_domains( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$sql = 'SELECT SUM( `count` ) AS `count`, SUBSTRING_INDEX( SUBSTRING_INDEX( TRIM( LEADING "https://" FROM TRIM( LEADING "http://" FROM TRIM( `name` ) ) ), "/", 1 ), ":", 1 ) AS `domain` '
			. " FROM `" . counterize_refererTable() . "`"
			. " WHERE `name` <> 'unknown' "
			. " AND `name` NOT LIKE " . $wpdb->prepare( "%s", get_option( 'home' ) . "%%" ) . " "
			. " AND `name` NOT LIKE " . $wpdb->prepare( "%s", get_option( 'siteurl' ) . "%%" ) . " "
			. " GROUP BY `domain` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most seen refering domains', COUNTERIZE_PLUGIN_REFERERS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Domains', COUNTERIZE_PLUGIN_REFERERS_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->domain, "http://{$row->domain}" );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most seen referers domains data feed
	public function counterize_render_most_seen_referers_domains( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_seen_referers_domains( $number, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}










	// get the most seen referers domains data feed for the last 24 hours
	public function counterize_feed_most_seen_referers_domains24hrs( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$sql = 'SELECT COUNT( m.id ) AS `count`, SUBSTRING_INDEX( SUBSTRING_INDEX( TRIM( LEADING "https://" FROM TRIM( LEADING "http://" FROM TRIM( r.`name` ) ) ), "/", 1 ), ":", 1 ) AS `domain` '
			. " FROM `" . counterize_logTable() . "` m, `" . counterize_refererTable() . "` r "
			. " WHERE m.`refererID` = r.`refererID` "
			. " AND r.`name` <> 'unknown' "
			. " AND r.`name` NOT LIKE " . $wpdb->prepare( "%s", get_option( "home" ) . "%%" ) . " "
			. " AND r.`name` NOT LIKE " . $wpdb->prepare( "%s", get_option( "siteurl" ) . "%%" ) . " "
			. " AND m.`timestamp` >= '{$onedayago}'"
			. " GROUP BY `domain` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most seen refering domains for the last 24 hours', COUNTERIZE_PLUGIN_REFERERS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Domains', COUNTERIZE_PLUGIN_REFERERS_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->domain, "http://{$row->domain}" );
		}
		unset( $rows, $sql, $onedayago );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most seen referers domains data feed for the last 24 hours
	public function counterize_render_most_seen_referers_domains24hrs( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_seen_referers_domains24hrs( $number, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}






	public function counterize_check_data_referers( $data )
	{

		//<!-- counterize_stats_referers_nb --> : Shows a list of the nb most seen referers
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_referers_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_seen_referers' ), 1
		);

		//<!-- counterize_stats_referers_24hrs_nb --> : Shows a list of the nb most seen referers during the last 24 hours
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_referers_24hrs_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_seen_referers24hrs' ), 1
		);

		//<!-- counterize_stats_domains_nb --> : Shows a list of the nb most seen refering domains
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_domains_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_seen_referers_domains' ), 1
		);

		//<!-- counterize_stats_domains_24hrs_nb --> : Shows a list of the nb most seen refering domains during the last 24 hours
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_domains_24hrs_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_seen_referers_domains24hrs' ), 1
		);


		return $data;
	}

	public function counterize_plugin_referers_shortcodes_callback( $output, $attr, $content = null )
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
		if( $type == 'referers' )
		{
			if( $period == '24h' )
			{
				$this->counterize_render_most_seen_referers24hrs( $items, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_seen_referers( $items, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'domains' )
		{
			if( $period == '24h' )
			{
				$this->counterize_render_most_seen_referers_domains24hrs( $items, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_seen_referers_domains( $items, $print_header === 'yes', $header );
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
		return $content . '										<option value=\"referers\">' . __( 'Referers', COUNTERIZE_PLUGIN_REFERERS_TD ) . '</option>\
										<option value=\"domains\">' . __( 'Refering domains', COUNTERIZE_PLUGIN_REFERERS_TD ) . '</option>\
';
	}

	public function counterize_report_what_filter_callback( $what )
	{
		$what['referers'] = __( 'Referers', COUNTERIZE_PLUGIN_REFERERS_TD );
		$what['domains'] = __( 'Refering domains', COUNTERIZE_PLUGIN_REFERERS_TD );
		return $what;
	}

	public function counterize_plugin_report_callback( $output, $what )
	{
		$items = counterize_get_option( 'amount2' );
		ob_start();
		if( in_array( 'referers', $what ) )
		{
			$this->counterize_render_most_seen_referers( $items );
			$this->counterize_render_most_seen_referers24hrs( $items );
		}
		if( in_array( 'domains', $what ) )
		{
			$this->counterize_render_most_seen_referers_domains( $items );
			$this->counterize_render_most_seen_referers_domains24hrs( $items );
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
 * Instanciate a new instance of Counterize_Plugin_Referers
 */
$counterize_plugins['referers'] = new Counterize_Plugin_Referers();

?>