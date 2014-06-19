<?php
/*
 Plugin Name: Counterize plugin: Operating Systems
 Plugin URI: http://www.gabsoftware.com/products/scripts/counterize/plugins/os
 Description: Display some information about the operating systems used by your visitors, for the Counterize plugin
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
add_filter( 'site_transient_update_plugins', 'counterize_plugin_os_disable_update_check' );

// Security check #2. We do not want to be able to load the plugin if Counterize isn't loaded before.
if( !defined( 'COUNTERIZE_CHECK') )
{
	add_action('admin_notices', 'counterize_plugin_os_admin_notice');
	return;
}

/* constants */

// Counterize OS plugin text domain
define( 'COUNTERIZE_PLUGIN_OS_TD', COUNTERIZE_TD );

// Absolute path of the plugin from the server view
define( 'COUNTERIZE_PLUGIN_OS_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__) ) );

// Public URL of the plugin
define( 'COUNTERIZE_PLUGIN_OS_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__) ) );

/*
 * Prevents WordPress from displaying wrong update notifications because
 * this plugin is located in the same directory as Counterize
 */
function counterize_plugin_os_disable_update_check( $value = '' )
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
function counterize_plugin_os_admin_notice()
{
	global $pagenow;
	if( current_user_can( 'install_plugins' ) && $pagenow == 'plugins.php' )
	{
		echo '<div class="error"><p>';
		echo 'The Counterize plugin "Operating Systems" could not find Counterize. Please install and activate the <a target="_blank" href="plugin-install.php?tab=plugin-information&amp;plugin=counterize&amp;TB_iframe=false&amp;width=600&amp;height=550">Counterize plugin</a> first.';
		echo '</p></div>';
	}
}

class Counterize_Plugin_OS
{
	/*
	 * Our plugin constructor
	 */
	function __construct()
	{
		// Place your add_actions and add_filters here
		add_action( 'init', array( &$this, 'counterize_plugin_os_init_callback' ) );

	} // end of constructor

	/*
	 * Place your plugin initialization code here
	 * For example, load your plugin translations here, not before.
	 */
	public function counterize_plugin_os_init_callback()
	{
		//increment the plugin counter
		global $counterize_plugin_count;
		$counterize_plugin_count++;

		// Load the plugin localization (.mo files) located
		// in the plugin "lang" subdirectory
		if( function_exists( 'load_plugin_textdomain' ) )
		{
			load_plugin_textdomain(
				COUNTERIZE_PLUGIN_OS_TD,
				COUNTERIZE_PLUGIN_OS_DIR . '/lang',
				plugin_basename( dirname(__FILE__) ) . '/lang'
			);
		}

		//add a submenu page for our plugin
		add_filter( 'counterize_dashboard_add_submenu', array( &$this, 'counterize_dashboard_add_submenu_os_callback' ), 10, 2 );
		add_action( 'counterize_show_data', array( &$this, 'counterize_show_data_os' ), 10, 3 );
		add_filter( 'counterize_check_data', array( &$this, 'counterize_check_data_os' ), 10, 1 );

		//add a submenu in the Counterize toolbar
		add_action( 'counterize_toolbar_add_submenu', array( &$this, 'counterize_toolbar_add_submenu_callback' ), 10, 1 );

		//add shortcodes
		add_filter( 'counterize_shortcodes', array( &$this, 'counterize_plugin_os_shortcodes_callback' ), 10, 3 );

		//filters for the Counterize button in TinyMCE
		add_filter( 'counterize_mce_js_type_filter', array( &$this, 'counterize_mce_js_type_filter_callback' ), 10, 1 );

		//filter for the report option in Counterize settings
		add_filter( 'counterize_report_what_filter', array( &$this, 'counterize_report_what_filter_callback' ), 10, 1 );

		//generate the report
		add_filter( 'counterize_report', array( &$this, 'counterize_plugin_report_callback' ), 10, 2 );

	} // end of function

	public function counterize_dashboard_add_submenu_os_callback( $counterize_dashboard_handles, $capability )
	{
		$counterize_dashboard_handles['os'] = add_submenu_page
		(
			COUNTERIZE_MENU_SLUG,
			__( 'Counterize', COUNTERIZE_PLUGIN_OS_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_OS_TD ) . ' - ' . __( 'Operating Systems', COUNTERIZE_PLUGIN_OS_TD ),
			__( 'Operating Systems', COUNTERIZE_PLUGIN_OS_TD ),
			$capability,
			'counterize_dashboard_os',
			array( &$this, 'counterize_display_dashboard_os_page_callback' )
		);

		//load the help tabs
		add_action( 'load-' . $counterize_dashboard_handles['os'], array( &$this, 'counterize_add_help_tabs_to_os_callback' ) );

		//insert the stylesheet and script
		add_action( "admin_print_scripts-{$counterize_dashboard_handles['os']}", 'counterize_javascript_head_callback' );
		add_action( "admin_print_styles-{$counterize_dashboard_handles['os']}" , 'counterize_stylesheet_head_callback' );

		return $counterize_dashboard_handles;
	}

	public function counterize_toolbar_add_submenu_callback( $wp_admin_bar )
	{
		// add the All stats node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_os',
			'title'  => '<span class="ab-icon" style="margin-top: 3px; margin-right: 5px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/os_16x16.png" alt="Counterize icon" /></span>'
						. __( 'OS' ),
			'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard_os' ), // link to the related Counterize Dashboard entry
			'parent' => COUNTERIZE_TOOLBAR_STATS_GROUP_SLUG
		);
		$wp_admin_bar->add_node( $args );
	}

	/*
	 * display help tabs
	 */
	public function counterize_add_help_tabs_to_os_callback()
	{
		$screen = get_current_screen();
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-os-plugin-help-collapsible', // This should be unique for the screen.
				'title'   => __( 'Most used operating systems (collapsible)', COUNTERIZE_PLUGIN_OS_TD ),
				'content' => __( '<p>Displays a mixed diagram of the most used operating systems. Each root item represents a different OS regardless of its version.</p><p>Click on the [+] symbol near each item to expand the item and let version information appear.</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-os-plugin-help-noversion', // This should be unique for the screen.
				'title'   => __( 'Most used operating systems (without version)', COUNTERIZE_PLUGIN_OS_TD ),
				'content' => __( '<p>Displays a diagram of the most used operating systems regardless of their version.</p>', COUNTERIZE_PLUGIN_OS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-os-plugin-help-version', // This should be unique for the screen.
				'title'   => __( 'Most used operating systems', COUNTERIZE_PLUGIN_OS_TD ),
				'content' => __( '<p>Displays a diagram of the most used operating systems with version information.</p>', COUNTERIZE_PLUGIN_OS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->set_help_sidebar
		(
			__( '<p>Choose a tab to learn more about the related diagram.</p>', COUNTERIZE_PLUGIN_OS_TD )
		);
	}

	public function counterize_display_dashboard_os_page_callback()
	{
		// Amount to pass as option to the graphs...
		$amount2 = counterize_get_option( 'amount2' );
		if( $amount2 == '' || $amount2 == ' ' || ! is_numeric( $amount2 ) )
		{
			$amount2 = 10;
		}

		echo '
		<div id="icon-plugins" class="icon32"></div>
		<h1>' . __( 'Counterize', COUNTERIZE_PLUGIN_OS_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_OS_TD ) . ' - ' . __( 'Operating Systems', COUNTERIZE_PLUGIN_OS_TD ) . '</h1>
		';

		$this->counterize_show_data_os( true, $amount2, true );

		//Print the footer
		counterize_pagefooter();
	}

	public function counterize_show_data_os( $admin = false, $amount2 = 10, $directcall = true )
	{
		if( $directcall )
		{
			?>

			<p><?php _e( 'Need help about this page? Click on <strong>Help</strong> on the top of this page!', COUNTERIZE_PLUGIN_OS_TD ); ?></p>

			<?php
			if( ! counterize_getamount() )
			{
				_e( "There's no data in the database - You can't see stats until you have data.", COUNTERIZE_PLUGIN_OS_TD );
				return;
			}
		}
		?>

	<!-- OS -->
	<div class="wrap">

		<?php $this->counterize_render_most_used_os_collapsible( $amount2, $amount2 ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_most_used_os_without_version( $amount2 ); ?>

	</div>

	<div class="wrap">

		<?php $this->counterize_render_most_used_os( $amount2 ); ?>

	</div>

		<?php
	}






	// get the most used os data feed with detailed version statistics for each item
	public function counterize_feed_most_used_os_collapsible( $nb_parent_items = 10, $nb_child_items = 15, $header_override = '' )
	{
		global $wpdb;
		$nb_parent_items = (int) $wpdb->prepare( "%d", $nb_parent_items );
		$nb_child_items  = (int) $wpdb->prepare( "%d", $nb_child_items );

		$sql_parents =
				"SELECT `osName` AS label, `osCode` AS `code`, `osURL` as `url`, SUM( `count` ) AS `count` "
			. "  FROM `" . counterize_agentsTable() . "`"
			. "  GROUP BY `label` "
			. "  ORDER BY `count` DESC , `label` ASC "
			. "  LIMIT {$nb_parent_items} ";

		$sql_children =
				"SELECT `osVersion` AS label, SUM( `count` ) AS `count` "
			. "  FROM `" . counterize_agentsTable() . "`"
			. "  WHERE `osName` = %s "
			. "  GROUP BY `label` "
			. "  ORDER BY `count` DESC , `label` ASC "
			. "  LIMIT %d ";

		$parents_rows = $wpdb->get_results( $sql_parents );
		unset( $sql_parents );

		$title = __( 'Most used operating systems ‪&#x202A;(click [+] for details)&#x202C;', COUNTERIZE_PLUGIN_OS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$parents_feed = new CounterizeFeed( $title, __( 'Operating systems', COUNTERIZE_PLUGIN_OS_TD ) );

		foreach( $parents_rows as $parent_row )
		{
			$src = counterize_get_image_url( $parent_row->code );
			$name = $parent_row->label;
			if( empty( $parent_row->code ) || $parent_row->code == ' ' )
			{
				$parent_label = __( 'Unknown', COUNTERIZE_PLUGIN_OS_TD );
			}
			else
			{
				$parent_label = $name;
			}

			$children_rows = $wpdb->get_results( $wpdb->prepare( $sql_children, $parent_label, $nb_child_items ) );
			$has_subitems = ( ( count( $children_rows ) > 1 || ( count( $children_rows ) == 1 && $children_rows[0]->label != '' ) ) && $parent_row->code != 'unknown' );
			if( $has_subitems )
			{
				$children_feed = new CounterizeFeed( __( 'Detailed statistics for this operating system', COUNTERIZE_PLUGIN_OS_TD ), sprintf( __( '%s versions', COUNTERIZE_PLUGIN_OS_TD ), $parent_label ) );
				foreach( $children_rows as $child_row )
				{
					if( empty( $child_row->label ) || $child_row->label == ' ' )
					{
						$child_label = __( '(no version)', COUNTERIZE_PLUGIN_OS_TD );
					}
					else
					{
						$child_label = $child_row->label;
					}
					$children_feed->add_item_2( $child_row->count, $child_label );
					unset( $child_label );
				}
				$children_feed->refresh_percentages();
			}
			unset( $children_rows );

			if( ! empty( $src ) )
			{
				$img = new CounterizeFeedImg
				(
					$src,
					$parent_label,
					$parent_label,
					'browsericon'
				);
				if( $has_subitems )
				{
					$parents_feed->add_item_5( $parent_row->count, $parent_label, $parent_row->url, $img, $children_feed );
				}
				else
				{
					$parents_feed->add_item_4( $parent_row->count, $parent_label, $parent_row->url, $img );
				}
			}
			else
			{
				if( $has_subitems )
				{
					$parents_feed->add_item_5( $parent_row->count, $parent_label, $parent_row->url, NULL, $children_feed );
				}
				else
				{
					$parents_feed->add_item_3( $parent_row->count, $parent_label, $parent_row->url );
				}
			}
			unset( $src, $name, $parent_label );
		}
		unset( $parents_rows, $sql_children );
		$parents_feed->refresh_percentages();

		return $parents_feed;
	}

	// render the most used os data feed with detailed version statistics for each item
	public function counterize_render_most_used_os_collapsible( $nb_parent_items = 10, $nb_child_items = 15, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_used_os_collapsible( $nb_parent_items, $nb_child_items, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}







	// get the most used os without version data feed
	public function counterize_feed_most_used_os_without_version( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$sql = "SELECT SUM(`count`) AS `count`, `osName` AS `label`, `osCode` AS `code`, `osURL` AS `url` "
			. " FROM `" . counterize_agentsTable() . "`"
			. " GROUP BY `label` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most used operating systems ‪‪&#x202A;(without version)‪&#x202C;', COUNTERIZE_PLUGIN_OS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Operating systems', COUNTERIZE_PLUGIN_OS_TD ) );

		foreach( $rows as $row )
		{
			$src = counterize_get_image_url( $row->code );
			$name = $row->label;
			if( empty( $row->code ) || $row->code == ' ' || $row->code == 'unknown' )
			{
				$label = __( 'Unknown', COUNTERIZE_PLUGIN_OS_TD );
			}
			else
			{
				$label = $name;
			}
			if( !empty( $src ) )
			{
				$img = new CounterizeFeedImg
				(
					$src,
					$label,
					$label,
					'browsericon'
				);
				$feed->add_item_4( $row->count, $label, $row->url, $img );
			}
			else
			{
				$feed->add_item_3( $row->count, $label, $row->url );
			}
			unset( $src, $name, $label );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most used os without version data feed
	public function counterize_render_most_used_os_without_version( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_used_os_without_version( $number, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}












	// get the most used os data feed
	public function counterize_feed_most_used_os( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$sql = "SELECT SUM( `count` ) AS `count`, CONCAT( CONCAT( `osName`, ' ' ), `osVersion` ) AS `label`, `osCode` AS `code`, `osURL` AS `url` "
			. " FROM `" . counterize_agentsTable() . "`"
			. " GROUP BY `label` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most used operating systems', COUNTERIZE_PLUGIN_OS_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Operating systems', COUNTERIZE_PLUGIN_OS_TD ) );

		foreach( $rows as $row )
		{
			$src = counterize_get_image_url( $row->code );
			$name = $row->label;
			if( empty( $row->code ) || $row->code == ' ' || $row->code == 'unknown' )
			{
				$label = __( 'Unknown', COUNTERIZE_PLUGIN_OS_TD );
			}
			else
			{
				$label = $name;
			}
			if( !empty( $src ) )
			{
				$img = new CounterizeFeedImg
				(
					$src,
					$label,
					$label,
					'browsericon'
				);
				$feed->add_item_4( $row->count, $label, $row->url, $img );
			}
			else
			{
				$feed->add_item_3( $row->count, $label, $row->url );
			}
			unset( $src, $name, $label );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most used os data feed
	public function counterize_render_most_used_os( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_used_os( $number, $header_override );
		$feed->render_feed_vertical( true, '100%', false, false, false, $print_header );
	}



	public function counterize_check_data_os( $data )
	{
		//<!-- counterize_stats_os_nb --> : Shows a list of the nb most used operating systems (with their version)
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_os_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_used_os' ), 1
		);

		//<!-- counterize_stats_os_nover_nb --> : Shows a list of the nb most used operating systems (without their version)
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_os_nover_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_used_os_without_version' ), 1
		);

		//<!-- counterize_stats_os_mixed_nb1_nb2 --> : Shows a list of the nb1 most used operating systems (clicking each entry will show the version details)
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_os_mixed_([0-9]+)_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_used_os_collapsible' ), 2
		);

		return $data;
	}

	public function counterize_plugin_os_shortcodes_callback( $output, $attr, $content = null )
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
		if( $type == 'os' )
		{
			if( $collapsible == 'yes' )
			{
				$this->counterize_render_most_used_os_collapsible( $items, $subitems, $print_header === 'yes', $header );
			}
			elseif( $version == 'yes' )
			{
				$this->counterize_render_most_used_os( $items, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_used_os_without_version( $items, $print_header === 'yes', $header );
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
		return $content . '										<option value=\"os\">' . __( 'Operating Systems', COUNTERIZE_PLUGIN_OS_TD ) . '</option>\
';
	}

	public function counterize_report_what_filter_callback( $what )
	{
		$what['os'] = __( 'Operating Systems', COUNTERIZE_PLUGIN_OS_TD );
		return $what;
	}

	public function counterize_plugin_report_callback( $output, $what )
	{
		$items = counterize_get_option( 'amount2' );
		$subitems = $items;
		ob_start();
		if( in_array( 'os', $what ) )
		{
			$this->counterize_render_most_used_os_collapsible( $items, $subitems );
			$this->counterize_render_most_used_os( $items );
			$this->counterize_render_most_used_os_without_version( $items );
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
 * Instanciate a new instance of Counterize_Plugin_OS
 */
$counterize_plugins['os'] = new Counterize_Plugin_OS();

?>