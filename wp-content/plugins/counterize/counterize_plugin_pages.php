<?php
/*
 Plugin Name: Counterize plugin: Pages
 Plugin URI: http://www.gabsoftware.com/products/scripts/counterize/plugins/pages
 Description: Display some information about popular pages and posts, and the most requested URLs, for the Counterize plugin
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
add_filter( 'site_transient_update_plugins', 'counterize_plugin_pages_disable_update_check' );

// Security check #2. We do not want to be able to load the plugin if Counterize isn't loaded before.
if( !defined( 'COUNTERIZE_CHECK') )
{
	add_action('admin_notices', 'counterize_plugin_pages_admin_notice');
	return;
}

/* constants */

// Counterize Pages plugin text domain
define( 'COUNTERIZE_PLUGIN_PAGES_TD', COUNTERIZE_TD );

// Absolute path of the plugin from the server view
define( 'COUNTERIZE_PLUGIN_PAGES_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__) ) );

// Public URL of the plugin
define( 'COUNTERIZE_PLUGIN_PAGES_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__) ) );

/*
 * Prevents WordPress from displaying wrong update notifications because
 * this plugin is located in the same directory as Counterize
 */
function counterize_plugin_pages_disable_update_check( $value = '' )
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
function counterize_plugin_pages_admin_notice()
{
	global $pagenow;
	if( current_user_can( 'install_plugins' ) && $pagenow == 'plugins.php' )
	{
		echo '<div class="error"><p>';
		echo 'The Counterize plugin "Pages" could not find Counterize. Please install and activate the <a target="_blank" href="plugin-install.php?tab=plugin-information&amp;plugin=counterize&amp;TB_iframe=false&amp;width=600&amp;height=550">Counterize plugin</a> first.';
		echo '</p></div>';
	}
}

class Counterize_Plugin_Pages
{
	/*
	 * Our plugin constructor
	 */
	function __construct()
	{
		// Place your add_actions and add_filters here
		add_action( 'init', array( &$this, 'counterize_plugin_pages_init_callback' ) );

	} // end of constructor

	/*
	 * Place your plugin initialization code here
	 * For example, load your plugin translations here, not before.
	 */
	public function counterize_plugin_pages_init_callback()
	{
		//increment the plugin counter
		global $counterize_plugin_count;
		$counterize_plugin_count++;

		// Load the plugin localization (.mo files) located
		// in the plugin "lang" subdirectory
		if( function_exists( 'load_plugin_textdomain' ) )
		{
			load_plugin_textdomain(
				COUNTERIZE_PLUGIN_PAGES_TD,
				COUNTERIZE_PLUGIN_PAGES_DIR . '/lang',
				plugin_basename( dirname(__FILE__) ) . '/lang'
			);
		}

		//add a submenu page for our plugin
		add_filter( 'counterize_dashboard_add_submenu', array( &$this, 'counterize_dashboard_add_submenu_pages_callback' ), 10, 2 );
		add_action( 'counterize_show_data', array( &$this, 'counterize_show_data_pages' ), 10, 3 );
		add_filter( 'counterize_check_data', array( &$this, 'counterize_check_data_pages' ), 10, 1 );

		//add a submenu in the Counterize toolbar
		add_action( 'counterize_toolbar_add_submenu', array( &$this, 'counterize_toolbar_add_submenu_callback' ), 10, 1 );

		//add shortcodes
		add_filter( 'counterize_shortcodes', array( &$this, 'counterize_plugin_pages_shortcodes_callback' ), 10, 3 );

		//filters for the Counterize button in TinyMCE
		add_filter( 'counterize_mce_js_type_filter', array( &$this, 'counterize_mce_js_type_filter_callback' ), 10, 1 );

		//filter for the report option in Counterize settings
		add_filter( 'counterize_report_what_filter', array( &$this, 'counterize_report_what_filter_callback' ), 10, 1 );

		//generate the report
		add_filter( 'counterize_report', array( &$this, 'counterize_plugin_report_callback' ), 10, 2 );

	} // end of function

	public function counterize_dashboard_add_submenu_pages_callback( $counterize_dashboard_handles, $capability )
	{
		$counterize_dashboard_handles['pages'] = add_submenu_page
		(
			COUNTERIZE_MENU_SLUG,
			__( 'Counterize', COUNTERIZE_PLUGIN_PAGES_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_PAGES_TD ) . ' - ' . __( 'Popular pages', COUNTERIZE_PLUGIN_PAGES_TD ),
			__( 'Popular pages', COUNTERIZE_PLUGIN_PAGES_TD ),
			$capability,
			'counterize_dashboard_pages',
			array( &$this, 'counterize_display_dashboard_pages_page_callback' )
		);

		//load the help tabs
		add_action( 'load-' . $counterize_dashboard_handles['pages'], array( &$this, 'counterize_add_help_tabs_to_pages_callback' ) );

		//insert the stylesheet and script
		add_action( "admin_print_scripts-{$counterize_dashboard_handles['pages']}", 'counterize_javascript_head_callback' );
		add_action( "admin_print_styles-{$counterize_dashboard_handles['pages']}" , 'counterize_stylesheet_head_callback' );

		return $counterize_dashboard_handles;
	}

	public function counterize_toolbar_add_submenu_callback( $wp_admin_bar )
	{
		// add the All stats node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_pages',
			'title'  => '<span class="ab-icon" style="margin-top: 3px; margin-right: 5px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/pages_16x16.png" alt="Counterize icon" /></span>'
						. __( 'Popular pages' ),
			'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard_pages' ), // link to the related Counterize Dashboard entry
			'parent' => COUNTERIZE_TOOLBAR_STATS_GROUP_SLUG
		);
		$wp_admin_bar->add_node( $args );
	}

	/*
	 * display help tabs
	 */
	public function counterize_add_help_tabs_to_pages_callback()
	{
		$screen = get_current_screen();
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-pages-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most popular posts/pages', COUNTERIZE_PLUGIN_PAGES_TD ),
				'content' => __( '<p>Displays a diagram of the most popular posts or pages.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
						. __( '<p>This is useful to find what is the favorite post or page of your visitors. It can also show you which posts or pages you could improve.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-pages24hr-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most searched keywords for the last 24 hours', COUNTERIZE_PLUGIN_PAGES_TD ),
				'content' => __( '<p>Displays a diagram of the most popular posts or pages, for the last 24 hours only.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
						. __( '<p>This is useful to find what is the favorite post or page of your visitors. It can also show you which posts or pages you could improve.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-urls-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most requested URLs', COUNTERIZE_PLUGIN_PAGES_TD ),
				'content' => __( '<p>Displays a diagram of the most requested URLs.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
						. __( '<p>This is useful if your posts or pages can be accessed by different URLs: you will know the most popular URL.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-urls24hr-plugin-help', // This should be unique for the screen.
				'title'   => __( 'Most requested URLs for the last 24 hours', COUNTERIZE_PLUGIN_PAGES_TD ),
				'content' => __( '<p>Displays a diagram of the most requested URLs, for the last 24 hours only.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
						. __( '<p>This is useful if your posts or pages can be accessed by different URLs: you will know the most popular URL.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->set_help_sidebar
		(
			__( '<p>Choose a tab to learn more about the related diagram.</p>', COUNTERIZE_PLUGIN_PAGES_TD )
		);
	}

	public function counterize_display_dashboard_pages_page_callback()
	{
		// Amount to pass as option to the graphs...
		$amount2 = counterize_get_option( 'amount2' );
		if( $amount2 == '' || $amount2 == ' ' || ! is_numeric( $amount2 ) )
		{
			$amount2 = 10;
		}

		echo '
		<div id="icon-plugins" class="icon32"></div>
		<h1>' . __( 'Counterize', COUNTERIZE_PLUGIN_PAGES_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_PLUGIN_PAGES_TD ) . ' - ' . __( 'Popular pages', COUNTERIZE_PLUGIN_PAGES_TD ) . '</h1>
		';

		$this->counterize_show_data_pages( true, $amount2, true );

		//Print the footer
		counterize_pagefooter();
	}

	public function counterize_show_data_pages( $admin = false, $amount2 = 10, $directcall = true )
	{
		if( $directcall )
		{
			?>

			<p><?php _e( 'Need help about this page? Click on <strong>Help</strong> on the top of this page!', COUNTERIZE_PLUGIN_PAGES_TD ); ?></p>

			<?php
			if( ! counterize_getamount() )
			{
				_e( "There's no data in the database - You can't see stats until you have data.", COUNTERIZE_PLUGIN_PAGES_TD );
				return;
			}
		}
		?>

		<!-- Popular posts -->
		<div class="wrap">

			<?php $this->counterize_render_most_popular_posts( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_popular_posts24hrs( $amount2 ); ?>

		</div>

		<!-- Urls -->
		<div class="wrap">

			<?php $this->counterize_render_most_requested_urls( $amount2 ); ?>

		</div>

		<div class="wrap">

			<?php $this->counterize_render_most_requested_urls24hrs( $amount2 ); ?>

		</div>

		<?php
	}






	// get the most requested urls data feed
	public function counterize_feed_most_requested_urls( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$sql = "SELECT `count` , `url` AS url, `url` AS label "
			. " FROM `" . counterize_pageTable() . "`"
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most requested URLs', COUNTERIZE_PLUGIN_PAGES_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'URL', COUNTERIZE_PLUGIN_PAGES_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->label, $row->url );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most requested urls data feed
	public function counterize_render_most_requested_urls( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_requested_urls( $number, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}











	// get the most requested urls data feed for the last 24 hours
	public function counterize_feed_most_requested_urls24hrs( $number = 10, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$sql = "SELECT COUNT( m.`id` ) AS `count`, p.`url` AS url, p.`url` AS label "
			. " FROM `" . counterize_logTable() . "` m, `" . counterize_pageTable() . "` p "
			. " WHERE m.`pageID` = p.`pageID`"
			. " AND m.`timestamp` >= '{$onedayago}'"
			. " GROUP BY p.`url` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most requested URLs ‪of the last 24 hours', COUNTERIZE_PLUGIN_PAGES_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'URL', COUNTERIZE_PLUGIN_PAGES_TD ) );

		foreach( $rows as $row )
		{
			$feed->add_item_3( $row->count, $row->label, $row->url );
		}
		unset( $rows, $sql, $number, $onedayago );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most requested urls data feed for the last 24 hours
	public function counterize_render_most_requested_urls24hrs( $number = 10, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_requested_urls24hrs( $number, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}














	// get the most popular posts/pages data feed
	public function counterize_feed_most_popular_posts( $number = 10, $tn_width = 50, $tn_height = 50, $header_override = '' )
	{
		global $wpdb;
		$number = $wpdb->prepare( "%d", $number );
		$sql = "SELECT c.`postID`, SUM( c.`count`) AS `count`, c.`url`, w.`post_title` AS label "
			. " FROM `" . counterize_pageTable() . "` c, `" . $wpdb->posts . "` w "
			. " WHERE c.`postID` = w.`ID` "
			. " GROUP BY c.`postID` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most popular posts/pages', COUNTERIZE_PLUGIN_PAGES_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Posts/pages', COUNTERIZE_PLUGIN_PAGES_TD ) );

		foreach( $rows as $row )
		{
			$label = trim( stripslashes( $row->label ) );
			if( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $row->postID ) )
			{
				$thumbnail_id = get_post_thumbnail_id( $row->postID  );
				$thumbnail_arr = wp_get_attachment_image_src( $thumbnail_id, array( $tn_width, $tn_height ) );
				$thumbnail_src = $thumbnail_arr[0];

				$img = new CounterizeFeedImg
				(
					$thumbnail_src,
					$label,
					sprintf( __( 'Click here to view the post "%s"', COUNTERIZE_PLUGIN_PAGES_TD ), $row->label ),
					'counterize_thumb',
					$tn_width,
					$tn_height
				);
				$feed->add_item_4( $row->count, $label, $row->url, $img );
				unset( $thumbnail_id, $thumbnail_arr, $thumbnail_src );
			}
			else
			{
				$feed->add_item_3( $row->count, $label, $row->url );
			}
			unset( $label );
		}
		unset( $rows, $sql );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most popular posts/pages data feed
	public function counterize_render_most_popular_posts( $number = 10, $tn_width = 50, $tn_height = 50, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_popular_posts( $number, $tn_width, $tn_height, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}












	// get the most popular posts/pages data feed for the last 24 hours
	public function counterize_feed_most_popular_posts24hrs( $number = 10, $tn_width = 50, $tn_height = 50, $header_override = '' )
	{
		global $wpdb;
		$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
		$number = $wpdb->prepare( "%d", $number );
		$sql = "SELECT COUNT( l.`id` ) AS `count`, c.`postID`, c.`url` AS url, w.`post_title` AS label "
			. " FROM `" . counterize_logTable() . "` l, `" . counterize_pageTable() . "` c, `" . $wpdb->posts . "` w "
			. " WHERE l.`pageID` = c.`pageID` "
			. " AND c.`postID` = w.`ID` "
			. " AND l.`timestamp` >= '{$onedayago}'"
			. " GROUP BY c.`postID` "
			. " ORDER BY `count` DESC "
			. " LIMIT {$number}";
		$rows = $wpdb->get_results( $sql );

		$title = __( 'Most popular posts/pages for the last 24 hours', COUNTERIZE_PLUGIN_PAGES_TD );
		if( ! empty( $header_override ) )
		{
			$title = $header_override;
		}

		$feed = new CounterizeFeed( $title, __( 'Posts/pages', COUNTERIZE_PLUGIN_PAGES_TD ) );

		foreach( $rows as $row )
		{
			$label = trim( stripslashes( $row->label ) );
			if( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $row->postID ) )
			{
				$thumbnail_id = get_post_thumbnail_id( $row->postID );
				$thumbnail_arr = wp_get_attachment_image_src( $thumbnail_id, array( $tn_width, $tn_height ) );
				$thumbnail_src = $thumbnail_arr[0];

				$img = new CounterizeFeedImg
				(
					$thumbnail_src,
					$label,
					sprintf( __( 'Click here to view the post "%s"', COUNTERIZE_PLUGIN_PAGES_TD ), $row->label ),
					'counterize_thumb',
					$tn_width,
					$tn_height
				);
				$feed->add_item_4( $row->count, $label, $row->url, $img );
				unset( $thumbnail_id, $thumbnail_arr, $thumbnail_src );
			}
			else
			{
				$feed->add_item_3( $row->count, $label, $row->url );
			}
			unset( $label );
		}
		unset( $rows, $sql, $onedayago );
		$feed->refresh_percentages();

		return $feed;
	}

	// render the most popular posts/pages data feed for the last 24 hours
	public function counterize_render_most_popular_posts24hrs( $number = 10, $tn_width = 50, $tn_height = 50, $print_header = true, $header_override = '' )
	{
		$feed = $this->counterize_feed_most_popular_posts24hrs( $number, $tn_width, $tn_height, $header_override );
		$feed->render_feed_vertical( false, '100%', true, true, false, $print_header );
	}



	public function counterize_check_data_pages( $data )
	{

		//<!-- counterize_stats_urls_nb --> : Shows a list of the nb most requested URLs
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_urls_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_requested_urls' ), 1
		);

		//<!-- counterize_stats_urls_24hrs_nb --> : Shows a list of the nb most requested URLs during the last 24 hours
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_urls_24hrs_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_requested_urls24hrs' ), 1
		);

		//<!-- counterize_stats_posts_nb --> : Shows a list of the nb most popular posts/pages
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_posts_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_popular_posts' ), 1
		);

		//<!-- counterize_stats_posts_24hrs_nb --> : Shows a list of the nb most popular posts/pages during the last 24 hours
		$data = counterize_check_data
		(
			$data, '/(\<\!\-|\#)\-\s*counterize_stats_posts_24hrs_([0-9]+)\s*\-(\-\>|\#)/', array( &$this, 'counterize_render_most_popular_posts24hrs' ), 1
		);

		return $data;
	}

	public function counterize_plugin_pages_shortcodes_callback( $output, $attr, $content = null )
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
		if( $type == 'urls' )
		{
			if( $period == '24h' )
			{
				$this->counterize_render_most_requested_urls24hrs( $items, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_requested_urls( $items, $print_header === 'yes', $header );
			}
		}
		elseif( $type == 'posts' )
		{
			if( $period == '24h' )
			{
				$this->counterize_render_most_popular_posts24hrs( $items, $tn_width, $tn_height, $print_header === 'yes', $header );
			}
			else
			{
				$this->counterize_render_most_popular_posts( $items, $tn_width, $tn_height, $print_header === 'yes', $header );
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
		return $content . '										<option value=\"urls\">' . __( 'URLs', COUNTERIZE_PLUGIN_PAGES_TD ) . '</option>\
										<option value=\"posts\">' . __( 'Posts and Pages', COUNTERIZE_PLUGIN_PAGES_TD ) . '</option>\
';
	}

	public function counterize_report_what_filter_callback( $what )
	{
		$what['urls'] = __( 'URLs', COUNTERIZE_PLUGIN_PAGES_TD );
		$what['posts'] = __( 'Posts and Pages', COUNTERIZE_PLUGIN_PAGES_TD );
		return $what;
	}

	public function counterize_plugin_report_callback( $output, $what )
	{
		$items = counterize_get_option( 'amount2' );
		ob_start();
		if( in_array( 'urls', $what ) )
		{
			$this->counterize_render_most_requested_urls( $items );
			$this->counterize_render_most_requested_urls24hrs( $items );
		}
		if( in_array( 'posts', $what ) )
		{
			$this->counterize_render_most_popular_posts( $items );
			$this->counterize_render_most_popular_posts24hrs( $items );
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
 * Instanciate a new instance of Counterize_Plugin_Pages
 */
$counterize_plugins['pages'] = new Counterize_Plugin_Pages();

?>