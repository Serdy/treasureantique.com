<?php
/*
 Plugin Name: Counterize plugin: Traffic
 Plugin URI: http://www.gabsoftware.com/products/scripts/counterize/plugins/traffic
 Description: Display some information about the traffic for the Counterize plugin
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
add_filter( 'site_transient_update_plugins', 'counterize_plugin_traffic_disable_update_check' );

// Security check #2. We do not want to be able to load the plugin if Counterize isn't loaded before.
if( !defined( 'COUNTERIZE_CHECK') )
{
	add_action('admin_notices', 'counterize_plugin_traffic_admin_notice');
	return;
}

/* constants */

// Counterize Traffic plugin text domain
define( 'COUNTERIZE_PLUGIN_TRAFFIC_TD', COUNTERIZE_TD );

// Absolute path of the plugin from the server view
define( 'COUNTERIZE_PLUGIN_TRAFFIC_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__) ) );

// Public URL of the plugin
define( 'COUNTERIZE_PLUGIN_TRAFFIC_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__) ) );


/*
 * Prevents WordPress from displaying wrong update notifications because
 * this plugin is located in the same directory as Counterize
 */
function counterize_plugin_traffic_disable_update_check( $value = '' )
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
function counterize_plugin_traffic_admin_notice()
{
	global $pagenow;
	if( current_user_can( 'install_plugins' ) && $pagenow == 'plugins.php' )
	{
		echo '<div class="error"><p>';
		echo 'The Counterize plugin "Traffic" could not find Counterize. Please install and activate the <a target="_blank" href="plugin-install.php?tab=plugin-information&amp;plugin=counterize&amp;TB_iframe=false&amp;width=600&amp;height=550">Counterize plugin</a> first.';
		echo '</p></div>';
	}
}

class Counterize_Plugin_Traffic
{
	/*
	 * Our plugin constructor
	 */
	function __construct()
	{
		// Place your add_actions and add_filters here
		add_action( 'init', array( &$this, 'counterize_plugin_traffic_init_callback' ) );

	} // end of constructor

	/*
	 * Place your plugin initialization code here
	 * For example, load your plugin translations here, not before.
	 */
	public function counterize_plugin_traffic_init_callback()
	{
		//increment the plugin counter
		global $counterize_plugin_count;
		$counterize_plugin_count++;

		// Load the plugin localization (.mo files) located
		// in the plugin "lang" subdirectory
		if( function_exists( 'load_plugin_textdomain' ) )
		{
			load_plugin_textdomain(
				COUNTERIZE_PLUGIN_TRAFFIC_TD,
				COUNTERIZE_PLUGIN_TRAFFIC_DIR . '/lang',
				plugin_basename( dirname(__FILE__) ) . '/lang'
			);
		}

		//add a submenu page for our plugin
		add_filter( 'counterize_dashboard_add_submenu', array( &$this, 'counterize_dashboard_add_submenu_traffic_callback' ), 10, 2 );
		add_action( 'counterize_show_data', array( &$this, 'counterize_show_data_traffic' ), 10, 3 );
		add_filter( 'counterize_check_data', array( &$this, 'counterize_check_data_traffic' ), 10, 1 );

		//add a submenu in the Counterize toolbar
		add_action( 'counterize_toolbar_add_submenu', array( &$this, 'counterize_toolbar_add_submenu_callback' ), 10, 1 );

		//add shortcodes
		add_filter( 'counterize_shortcodes', array( &$this, 'counterize_plugin_traffic_shortcodes_callback' ), 10, 3 );

		//filters for the Counterize button in TinyMCE
		add_filter( 'counterize_mce_js_type_filter', array( &$this, 'counterize_mce_js_type_filter_callback' ), 10, 1 );
		add_filter( 'counterize_mce_js_period_filter', array( &$this, 'counterize_mce_js_period_filter_callback' ), 10, 1 );

		//filter for the report option in Counterize settings
		add_filter( 'counterize_report_what_filter', array( &$this, 'counterize_report_what_filter_callback' ), 10, 1 );

		//generate the report
		add_filter( 'counterize_report', array( &$this, 'counterize_plugin_report_callback' ), 10, 2 );

	} // end of function


	public function counterize_dashboard_add_submenu_traffic_callback( $counterize_dashboard_handles, $capability )
	{
		$counterize_dashboard_handles['traffic'] = add_submenu_page
		(
			COUNTERIZE_MENU_SLUG,
			__( 'Counterize', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . ' - ' . __( 'Traffic', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
			__( 'Traffic', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
			$capability,
			'counterize_dashboard_traffic',
			array( &$this, 'counterize_display_dashboard_traffic_page_callback' )
		);

		//load the help tabs
		add_action( 'load-' . $counterize_dashboard_handles['traffic'], array( &$this, 'counterize_add_help_tabs_to_traffic_callback' ) );

		//insert the stylesheet and script
		add_action( "admin_print_scripts-{$counterize_dashboard_handles['traffic']}", 'counterize_javascript_head_callback' );
		add_action( "admin_print_styles-{$counterize_dashboard_handles['traffic']}" , 'counterize_stylesheet_head_callback' );

		return $counterize_dashboard_handles;
	}

	public function counterize_toolbar_add_submenu_callback( $wp_admin_bar )
	{
		// add the All stats node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_traffic',
			'title'  => '<span class="ab-icon" style="margin-top: 3px; margin-right: 5px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/traffic_16x16.png" alt="Counterize icon" /></span>'
						. __( 'Traffic' ),
			'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard_traffic' ), // link to the related Counterize Dashboard entry
			'parent' => COUNTERIZE_TOOLBAR_STATS_GROUP_SLUG
		);
		$wp_admin_bar->add_node( $args );
	}

	/*
	 * display help tabs
	 */
	public function counterize_add_help_tabs_to_traffic_callback()
	{
		$screen = get_current_screen();
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-hits', // This should be unique for the screen.
				'title'   => __( 'Hit counter', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => '<p>' . __( 'Displays a table with hits, page views and unique visitors. Here is an explanation of what each line represents:', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</p>
					<dl>
						<dt>' . __( 'Hits', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'A hit is counted each time a ressource is fetched. The resource is not necessarily an existing page or post.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>

						<dt>' . __( 'Pages views', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'A page view is counted each time a hit concerns a valid page or post.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>

						<dt>' . __( 'Unique visitors', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'A unique visitor is counted only the first time its IP is recorded.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>

						<dt>' . __( 'Unique visitors ‪(1h interval)‬', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'A visit will be counted again each time two or more hits from the same visitor are separated of more than 1 hour.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>

						<dt>' . __( 'Unique visitors ‪(30 min interval)‬', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'A visit will be counted again each time two or more hits from the same visitor are separated of more than 30 minutes.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>

						<dt>' . __( 'Hits per unique visitor‬', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'The number of hits per unique visitor.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>

						<dt>' . __( 'Pages per unique visitor', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</dt>
						<dd>' . __( 'The number of pages/posts visited per unique visitor.', COUNTERIZE_PLUGIN_TRAFFIC_TD) . '</dd>
					</dl>'
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-monthday', // This should be unique for the screen.
				'title'   => __( 'Hits based on day of month', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of hits depending on the day of month.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-currentmonth', // This should be unique for the screen.
				'title'   => __( 'Hits based for the current month', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of hits depending on the day of month, for this month only.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-dayweek', // This should be unique for the screen.
				'title'   => __( 'Hits based on day of week', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of hits depending on the day of week.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-last7days', // This should be unique for the screen.
				'title'   => __( 'Hits for the last 7 days', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of hits for the last 7 days.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-progressionweek', // This should be unique for the screen.
				'title'   => __( 'Progression between last week and current week', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the progression between last week and the current week.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-hitsmonth', // This should be unique for the screen.
				'title'   => __( 'Hits based on month', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of hits per month.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-monthyear', // This should be unique for the screen.
				'title'   => __( 'Monthly hits for the current year', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of monthly hits for the current year.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-hourday', // This should be unique for the screen.
				'title'   => __( 'Hits based on hour of day', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the number of hits depending on the hour of day.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-traffic-plugin-help-hourlyhits24hr', // This should be unique for the screen.
				'title'   => __( 'Hourly hits for the last 24 hours', COUNTERIZE_PLUGIN_TRAFFIC_TD ),
				'content' => __( '<p>Displays a diagram of the hourly hits for the last 24 hours.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->set_help_sidebar
		(
			__( '<p>Choose a tab to learn more about the related diagram.</p>', COUNTERIZE_PLUGIN_TRAFFIC_TD )
		);
	}

	public function counterize_display_dashboard_traffic_page_callback()
	{
		// Amount to pass as option to the graphs...
		$amount2 = counterize_get_option( 'amount2' );
		if( $amount2 == '' || $amount2 == ' ' || ! is_numeric( $amount2 ) )
		{
			$amount2 = 0;
		}

		echo '
		<div id="icon-plugins" class="icon32"></div>
		<h1>' . __( 'Counterize', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . ' - ' . __( 'Traffic', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</h1>
		';

		$this->counterize_show_data_traffic( true, $amount2, true );

		//Print the footer
		counterize_pagefooter();
	}

	public function counterize_show_data_traffic( $admin = false, $amount2 = 10, $directcall = true )
	{
		if( $directcall )
				{
			?>

			<p><?php _e( 'Need help about this page? Click on <strong>Help</strong> on the top of this page!', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></p>

			<?php
			if( ! counterize_getamount() )
			{
				_e( "There's no data in the database - You can't see stats until you have data. ", COUNTERIZE_PLUGIN_TRAFFIC_TD );
				return;
			}
		}
		?>

	<!-- Traffic -->
	<div class="wrap">

		<h2><?php _e( 'Hit Counter', COUNTERIZE_PLUGIN_TRAFFIC_TD );?></h2>

		<?php $this->counterize_get_hits(); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_daily_stats( false ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_daily_stats( true ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_weekly_stats(); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_weekly_stats( true ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_week_progression_stats( true ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_monthly_stats(); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_monthly_stats( true ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_hourly_stats(); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_hourly_stats( true ); ?>

	</div>

		<?php
	}








	/*
	 * Display a table containing useful traffic information
	 */
	public function counterize_get_hits()
	{
		$sincetoday = time() - strtotime( 'today' );
		?>

		<table id="counterizehitcountertable" width="100%" cellpadding="3" cellspacing="3" summary="<?php _e( 'Traffic statistics', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>">
			<tr>
				<td scope="col" style="width: 17%" align="center">&nbsp;</td>
				<td scope="col" style="width: 16%" align="center">
					<strong><?php _e( 'Today', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong>
				</td>
				<td scope="col" style="width: 17%" align="center">
					<strong><?php _e( 'Last 24 hours', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong>
				</td>
				<td scope="col" style="width: 17%" align="center">
					<strong><?php _e( 'Last 7 days', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong>
				</td>
				<td scope="col" style="width: 17%" align="center">
					<strong><?php _e( 'Last 30 days', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong>
				</td>
				<td scope="col" style="width: 16%" align="center">
					<strong><?php _e( 'Total', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong>
				</td>
			</tr>
			<tr class="alternate">
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'A hit is counted each time a ressource is fetched. The resource is not necessarily an existing page or post.', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Hits', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php echo counterize_getamount( false, $sincetoday ); ?></td>
				<td align="center"><?php echo counterize_getamount( false,       86400 ); ?></td>
				<td align="center"><?php echo counterize_getamount( false,      604800 ); ?></td>
				<td align="center"><?php echo counterize_getamount( false,     2592000 ); ?></td>
				<td align="center"><?php echo counterize_getamount(                    ); ?></td>
			</tr>
			<tr>
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'A page view is counted each time a hit concerns a valid page or post', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Pages views', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php echo counterize_getpagesamount( $sincetoday ); ?></td>
				<td align="center"><?php echo counterize_getpagesamount(       86400 ); ?></td>
				<td align="center"><?php echo counterize_getpagesamount(      604800 ); ?></td>
				<td align="center"><?php echo counterize_getpagesamount(     2592000 ); ?></td>
				<td align="center"><?php echo counterize_getpagesamount(             ); ?></td>
			</tr>
			<tr class="alternate">
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'A unique visitor is counted only the first time its IP is recorded.', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Unique visitors', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php echo counterize_getuniqueamount( $sincetoday ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(       86400 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(      604800 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(     2592000 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(             ); ?></td>
			</tr>
			<tr>
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'A visit will be counted again each time two or more hits from the same visitor are separated of more than 1 hour.', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Unique visitors &#x202A;(1h interval)&#x202C;', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php echo counterize_getuniqueamount( $sincetoday, 3600 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(       86400, 3600 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(      604800, 3600 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(     2592000, 3600 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(           0, 3600 ); ?></td>
			</tr>
			<tr class="alternate">
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'A visit will be counted again each time two or more hits from the same visitor are separated of more than 30 minutes.', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Unique visitors &#x202A;(30&nbsp;min interval)&#x202C;', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php echo counterize_getuniqueamount( $sincetoday, 1800 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(       86400, 1800 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(      604800, 1800 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(     2592000, 1800 ); ?></td>
				<td align="center"><?php echo counterize_getuniqueamount(           0, 1800 ); ?></td>
			</tr>
			<tr>
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'The number of hits per unique visitor.', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Hits per unique visitor', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php $res = counterize_getuniqueamount( $sincetoday ); echo ( $res > 0 ) ? ( round( counterize_getamount( false, $sincetoday ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(       86400 ); echo ( $res > 0 ) ? ( round( counterize_getamount( false,       86400 ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(      604800 ); echo ( $res > 0 ) ? ( round( counterize_getamount( false,      604800 ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(     2592000 ); echo ( $res > 0 ) ? ( round( counterize_getamount( false,     2592000 ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(             ); echo ( $res > 0 ) ? ( round( counterize_getamount(                    ) / $res, 2 ) ) : 0; ?></td>
			</tr>
			<tr class="alternate">
				<td align="center" class="counterize_caption_help"><strong title="<?php _e( 'The number of pages/posts visited per unique visitor.', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?>"><?php _e( 'Pages per unique visitor', COUNTERIZE_PLUGIN_TRAFFIC_TD ); ?></strong></td>
				<td align="center"><?php $res = counterize_getuniqueamount( $sincetoday ); echo ( $res > 0 ) ? ( round( counterize_getpagesamount( $sincetoday ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(       86400 ); echo ( $res > 0 ) ? ( round( counterize_getpagesamount(       86400 ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(      604800 ); echo ( $res > 0 ) ? ( round( counterize_getpagesamount(      604800 ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(     2592000 ); echo ( $res > 0 ) ? ( round( counterize_getpagesamount(     2592000 ) / $res, 2 ) ) : 0; ?></td>
				<td align="center"><?php $res = counterize_getuniqueamount(             ); echo ( $res > 0 ) ? ( round( counterize_getpagesamount(             ) / $res, 2 ) ) : 0; ?></td>
			</tr>
		</table>

		<?php
	}







	// get the daily stats data feed
	public function counterize_feed_daily_stats( $only_this_month = false, $header_override = '' )
	{
		global $wpdb;
		$sql = "SELECT "
			. " 	DAYOFMONTH( `timestamp` ) AS label, "
			. " 	COUNT( 1 ) AS `count` "
			. " FROM `" . counterize_logTable() . "`";
		if( $only_this_month )
		{
			$sql .= " WHERE `timestamp` >= '" . date( 'Y-m-01' ) . "'";
		}
		$sql .= " GROUP BY `label`";

		$rows = $wpdb->get_results( $sql );

		if( $only_this_month )
		{
			$title = __( 'Hits for the current month', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Day', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
		}
		else
		{
			$title = __( 'Hits based on day of month', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Day of month', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
		}

		foreach( $rows as $row )
		{
			$feed->add_item_2( $row->count, $row->label );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the daily stats data feed
	public function counterize_render_daily_stats( $only_this_month = false, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_daily_stats( $only_this_month, $header_override );

		if( is_admin() )
		{
			$feed->render_feed_horizontal( 80, '100%', $print_header );
		}
		else
		{
			$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
		}
	}










	// get the weekly stats data feed
	public function counterize_feed_weekly_stats( $only_this_week = false, $header_override = '' )
	{
		global $wpdb;
		$sevendaysago = date( 'Y-m-d', time() - 604800 ); //604800 = 86400 * 7
		if( ! $only_this_week )
		{
			$sql = "SELECT "
				. " 	DAYNAME( `timestamp` ) AS `label`, "
				. " 	COUNT( 1 ) AS `count`, "
				. " 	DAYOFWEEK( `timestamp` ) AS `day` "
				. " FROM `" . counterize_logTable() . "` "
				. " GROUP BY `label` "
				. " ORDER BY `day` ASC";
		}
		else
		{
			$sql = "SELECT "
				. " 	DATE_FORMAT( `timestamp`, '%b %d' ) AS `label`, "
				. " 	DATE_FORMAT( `timestamp`, '%b' ) AS `month`, "
				. " 	DATE_FORMAT( `timestamp`, '%d' ) AS `day`, "
				. " 	COUNT( 1 ) AS `count`"
				. " FROM `" . counterize_logTable() . "` "
				. " WHERE `timestamp` >= '{$sevendaysago}' "
				. " GROUP BY `label` "
				. " ORDER BY `label` ASC";
		}

		$rows = $wpdb->get_results( $sql );

		if( $only_this_week )
		{
			$title = __( 'Hits for the last 7 days', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Date', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
			foreach( $rows as $row )
			{
				$feed->add_item_2( $row->count, sprintf( __( $row->month . ' %d', COUNTERIZE_PLUGIN_TRAFFIC_TD ), $row->day ) );
			}
		}
		else
		{
			$title = __( 'Hits based on day of week', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Day of week', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
			foreach( $rows as $row )
			{
				$feed->add_item_2( $row->count, __( $row->label, COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
			}
		}

		unset( $rows, $sql );

		$feed->refresh_percentages();

		return $feed;
	}

	// render the  weekly stats data feed
	public function counterize_render_weekly_stats( $only_this_week = false, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_weekly_stats( $only_this_week, $header_override );

		if( is_admin() )
		{
			$feed->render_feed_horizontal( 80, '100%', $print_header );
		}
		else
		{
			$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
		}
	}










	// get the weekly stats data feed
	public function counterize_feed_week_progression_stats( $header_override = '' )
	{
		global $wpdb;

		$sql = "SELECT ";
		for( $i = 7; $i > 0; $i-- )
		{
			$end7   = date( 'Y-m-d', strtotime(  $i - 1 . ' days ago' ) );
			$start7 = date( 'Y-m-d',     strtotime(  $i     . ' days ago' ) );

			$end14   = date( 'Y-m-d', strtotime( $i + 6 . ' days ago' ) );
			$start14 = date( 'Y-m-d',     strtotime( $i + 7 . ' days ago' ) );

			$sql .= "
			(
				SELECT DAYNAME( '{$start7}' )
			) AS `label_{$i}`,
			(
				SELECT (
					SELECT COUNT( `id` )
					FROM `" . counterize_logTable() . "`
					WHERE `timestamp` BETWEEN '{$start7}' AND '{$end7}'
				) / (
					SELECT COUNT( `id` )
					FROM `" . counterize_logTable() . "`
					WHERE `timestamp` BETWEEN '{$start14}' AND '{$end14}'
				)
			) AS `count_{$i}`";
			if( $i > 1 )
			{
				$sql .= ", ";
			}
			unset( $end7, $start7, $end14, $start14 );
		}

		$row = $wpdb->get_row( $sql, ARRAY_A );
		unset( $sql );

		//echo $sql;
		//print_r( $row );

		$title = __( 'Progression between last week and current week', COUNTERIZE_PLUGIN_TRAFFIC_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Day of week', COUNTERIZE_PLUGIN_TRAFFIC_TD ), '%' );

		for( $i = 7; $i > 0; $i-- )
		{
			$feed->add_item_2(  is_null( $row["count_{$i}"] ) ? 0 : ( $row["count_{$i}"] - 1 ) * 100, $row["label_{$i}"] );
		}
		unset( $row );

		$feed->refresh_percentages();

		return $feed;
	}

	// render the  weekly stats data feed
	public function counterize_render_week_progression_stats( $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_week_progression_stats( $header_override );

		if( is_admin() )
		{
			$feed->render_feed_horizontal( 80, '100%', $print_header, false );
		}
		/*else
		{
			$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
		}*/
	}










		// get the monthly stats data feed
	public function counterize_feed_monthly_stats( $only_this_year = false, $header_override = '' )
	{
		global $wpdb;
		$sql = "SELECT "
			. " 	CONCAT( CONCAT( SUBSTRING( MONTHNAME( `timestamp` ), 1, 3 ), ' ' ), SUBSTRING( YEAR( `timestamp` ), 3, 2 ) ) AS `label`, "
			. " 	COUNT( 1 ) AS `count`, "
			. " 	MONTH( `timestamp` ) AS `m`, "
			. " 	YEAR( `timestamp` ) AS `y` "
			. " FROM `" . counterize_logTable() . "`";
		if( $only_this_year )
		{
			$sql .= " WHERE `timestamp` >= '" . date( 'Y-01-01' ) . "'";
		}
		$sql .= " GROUP BY `label` "
			. " ORDER BY `y`, `m`";

		$rows = $wpdb->get_results( $sql );

		if( $only_this_year )
		{
			$title = __( 'Monthly hits for the current year', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Month', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
		}
		else
		{
			$title = __( 'Hits based on month', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Month', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
		}

		foreach( $rows as $row )
		{
			$feed->add_item_2( $row->count, $row->label );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the monthly stats data feed
	public function counterize_render_monthly_stats( $only_this_year = false, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_monthly_stats( $only_this_year, $header_override );

		if( is_admin() )
		{
			$feed->render_feed_horizontal( 80, '100%', $print_header );
		}
		else
		{
			$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
		}
	}












	// get the hourly stats data feed
	public function counterize_feed_hourly_stats( $only_today = false, $header_override = '' )
	{
		global $wpdb;
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$sql = "SELECT "
			. " 	HOUR( `timestamp` ) AS `label`, "
			. " 	COUNT( 1 ) AS `count` "
			. " FROM `" . counterize_logTable() . "` ";
		if( $only_today )
		{
			$sql .= " WHERE `timestamp` >= '{$onedayago}'";
		}
		$sql .= " GROUP BY `label` ";

		$rows = $wpdb->get_results( $sql );

		if( $only_today )
		{
			$title = __( 'Hourly hits for the last 24 hours', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Hour', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
		}
		else
		{
			$title = __( 'Hits based on hour of day', COUNTERIZE_PLUGIN_TRAFFIC_TD );
			if( ! empty( $header_override ) )
			{
				$title = $header_override;
			}
			$feed = new CounterizeFeed( $title, __( 'Hour', COUNTERIZE_PLUGIN_TRAFFIC_TD ) );
		}

		foreach( $rows as $row )
		{
			$feed->add_item_2( $row->count, $row->label );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the  hourly stats data feed
	public function counterize_render_hourly_stats( $only_today = false, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_hourly_stats( $only_today, $header_override );

		if( is_admin() )
		{
			$feed->render_feed_horizontal( 80, '100%', $print_header );
		}
		else
		{
			$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
		}
	}











	public function counterize_check_data_traffic( $data )
	{

		//<!-- counterize_stats_hits --> : Shows a table containing statistics about hits
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_hits\s*\-(\-\>|\#)/', array( &$this, 'counterize_get_hits' ), 0
		);

		return $data;
	}

	public function counterize_plugin_traffic_shortcodes_callback( $output, $attr, $content = null )
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
		if( $type == 'hits' )
		{
			$this->counterize_get_hits();
		}
		elseif( $type == 'hourly' )
		{
			if( $period == 'onlytoday' )
			{
				$this->counterize_render_hourly_stats( TRUE, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_hourly_stats( FALSE, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'daily' )
		{
			if( $period == 'onlythismonth' )
			{
				$this->counterize_render_daily_stats( TRUE, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_daily_stats( FALSE, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'weekly' )
		{
			if( $period == 'onlythisweek' )
			{
				$this->counterize_render_weekly_stats( TRUE, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_weekly_stats( FALSE, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'monthly' )
		{
			if( $period == 'onlythisyear' )
			{
				$this->counterize_render_monthly_stats( TRUE, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_monthly_stats( FALSE, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'totalhits' )
		{
			echo counterize_getamount();
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
		return $content . '										<optgroup label=\"' . __( 'Traffic', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '\">\
											<option value=\"hits\">' . __( 'Hits', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</option>\
											<option value=\"hourly\">' . __( 'Hourly stats', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</option>\
											<option value=\"daily\">' . __( 'Daily stats', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</option>\
											<option value=\"weekly\">' . __( 'Weekly stats', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</option>\
											<option value=\"monthly\">' . __( 'Monthly stats', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</option>\
											<option value=\"totalhits\">' . __( 'Total hits', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</option>\
										</optgroup>\
';
	}

	public function counterize_mce_js_period_filter_callback( $content )
	{
		return $content . '									<input type=\"radio\" name=\"period\" id=\"counterize-period-onlytoday\"     value=\"onlytoday\" /><label for=\"counterize-period-onlytoday\">' . __( 'Only today', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</label><br />\
									<input type=\"radio\" name=\"period\" id=\"counterize-period-onlythisweek\"  value=\"onlythisweek\" /><label for=\"counterize-period-onlythisweek\">' . __( 'Only this week', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</label><br />\
									<input type=\"radio\" name=\"period\" id=\"counterize-period-onlythismonth\" value=\"onlythismonth\" /><label for=\"counterize-period-onlythismonth\">' . __( 'Only this month', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</label><br />\
									<input type=\"radio\" name=\"period\" id=\"counterize-period-onlythisyear\"  value=\"onlythisyear\" /><label for=\"counterize-period-onlythisyear\">' . __( 'Only this year', COUNTERIZE_PLUGIN_TRAFFIC_TD ) . '</label><br />\
';
	}

	public function counterize_report_what_filter_callback( $what )
	{
		$what['hits'] = __( 'Hits', COUNTERIZE_PLUGIN_TRAFFIC_TD );
		$what['hourly'] = __( 'Hourly stats', COUNTERIZE_PLUGIN_TRAFFIC_TD );
		$what['daily'] = __( 'Daily stats', COUNTERIZE_PLUGIN_TRAFFIC_TD );
		$what['weekly'] = __( 'Weekly stats', COUNTERIZE_PLUGIN_TRAFFIC_TD );
		$what['monthly'] = __( 'Monthly stats', COUNTERIZE_PLUGIN_TRAFFIC_TD );
		return $what;
	}

	public function counterize_plugin_report_callback( $output, $what )
	{
		ob_start();
		if( in_array( 'hits', $what ) )
		{
			$this->counterize_get_hits();
		}
		if( in_array( 'hourly', $what ) )
		{
			$this->counterize_render_hourly_stats( FALSE );
			$this->counterize_render_hourly_stats( TRUE );
		}
		if( in_array( 'daily', $what ) )
		{
			$this->counterize_render_daily_stats( FALSE );
			$this->counterize_render_daily_stats( TRUE );
		}
		if( in_array( 'weekly', $what ) )
		{
			$this->counterize_render_weekly_stats( FALSE );
			$this->counterize_render_weekly_stats( TRUE );
		}
		if( in_array( 'monthly', $what ) )
		{
			$this->counterize_render_monthly_stats( FALSE );
			$this->counterize_render_monthly_stats( TRUE );
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
 * Instanciate a new instance of Counterize_Plugin_Traffic
 */
$counterize_plugins['traffic'] = new Counterize_Plugin_Traffic();

?>