<?php
/*
 Plugin Name: Counterize
 Plugin URI: http://www.gabsoftware.com/products/scripts/counterize/
 Description: Complete counter and statistics plugin for Wordpress with no external libs. See Readme for more details.
 Version: 3.1.5
 Author: Gabriel Hautclocq
 Author URI: http://www.gabsoftware.com/
 License: ISC
*/

//error_reporting( E_ALL );

if( ! isset( $_SESSION ) )
{
	session_start();
}

// security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	if( ! isset( $_GET['external'] ) || ! isset( $_GET['href'] ) || ! isset( $_GET['from'] ) )
	{
		die( 'There is nothing to see here!' );
	}
	else
	{
		// Make sure that the WordPress bootstrap has run before continuing.
		require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php');
	}
}


/* constants */

// Used to check if Counterize has been loaded
define( 'COUNTERIZE_CHECK', 'true' );

// Counterize text domain
define( 'COUNTERIZE_TD', 'counterize' );

// Counterize version
define( 'COUNTERIZE_VERSION_MAJ', 3   );
define( 'COUNTERIZE_VERSION_MIN', 1   );
define( 'COUNTERIZE_VERSION_REV', 4  );

// Specify width and height for icons here
define( 'COUNTERIZE_ICON_SIZE', 16  );

// Counterize directory seen from the server
define( 'COUNTERIZE_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );

//counterize directory seen from internet
if( counterize_is_ssl() )
{
	define( 'COUNTERIZE_PLUGIN_URL', str_replace( 'http://', 'https://', WP_PLUGIN_URL ) . '/' . plugin_basename( dirname( __FILE__ ) ) );
}
else
{
	define( 'COUNTERIZE_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) );
}

//Counterize public and server-side image directories
define( 'COUNTERIZE_IMAGE_URL', COUNTERIZE_PLUGIN_URL . '/images' );
define( 'COUNTERIZE_IMAGE_DIR', COUNTERIZE_PLUGIN_DIR . '/images' );

define( 'COUNTERIZE_MENU_SLUG', 'counterize_dashboard' );

define( 'COUNTERIZE_TOOLBAR_SLUG', 'counterize_toolbar' );
define( 'COUNTERIZE_TOOLBAR_STATS_GROUP_SLUG', 'counterize_stats_group' );

/* global variables */

//capability needed to view the Counterize Dashboard
//$counterize_dashboard_capability = 'manage_options';

//we store the plugin count here.
$counterize_plugin_count = 0;

//include counterize non-admin php files
$counterize_includes = array
(
	COUNTERIZE_PLUGIN_DIR . '/counterize_browsniff.php',
	COUNTERIZE_PLUGIN_DIR . '/counterize_iptocountry.php',
	COUNTERIZE_PLUGIN_DIR . '/counterize_feed.php'
	//COUNTERIZE_PLUGIN_DIR . '/counterize_widget.php'
	//COUNTERIZE_PLUGIN_DIR . '/counterize_deprecated.php'
);
$counterize_includes = apply_filters( 'counterize_before_includes', $counterize_includes );
foreach( $counterize_includes as $counterize_include )
{
	require_once( $counterize_include );
}
do_action( 'counterize_after_includes' );

//Process outlinks then exit the counterize script ASAP.
if( ! empty( $_GET['external'] ) && ! empty( $_GET['href'] ) && ! empty( $_GET['from'] ) )
{
	if( $_GET['external'] === '1' )
	{
		//add the outlink
		counterize_add_outlink( urldecode( $_GET['href'] ), urldecode( $_GET['from'] ) );

		/*
		//for debug only
		$pathinfo = pathinfo( __FILE__ );
		$path = $pathinfo['dirname'];
		file_put_contents( "{$path}/outlinks.txt", "\n\n" . 'leaving: ' . $_GET['from'] . "\n" . 'going to: ' . $_GET['href'], FILE_APPEND );
		*/

		//response
		echo 'ok';

		//end the script
		exit;
	}
	else
	{
		//response
		echo 'error';

		//end the script
		exit;
	}
}

/*
 * Used to translate the days and months names
 */
function counterize_translate_days()
{
	$days = Array
	(
		__( 'Monday',    COUNTERIZE_TD ),
		__( 'Tuesday',   COUNTERIZE_TD ),
		__( 'Wednesday', COUNTERIZE_TD ),
		__( 'Thursday',  COUNTERIZE_TD ),
		__( 'Friday',    COUNTERIZE_TD ),
		__( 'Saturday',  COUNTERIZE_TD ),
		__( 'Sunday',    COUNTERIZE_TD )
	);

	$months = Array
	(
		__( 'Jan %d', COUNTERIZE_TD ),
		__( 'Feb %d', COUNTERIZE_TD ),
		__( 'Mar %d', COUNTERIZE_TD ),
		__( 'Apr %d', COUNTERIZE_TD ),
		__( 'May %d', COUNTERIZE_TD ),
		__( 'Jun %d', COUNTERIZE_TD ),
		__( 'Jul %d', COUNTERIZE_TD ),
		__( 'Aug %d', COUNTERIZE_TD ),
		__( 'Sep %d', COUNTERIZE_TD ),
		__( 'Oct %d', COUNTERIZE_TD ),
		__( 'Nov %d', COUNTERIZE_TD ),
		__( 'Dec %d', COUNTERIZE_TD )
	);
}

// Counterize Tables
function counterize_agentsTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize_UserAgents';
}

function counterize_logTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize';
}

function counterize_pageTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize_Pages';
}

function counterize_refererTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize_Referers';
}

function counterize_keywordTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize_Keywords';
}

function counterize_countryTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize_Countries';
}

function counterize_outlinksTable()
{
	global $wpdb;
	return $wpdb->prefix . 'Counterize_Outlinks';
}

// Returns the value of the specified option
function counterize_get_option( $name )
{
	$options = get_option( 'counterize_options' );
	if( isset( $options[$name] ) )
	{
		return $options[$name];
	}
	else
	{
		return FALSE;
	}
}

// Sets the value of the specified option
function counterize_set_option( $name, $value )
{
	$options = get_option( 'counterize_options' );
	$options[$name] = $value;

	return update_option( 'counterize_options', $options );
}

function counterize_get_version( $what = 'all' )
{
	$version =  get_option( 'counterize_version' );
	if( $version === FALSE )
	{
		//The option was not found. We are certainly installing Counterize for the first time.
		// We return a default version.
		$version = '1.0.0';
	}

	switch( $what )
	{
		case 'all':
			return $version;
			break;
		case 'major':
			$version_array = explode( '.', $version );
			return $version_array[0];
			break;
		case 'minor':
			$version_array = explode( '.', $version );
			return $version_array[1];
			break;
		case 'revision':
			$version_array = explode( '.', $version );
			return $version_array[2];
			break;
		default:
			return $version;
	}
}

//return true if the connection uses SSL
function counterize_is_ssl()
{
	if( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' )
	{
		return true;
	}
	elseif( $_SERVER['SERVER_PORT'] == 443 )
	{
		return true;
	}
	else
	{
		return false;
	}
}

// seed with microseconds
function counterize_make_seed()
{
	list( $usec, $sec ) = explode( ' ', microtime() );
	return (float) $sec + ( (float) $usec * 100000 );
}

function counterize_get_random_number( $min, $max )
{
	mt_srand( counterize_make_seed() );
	return mt_rand( $min, $max );
}

// Returns how many entries there are in the DB.
// if $only_this_month is set to true, return the number of hits since the beginning of the current month
// else if $since > 0, return the number of hits since $since seconds
function counterize_getamount( $only_this_month = false, $since = 0 )
{
	global $wpdb;
	$since = intval( $since );
	$sql = 'SELECT COUNT( 1 ) '
		. ' FROM `' . counterize_logTable() . '` ';
	if( $only_this_month )
	{
		$sql .= $wpdb->prepare( ' WHERE `timestamp` >= %s ', date( "Y-m-01" ) );
	}
	elseif( $since > 0 )
	{
		$sincedate = date( 'Y-m-d', time() - $since );
		$sql .= $wpdb->prepare( ' WHERE `timestamp` >= %s ', $sincedate );
	}
	return $wpdb->get_var( $sql );
}

/*
// Returns how many key-words there are in the DB.
function counterize_getkeywordamount()
{
	global $wpdb;
	$sql = "SELECT SUM( `count` ) "
		. " FROM `" . counterize_keywordTable() . "`"
		. " WHERE `keywordID` <> 1";
	return $wpdb->get_var( $sql );
}
*/

// Return how many unique entries there are in the DB. After $interval seconds, a new entry with the same haship will be counted as new.
// Example: Get the unique visitors, with same haship counted as new after one hour:
// SELECT COUNT( DISTINCT haship, 3600 * ROUND( UNIX_TIMESTAMP( timestamp ) / 3600 ) ) AS NB
function counterize_getuniqueamount( $since = 0, $interval = 0 )
{
	global $wpdb;
	$interval = intval( $interval );
	$since = intval( $since );
	if( $interval == 0 )
	{
		$sql = 'SELECT COUNT( DISTINCT `haship` ) ';
	}
	else
	{
		$sql = $wpdb->prepare( 'SELECT COUNT( DISTINCT `haship`, %d * ROUND( UNIX_TIMESTAMP( `timestamp` ) / %d ) ) ', $interval, $interval );
	}
	$sql .= ' FROM `' . counterize_logTable() . '` ';
	if( $since > 0 )
	{
		$sincedate = date( 'Y-m-d', time() - $since );
		$sql .= $wpdb->prepare( 'WHERE `timestamp` >= %s ', $sincedate );
	}
	return $wpdb->get_var( $sql );
}


// Return how many unique entries there are in the DB.
/*
function counterize_getuniqueamount()
{
	global $wpdb;
	$sql = "SELECT COUNT( DISTINCT `haship` ) "
		. " FROM `" . counterize_logTable() . "`";
	return $wpdb->get_var( $sql );
}
*/

// Return how many countries there are in the DB.
function counterize_getcountriesamount()
{
	global $wpdb;
	$sql = "SELECT COUNT( DISTINCT `countryID` ) "
		. " FROM `" . counterize_logTable() . "`";
	return $wpdb->get_var( $sql );
}

// Return how many pages have been visited.
function counterize_getpagesamount( $since = 0 )
{
	global $wpdb;
	$since = intval( $since );
	$sql = 'SELECT COUNT( 1 ) '
		. ' FROM `' . counterize_logTable() . '` L, `' . counterize_pageTable() . '` P '
		. ' WHERE L.`pageID` = P.`pageID` '
		. ' AND P.`postID` > 0 ';
	if( $since > 0 )
	{
		$sincedate = date( 'Y-m-d', time() - $since );
		$sql .= $wpdb->prepare( 'AND `timestamp` >= %s ', $sincedate );
	}
	return $wpdb->get_var( $sql );
}

// Returns amount of entries in the DB matching the IP address of the current visitor.
function counterize_getfromcurrentip()
{
	global $wpdb;
	$sql = "SELECT COUNT( 1 ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `IP` = '" . $_SERVER['REMOTE_ADDR'] . "'";
	return $wpdb->get_var( $sql );
}

// Returns amount of hits today.
function counterize_gethitstoday()
{
	global $wpdb;
	$today = date( 'Y-m-d' );
	$sql = "SELECT COUNT( 1 ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `timestamp` >= '{$today}'";
	return $wpdb->get_var( $sql );
}

// Returns amount of hits during the last 7 days.
function counterize_getlatest7days()
{
	global $wpdb;
	$sevendaysago = date( 'Y-m-d', time() - 604800 ); //604800 = 86400 * 7
	$sql = "SELECT COUNT( 1 ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `timestamp` >= '{$sevendaysago}'";
	return $wpdb->get_var( $sql );
}

// From Curtis(http://www.graymattersonline.net/)
// Returns amount of unique IP's in the last 7 days
function counterize_getuniquelatest7days()
{
	global $wpdb;
	$sevendaysago = date( 'Y-m-d', time() - 604800 ); //604800 = 86400 * 7
	$sql = "SELECT COUNT( DISTINCT `haship` ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `timestamp` >= '{$sevendaysago}'";
	return $wpdb->get_var( $sql );
}

// Returns the number of currently online users
// A user is considered online if its visit is less than 5 minutes old
function counterize_get_online_users()
{
	global $wpdb;
	$timestamp = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600 ) ); //3600 = 60 * 60
	$sql = "SELECT COUNT( DISTINCT `haship` ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `timestamp` > DATE_SUB( '{$timestamp}', INTERVAL 5 MINUTE )";
	return $wpdb->get_var( $sql );
}

// Returns amount of unique referer-URl's
function counterize_getuniquereferers()
{
	global $wpdb;
	$sql = "SELECT COUNT( DISTINCT `refererID` ) "
		. " FROM `" . counterize_logTable() . "`";
	return $wpdb->get_var( $sql );
}

// Returns amount of unique browser-strings in DB.
function counterize_getuniquebrowsers()
{
	global $wpdb;
	$sql = "SELECT COUNT( 1 ) "
		. " FROM `". counterize_agentsTable() . "`";
	return $wpdb->get_var( $sql );
}

// Returns amount on current article
function counterize_getHitsOnCurrentArticle()
{
	global $wpdb;
	if( $_SERVER['REQUEST_URI'] )
	{
		$requesturl = $_SERVER['REQUEST_URI'];
		$sql = "SELECT `count` "
			. " FROM `" . counterize_pageTable() . "`"
			. " WHERE `url` = '{$requesturl}'";
		return $wpdb->get_var( $sql );
	}
	return 0;
}

//get number of hits on current post/page
//( Thanks to Greg Froese )
function counterize_current_post_hits()
{
	//don't show anything if more than one post on the page
	if( is_single() || is_page() )
	{
		global $wp_query, $wpdb;
		$thePostID = $wp_query->post->ID;
		$sql = "SELECT SUM( `count` ) "
		. " FROM `" . counterize_pageTable() . "` "
		. " WHERE `postID` = %d";
		$result = $wpdb->get_var( $wpdb->prepare( $sql, $thePostID ) );
		if( ! $result )
		{
			$result = 0;
		}
		return $result;
	}
	return false;
}

// Returns amount of unique URl's
function counterize_getuniqueurl()
{
	global $wpdb;
	$sql = "SELECT COUNT( 1 ) "
		. " FROM `" . counterize_refererTable() . "`";
	return $wpdb->get_var( $sql );
}

function counterize_return_first_hit( $dateformat = "j/n-Y" )
{
	global $wpdb;
	$sql = "SELECT MIN(`timestamp`) "
		. " FROM `" . counterize_logTable() . "`";
	$t = $wpdb->get_var( $sql );
	return date( $dateformat, strtotime( $t ) );
}




















/*
 * Retourne un tableau contenant le domaine et les mots clés
 */
function counterize_ref_analyzer( $referer )
{
	if( !empty( $referer ) && $referer != "unknown" )
	{
		$domain = explode( '/', $referer );

		$source = array(
			array( 'google'   , 'q' ),
			array( 'bing'     , 'q' ),
			array( 'yahoo'    , array( 'p', 'q') ),
			array( 'msn'      , 'q' ),
			array( 'ask'      , 'q' ),
			array( 'baidu'    , 'wd' ),
			array( 'aol'      , array( 'query', 'encquery', 'q' ) ),
			array( 'alltheweb', 'q' ),
			array( 'altavista', 'q' ),
			array( 'excite'   , 'search' ),
			array( 'hotbot'   , 'query' ),
			array( 'lycos'    , 'query' ),
			array( 'live'     , 'q' ),
			array( 't-online' , 'q' ),
			array( 'netscape' , 'query' ),
			array( 'daum'     , 'q' ),
			array( 'eniro'    , 'search_word' ),
			array( 'pchome'   , 'q' ),
			array( 'cnn'      , 'query' ),
			array( 'about'    , 'terms' ),
			array( 'mamma'    , array( 'q', 'query' ) ),
			array( 'voila'    , 'rdata' ),
			array( 'virgilio' , 'qs' ),
			array( 'alice'    , 'qs' ),
			array( 'najdi'    , 'q' ),
			array( 'seznam'   , 'q' ),
			array( 'search'   , 'q' ),
			array( 'wp'       , 'szukaj' ),
			array( 'onet'     , 'qt' ),
			array( 'szukacz'  , 'q' ),
			array( 'yam'      , 'k' ),
			array( 'kvasir'   , 'q' ),
			array( 'sesam'    , 'q' ),
			array( 'ozu'      , 'q' ),
			array( 'terra'    , 'query' ),
			array( 'mynet'    , 'q' ),
			array( 'ekolay'   , 'q' ),
			array( 'rambler'  , 'query' ),
			array( 'yandex'   , 'text' )
		);

		$keyword = '';
		$output = array();
		for( $i = 0, $nb = count( $source ); $i < $nb; $i++ )
		{
			if( preg_match( '#' . $source[$i][0] . '#i', $referer ) )
			{
				$parse = parse_url( $referer );
				if( ! isset( $parse[ 'query' ] ) )
				{
					break;
				}
				parse_str( $parse[ 'query' ], $output );
				if( is_array( $source[$i][1] ) )
				{
					foreach( $source[$i][1] as $q )
					{
						if( isset( $output[$q] ) )
						{
							$keyword = $output[$q];
						}
						break 2;
					}
				}
				elseif( ! empty( $output[ $source[$i][1] ] ) )
				{
					$keyword = $output[ $source[$i][1] ];
				}
				break;
			}
		}

		return array( 'domain' => str_replace( 'www.', '', $domain[2] ), 'keyword' => strtolower( $keyword ) );
	}
	else
	{
		return array( 'domain' => '', 'keyword' => '' );
	}
}

// Returns amount of unique hits today
function counterize_getuniquehitstoday()
{
	global $wpdb;
	$today = date( 'Y-m-d' );
	$sql = "SELECT COUNT( DISTINCT `haship` ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `timestamp` >= '{$today}'";
	return $wpdb->get_var( $sql );
}

// Returns amount of hits today, from the visiting IP
function counterize_gethitstodayfromcurrentip()
{
	global $wpdb;
	$today = date( 'Y-m-d' );
	$sql = $wpdb->prepare
	(
		"SELECT COUNT( 1 ) "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `timestamp` >= %s "
		. " AND `IP` = %s ;",
		$today,
		$_SERVER['REMOTE_ADDR']
	);
	return $wpdb->get_var( $sql );
}

//similar to WordPress's check_admin_referer but does not die if check failed
function counterize_check_admin_referer( $action = -1, $query_arg = '_wpnonce' )
{
	$adminurl = strtolower( admin_url() );
	$referer = strtolower( wp_get_referer() );
	$result = isset( $_REQUEST[$query_arg] ) ? wp_verify_nonce( $_REQUEST[$query_arg], $action ) : false;
	if( ! $result && ! ( -1 == $action && strpos( $referer, $adminurl ) === 0 ) )
	{
		$result = false;
	}
	return $result;
}

/*
 * Returns an array containing the SQL to fetch information matching ID in DB, the number of entries and the number of pages.
 * This function is mostly used by the History.
 * It will take care of all the filters defined in the History.
 * Do not modify if you don't know what you do.
 */
function counterize_getentries_sql( $amount = 50, $entryID = null )
{
	global $wpdb;
	$limit = 50;
	$offset = 0;

	if( ! is_numeric( $amount ) || $amount == 0 )
	{
		$amount = 50;
	}

	//check if ipfilter is set
	if( ! empty( $_POST['ipfilter'] ) )
	{
		$ipfilter = str_replace( '%', '%%', $_POST['ipfilter'] );
	}
	elseif( ! empty( $_GET['ipfilter'] ) )
	{
		$ipfilter = str_replace( '%', '%%', $_GET['ipfilter'] );
	}

	//check if countryfilter is set
	if( ! empty( $_POST['countryfilter'] ) )
	{
		$countryfilter = str_replace( '%', '%%', $_POST['countryfilter'] );
	}
	elseif( ! empty( $_GET['countryfilter'] ) )
	{
		$countryfilter = str_replace( '%', '%%', $_GET['countryfilter'] );
	}

	//check if urifilter is set
	if( ! empty( $_POST['urifilter'] ) )
	{
		$urifilter = str_replace( '%', '%%', $_POST['urifilter'] );
	}
	elseif( ! empty( $_GET['urifilter'] ) )
	{
		$urifilter = str_replace( '%', '%%', $_GET['urifilter'] );
	}

	//check if refererfilter is set
	if( ! empty( $_POST['refererfilter'] ) )
	{
		$refererfilter = str_replace( '%', '%%', $_POST['refererfilter'] );
	}
	elseif( ! empty( $_GET['refererfilter'] ) )
	{
		$refererfilter = str_replace( '%', '%%', $_GET['refererfilter'] );
	}

	//check if agentfilter is set
	if( ! empty( $_POST['agentfilter'] ) )
	{
		$agentfilter = str_replace( '%', '%%', $_POST['agentfilter'] );
	}
	elseif( ! empty( $_GET['agentfilter'] ) )
	{
		$agentfilter = str_replace( '%', '%%', $_GET['agentfilter'] );
	}

	//check if keywordfilter is set
	if( ! empty( $_POST['keywordfilter'] ) )
	{
		$keywordfilter = str_replace( '%', '%%', $_POST['keywordfilter'] );
	}
	elseif( ! empty( $_GET['keywordfilter'] ) )
	{
		$keywordfilter = str_replace( '%', '%%', $_GET['keywordfilter'] );
	}

	//check if datefilter1 and datefilter2 are set
	if( ! empty( $_POST['datefilter1'] ) && ! empty( $_POST['datefilter2'] ) )
	{
		$datefilter1 = $_POST['datefilter1'];
		$datefilter2 = $_POST['datefilter2'];
	}
	elseif( ! empty( $_GET['datefilter1'] ) && ! empty( $_GET['datefilter2'] )  )
	{
		$datefilter1 = $_GET['datefilter1'];
		$datefilter2 = $_GET['datefilter2'];
	}

	//check if filtertype is set
	if( ! empty( $_POST['filtertype'] ) )
	{
		$filtertype = $_POST['filtertype'];
	}
	elseif( ! empty( $_GET['filtertype'] ) )
	{
		$filtertype = $_GET['filtertype'];
	}

	//check if counterize_gotopage is set
	if( ! empty( $_POST['counterize_gotopage'] ) )
	{
		$counterize_gotopage = $_POST['counterize_gotopage'];
	}
	elseif( ! empty( $_GET['counterize_gotopage'] ) )
	{
		$counterize_gotopage = $_GET['counterize_gotopage'];
	}





	//generate the SQL query
	$sql_items = "SELECT `id`, `IP`, `haship`, `timestamp`, p.url AS url, r.name AS referer, ua.name AS useragent, c.code AS countrycode, "
		. " m.refererID, m.agentID, m.pageID, m.countryID, k.keyword, k.keywordID \n";
	$sql_count = "SELECT COUNT( `id` ) \n";

	$sql =	"FROM `" . counterize_logTable() . "` m \n"
		.	"JOIN `" . counterize_pageTable()    . "` p ON m.`pageID` = p.`pageID` \n"
		.	"JOIN `" . counterize_agentsTable()  . "` ua ON m.`agentID` = ua.`agentID` \n"
		.	"JOIN `" . counterize_countryTable() . "` c ON m.`countryID` = c.`countryID` \n"
		.	"JOIN `" . counterize_refererTable() . "` r ON m.`refererID` = r.`refererID` \n"
		.	"JOIN `" . counterize_keywordTable() . "` k ON k.`keywordID` = r.`keywordID` \n";

	$where = false;
	$comparator = '=';
	if
	(
		isset( $filtertype )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		if( $filtertype === 'ex' )
		{
			$comparator = '<>';
		}
		elseif( $filtertype === 'like' )
		{
			$comparator = 'LIKE';
		}
		elseif( $filtertype === 'notlike' )
		{
			$comparator = 'NOT LIKE';
		}
	}

	//if an IP filter is set
	if
	(
		isset( $ipfilter )
		&&
		(
			counterize_check_admin_referer( 'action_ip_filter' )
		||	counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$filters = explode( '||', $ipfilter );
		$first = true;
		foreach( $filters as $filter )
		{
			if( $first )
			{
				$first = false;
				$sql .= " m.`IP` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
			else
			{
				$logical = 'OR';
				if( $comparator == '<>' )
				{
					$logical = 'AND';
				}
				$sql .= " {$logical} m.`IP` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
		}
		unset( $filters );
		$sql .= ") \n";
	}

	//if a country filter is set
	if
	(
		isset( $countryfilter )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_country_filter' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$filters = explode( '||', $countryfilter );
		$first = true;
		foreach( $filters as $filter )
		{
			if( $first )
			{
				$first = false;
				$sql .= " c.`code` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
			else
			{
				$logical = 'OR';
				if( $comparator == '<>' )
				{
					$logical = 'AND';
				}
				$sql .= " {$logical} c.`code` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
		}
		unset( $filters );
		$sql .= ") \n";
	}

	//if an URL filter is set
	if
	(
		isset( $urifilter )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_uri_filter' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$filters = explode( '||', $urifilter );
		$first = true;
		foreach( $filters as $filter )
		{
			if( $first )
			{
				$first = false;
				$sql .= " p.`url` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
			else
			{
				$logical = 'OR';
				if( $comparator == '<>' )
				{
					$logical = 'AND';
				}
				$sql .= " {$logical} p.`url` {$comparator} " . $wpdb->prepare( "%s", $filter ) . " ";
			}
		}
		unset( $filters );
		$sql .= ") \n";
	}

	//if a referer filter is set
	if
	(
		isset( $refererfilter )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_referer_filter' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$filters = explode( '||', $refererfilter );
		$first = true;
		foreach( $filters as $filter )
		{
			if( $first )
			{
				$first = false;
				$sql .= " r.`name` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
			else
			{
				$logical = 'OR';
				if( $comparator == '<>' )
				{
					$logical = 'AND';
				}
				$sql .= " {$logical} r.`name` {$comparator} " . $wpdb->prepare( "%s", $filter ) . " ";
			}
		}
		unset( $filters );
		$sql .= ") \n";
	}

	//if an user-agent filter is set
	if
	(
		isset( $agentfilter )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_agent_filter' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$filters = explode( '||', $agentfilter );
		$first = true;
		foreach( $filters as $filter )
		{
			if( $first )
			{
				$first = false;
				$sql .= " ua.`name` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
			else
			{
				$logical = 'OR';
				if( $comparator == '<>' )
				{
					$logical = 'AND';
				}
				$sql .= " {$logical} ua.`name` {$comparator} " . $wpdb->prepare( "%s", $filter ) . " ";
			}
		}
		unset( $filters );
		$sql .= ") \n";
	}

	//if a keyword filter is set
	if
	(
		isset( $keywordfilter )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_keyword_filter' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$filters = explode( '||', $keywordfilter );
		$first = true;
		foreach( $filters as $filter )
		{
			if( $first )
			{
				$first = false;
				$sql .= " k.`keyword` {$comparator} " . $wpdb->prepare( "%s", $filter ) ." ";
			}
			else
			{
				$logical = 'OR';
				if( $comparator == '<>' )
				{
					$logical = 'AND';
				}
				$sql .= " {$logical} k.`keyword` {$comparator} " . $wpdb->prepare( "%s", $filter ) . " ";
			}
		}
		unset( $filters );
		$sql .= " ) \n";
	}
	unset( $comparator );

	//if datefilter1 and datefilter2 filters are set
	if
	(
		isset( $datefilter1 ) && isset( $datefilter2 )
		&&
		(
			counterize_check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$clause = $where === FALSE ? 'WHERE (' : 'AND (';
		$where = TRUE;
		$sql .= $clause;
		$sql .= " m.`timestamp` BETWEEN " . $wpdb->prepare( "%s", $datefilter1 ) . " AND " . $wpdb->prepare( "%s", $datefilter2 ) . " ) \n";
	}

	//if an entryID is set
	if( isset( $entryID ) )
	{
		$clause = $where === FALSE ? 'WHERE' : 'AND';
		$where = TRUE;
		$sql .= "{$clause} m.`id` = " . $wpdb->prepare( "%s", $entryID ) . " \n";
	}

	$sql_items .= $sql;
	$sql_count .= $sql;

	$sql_items .= "ORDER BY m.`timestamp` DESC";





	//get the number of entries and compute the number of pages
	$nbentries = intval( $wpdb->get_var( $sql_count ) );
	unset( $sql_count );
	if( $amount <= 0 )
	{
		$amount = 50;
	}
	$lastpage = ceil( $nbentries / $amount );

	if
	(
		isset( $counterize_gotopage )
		&&	is_numeric( $counterize_gotopage )
		&&
		(
			counterize_check_admin_referer( 'counterize_history_nav', 'counterize_history_nav_field' )
		||	counterize_check_admin_referer( 'action_nav_link' )
		)
	)
	{
		$currentpage = intval( $counterize_gotopage );
	}
	else
	{
		$currentpage = 1;
	}
	if( $currentpage < 1 )
	{
		$currentpage = 1;
	}
	elseif( $currentpage > $lastpage )
	{
		$currentpage = $lastpage;
	}



	//add the LIMIT boundaries
	if( is_numeric( $amount ) )
	{
		if( intval( $amount ) > 0 )
		{
			$limit = intval( $amount );
		}
	}
	$offset = $limit * ( $currentpage - 1 );
	if( $offset < 0 )
	{
		//This case only happens when the database is empty
		$offset = 0;
	}
	$sql_items .= " LIMIT {$offset}, {$limit}";


	return array( $sql_items, $nbentries, $lastpage );
}


/*
 * Outputs some HTML in the head tag
 */
function counterize_header_callback()
{
	// adds some conditional comments for IE6-9 gradients support
	if( isset( $_SERVER['HTTP_USER_AGENT'] ) )
	{
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		list
		(
			$browser_name, $browser_code, $browser_ver, $browser_url,
			$os_name, $os_code, $os_ver, $os_url,
			$pda_name, $pda_code, $pda_ver, $pda_url
		) = counterize_detect_browser( $useragent );

		// if browser is IE, we need to fix it...
		if( $browser_code == 'ie' )
		{
			?>

			<!-- Added by Counterize for IE6-8 gradients support -->
			<!--[if (gte IE 6)&(lte IE 8)]>
				<style type="text/css">
					/* IE6-8 filters */
					.counterize_chart_bar_horizontal.blue {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#003399', endColorstr='#006699',GradientType=1 );
					}
					.counterize_chart_bar_vertical.blue {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#006699', endColorstr='#003399',GradientType=0 );
					}

					.counterize_chart_bar_horizontal.red {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cc0000', endColorstr='#ff0000',GradientType=1 );
					}
					.counterize_chart_bar_vertical.red {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ff0000', endColorstr='#cc0000',GradientType=0 );
					}

					.counterize_chart_bar_horizontal.green {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#009900', endColorstr='#00dc00',GradientType=1 );
					}
					.counterize_chart_bar_vertical.green {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00dc00', endColorstr='#009900',GradientType=0 );
					}

					.counterize_chart_bar_horizontal.yellow {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cccc00', endColorstr='#ffff00',GradientType=1 );
					}
					.counterize_chart_bar_vertical.yellow {
						filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffff00', endColorstr='#cccc00',GradientType=0 );
					}
				</style>
			<![endif]-->

			<!-- Added by Counterize for IE9+ gradients support -->
			<!--[if IE 9]>
				<style type="text/css">
					/* IE9 SVG */
					.counterize_chart_bar_horizontal.blue {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzAwMzM5OSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMwMDY2OTkiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}
					.counterize_chart_bar_vertical.blue {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzAwNjY5OSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMwMDMzOTkiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}

					.counterize_chart_bar_horizontal.red {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2NjMDAwMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNmZjAwMDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}
					.counterize_chart_bar_vertical.red {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZmMDAwMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNjYzAwMDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}


					.counterize_chart_bar_horizontal.green {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzAwOTkwMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMwMGRjMDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}
					.counterize_chart_bar_vertical.green {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzAwZGMwMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMwMDk5MDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}


					.counterize_chart_bar_horizontal.yellow {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2NjY2MwMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNmZmZmMDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}
					.counterize_chart_bar_vertical.yellow {
						background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZmZmYwMCIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNjY2NjMDAiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
					}

				</style>
			<![endif]-->

			<?php
		}
	}
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * =========================================================================== *
 * Insert new data into the database.                                          *
 * Only insert unfiltered data.                                                *
 * Only insert if not a bot.                                                   *
 * Get all the informations from the $_SERVER array.                           *
 * =========================================================================== *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */
function counterize_add_callback()
{
	global $wpdb;
	global $user_ID;

	// Set to unknown, if we're unable to extract information below.
	$referer = $remoteaddr = $useragent = $requesturl = 'unknown';

	if( isset( $_SERVER['REMOTE_ADDR'] ) )
	{
		$remoteaddr = apply_filters( 'counterize_server_remote_addr', $_SERVER['REMOTE_ADDR'] );
	}
	if( isset( $_SERVER['HTTP_USER_AGENT'] ) )
	{
		$useragent = apply_filters( 'counterize_server_http_user_agent', $_SERVER['HTTP_USER_AGENT'] );
	}
	if( isset( $_SERVER['REQUEST_URI'] ) )
	{
		$requesturl = apply_filters( 'counterize_server_request_uri', $_SERVER['REQUEST_URI'] );
	}
	if( isset( $_SERVER['HTTP_REFERER'] ) )
	{
		$referer = apply_filters( 'counterize_server_referer', $_SERVER['HTTP_REFERER'] );
	}

	$this_url = apply_filters( 'counterize_server_this_url', 'http://' . $_SERVER['HTTP_HOST'] . $requesturl );
	// Check to see if we really want to insert the entry...

	$checkval = 0;

	//
	// Bots detected and excluded if necessary
	//
	$logbots = counterize_get_option( 'logbots' );
	if( $logbots != '1' )
	{
		$botarray = array();
		//load the bot array
		if( file_exists( COUNTERIZE_PLUGIN_DIR . '/botlist.txt' ) )
		{
			$botarray = apply_filters( 'counterize_bot_array', file( COUNTERIZE_PLUGIN_DIR . '/botlist.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES ) );
		}
		//load the user bot file
		if( file_exists( COUNTERIZE_PLUGIN_DIR . '/user_botlist.txt' ) )
		{
			$botarray = $botarray + file( COUNTERIZE_PLUGIN_DIR . '/user_botlist.txt', FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES );
		}

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
				if( isset( $temp[1] ) && isset( $temp[2] ) )
				{
					$page = $temp[1];
					$refer = $temp[2];
					if( $refer == '%HTTP_HOST%' )
					{
						$refer = 'http://' . $_SERVER['HTTP_HOST'] . '/';
					}
				}
				else
				{
					//by security
					$is_complex = false;
				}
				unset( $temp );
				$condition = stristr( $useragent, $bot ) !== FALSE;
			}
			elseif( $is_regexp )
			{
				$bot = str_replace( 'regexp:', '', $entry );
				$refer = '';
				$page = '';
				$condition = preg_match( $bot, $useragent );
			}
			else
			{
				$bot = $entry;
				$refer = '';
				$page = '';
				$condition = stristr( $useragent, $bot ) !== FALSE;
			}

			//if we found a bot then
			if( $condition )
			{
				//if we have a complex filter
				if( $is_complex && !empty( $refer ) && !empty( $page ) )
				{
					if( $page != '*' )
					{
						if( $refer == $referer && $requesturl == $page )
						{
							$checkval = 1;
						}
					}
					else
					{
						if( $refer == $referer )
						{
							$checkval = 1;
						}
					}
				}
				else
				{
					$checkval = 1;
				}
				break;
			}
		}
	}

	// From SHRIKEE, don't count RSS and other stuff...
	// Exclude files from being counted
	if( $checkval == 0 )
	{
		// Exclude if referer is the same as url
		if( $this_url == $referer )
		{
			$checkval = 1;
		}

		// Exclude RSS feeds (Both with and without permalinks)
		// Stating just feed would make it impossible to name a page or post 'feed'
		/* if( stristr( $requesturl, "feed/" ) )
		$checkval = 1;
		if( stristr( $requesturl, "feed=" ) )
		$checkval = 1;
		*/
		// Exclude files which browsers like safari and opera request on each page
		if( stristr( $requesturl, 'robots.txt' ) )
		{
			$checkval = 1;
		}
		elseif( stristr( $requesturl, 'favicon.ico' ) )
		{
			$checkval = 1;
		}

		// Exclude any admin or core files
		elseif( stristr( $requesturl, 'wp-includes/' ) )
		{
			$checkval = 1;
		}
		elseif( stristr( $requesturl, 'wp-admin/' ) )
		{
			$checkval = 1;
		}
		elseif( stristr( $requesturl, 'wp-content/' ) )
		{
			$checkval = 1;
		}
	}

	// more extensions?
	elseif( stristr( $requesturl, '.jpg' ) )
	{
		$checkval = 1;
	}
	elseif( stristr( $requesturl, '.bmp' ) )
	{
		$checkval = 1;
	}
	elseif( stristr( $requesturl, '.png' ) )
	{
		$checkval = 1;
	}
	elseif( stristr( $requesturl, '.gif' ) )
	{
		$checkval = 1;
	}


	// If not found anything unwanted yet, check to see if it's on the excludelist...
	if( $checkval == 0 )
	{
		$excludelist = counterize_get_option( 'excludedip' );

		$tmp_array = explode( ',', $excludelist );
		$count = count( $tmp_array );

		if( $excludelist != '' && $excludelist != ' ' )
		{
			for( $i = 0; $i < $count; $i++ )
			{
				if( strpos( $remoteaddr, $tmp_array[$i] ) !== FALSE )
				{
					// IP found on exclude-list - we don't want it!
					$checkval = 1;
				}
			}
		}
	}

	// DISABLED: This functionality is replaced with the admin functionality
	//			to enable/disable counting of certain users...
	//
	// let's check it is a logged in user.
	// If he's logged in, we don't count him.
	if( $checkval == 0 )
	{
		$excluded_users = explode( ',', counterize_get_option( 'excludedusers' ) );
		get_currentuserinfo();
		$tmp = count( $excluded_users );
		if( $user_ID != '' && $user_ID != ' ' && in_array( $user_ID, $excluded_users ) )
		{
			$checkval = 1;
		}
	}

	//let plugins add more conditions
	$checkval = apply_filters( 'counterize_check_insert_into_database', $checkval );

	// If checkval is still 0, then yes - we want to insert it...
	if( $checkval == 0 )
	{
		do_action( 'counterize_before_insert_into_database' );

		// Replace %20's(spaces) in strings with a white-space
		// Man, someone should create a better checking-module... *sigh*
		$requesturl = str_replace( '%20', ' ', $requesturl );
		$referer = str_replace( '%20', ' ', $referer );
		$timestamp = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 3600  ) ); // 3600 = 60 * 60

		$can_collect_ip = intval( counterize_get_option( 'enableip' ) );
		$country_code = counterize_iptocountrycode( $remoteaddr );

		$agentID   = counterize_getUserAgentID( $useragent    );
		$pageID    = counterize_getPageID     ( $requesturl   );
		$keywordID = counterize_getKeywordID  ( $referer      );
		$refererID = counterize_getRefererID  ( $referer      );
		$countryID = counterize_getCountryID  ( $country_code );

		$sql = "INSERT IGNORE INTO `" . counterize_logTable() . "` "
			. " (`IP`, `haship`, `timestamp`, `pageID`, `refererID`, `agentID`, `countryID`) "
			. " VALUES ( %s, %s, %s, %d, %d, %d, %d ) ;";
		$sql = $wpdb->prepare
		(
			$sql,
			( $can_collect_ip > 0 ? $remoteaddr : 'unavailable' ),
			substr( md5( $remoteaddr ), 1, 16 ),
			$timestamp,
			$pageID,
			$refererID,
			$agentID,
			$countryID
		);

		$results = $wpdb->query( $sql );
		$_SESSION['counterize_log_id'] = $wpdb->insert_id;

		counterize_AddUserAgentVisit( $agentID   );
		counterize_AddPageVisit     ( $pageID    );
		counterize_AddKeywordVisit  ( $keywordID );
		counterize_AddRefererVisit  ( $refererID );
		counterize_AddCountryVisit  ( $countryID );

		do_action( 'counterize_after_insert_into_database' );
	}
}

//Add the outlink into the database
function counterize_add_outlink( $outlink, $from )
{
	global $wpdb;

	//we insert and update the outlink record
	$outlinkID = counterize_getOutlinkID( $outlink );
	counterize_AddOutlinkVisit( $outlinkID );

	//we update the log record
	$sql = 'UPDATE `' . counterize_logTable() . '` '
		. ' SET `outlinkID` = %d '
		. ' WHERE id = %d ;';
	if( ! empty( $_SESSION['counterize_log_id'] ) && is_numeric( $_SESSION['counterize_log_id'] ) )
	{
		$sql = $wpdb->prepare( $sql, $outlinkID, intval( $_SESSION['counterize_log_id'] ) );
		$wpdb->query( $sql );
		unset( $_SESSION['counterize_log_id'] );
	}
}

// gives the useragentID back
function counterize_getUserAgentID( $useragent )
{
	global $wpdb;
	$sql = $wpdb->prepare
	(
		"SELECT `agentID` "
		. " FROM `" . counterize_agentsTable() . "` "
		. " WHERE `name` = %s",
		$useragent
	);

	$agentID = $wpdb->get_var( $sql );

	if( is_null( $agentID ) )
	{
		// creates a new agent
		list
		(
			$browser_name, $browser_code, $browser_ver, $browser_url,
			$os_name, $os_code, $os_ver, $os_url,
			$pda_name, $pda_code, $pda_ver, $pda_url
		) = counterize_detect_browser( $useragent );

		$sql = $wpdb->prepare
		(
			"INSERT IGNORE INTO `" . counterize_agentsTable() . "` "
			. " (`name`, `count`, `browserName`, `browserCode`, `browserVersion`, `browserURL`, `osName`, `osCode`, `osVersion`, `osURL`) "
			. " VALUES ( %s, 0, %s, %s, %s, %s, %s, %s, %s, %s ) ;",
			$useragent,
			$browser_name,
			$browser_code,
			$browser_ver,
			$browser_url,
			$os_name,
			$os_code,
			$os_ver,
			$os_url
		);
		$wpdb->query( $sql );
		return $wpdb->insert_id;
	}
	return $agentID;
}

function counterize_AddUserAgentVisit( $agentID )
{
	global $wpdb;
	if( is_numeric( $agentID ) )
	{
		$sql = $wpdb->prepare
		(
			"UPDATE `" . counterize_agentsTable() . "` "
			. " SET `count` = `count` + 1 "
			. " WHERE `agentID` = %d ;",
			$agentID
		);
		$wpdb->query( $sql );
	}
}









// gives the countryID back
function counterize_getCountryID( $code )
{
	global $wpdb;
	$sql = $wpdb->prepare
	(
		"SELECT `countryID` "
		. " FROM `" . counterize_countryTable() . "`  "
		. " WHERE `code` = %s ;",
		$code
	);
	$countryID = $wpdb->get_var( $sql );

	if( is_null( $countryID ) )
	{
		// creates a new country
		$sql = $wpdb->prepare
		(
			"INSERT IGNORE INTO `" . counterize_countryTable() . "` "
			. " (`code`, `count`) VALUES ( '%s', 0 ) ;",
			$code
		);
		$wpdb->query( $sql );
		return $wpdb->insert_id;
	}
	return $countryID;
}

function counterize_AddCountryVisit( $countryID )
{
	global $wpdb;
	if( is_numeric( $countryID ) )
	{
		$sql = $wpdb->prepare
		(
			"UPDATE `" . counterize_countryTable() . "` "
			. " SET `count` = `count` + 1 "
			. " WHERE `countryID` = %d ;",
			$countryID
		);
		$wpdb->query( $sql );
	}
}





// gives the keywordID back
function counterize_getKeywordID( $referer )
{
	global $wpdb;
	$ref = counterize_ref_analyzer( $referer );
	$sql = $wpdb->prepare
	(
		"SELECT `keywordID` "
		. " FROM `" . counterize_keywordTable() . "` "
		. " WHERE `keyword` = %s ;",
		$ref['keyword']
	);
	$keywordID = $wpdb->get_var( $sql );

	if( is_null( $keywordID )  )
	{
		// create new keyword
		$sql = $wpdb->prepare
		(
			"INSERT IGNORE INTO `" . counterize_keywordTable() . "` "
			. " (`keyword`, `count`) "
			. " VALUES ( %s, 0 ) ;",
			$ref['keyword']
		);
		$wpdb->query( $sql );
		return $wpdb->insert_id;
	}
	return $keywordID;
}


function counterize_AddKeywordVisit( $keywordID )
{
	global $wpdb;
	if( is_numeric( $keywordID ) )
	{
		$sql = $wpdb->prepare
		(
			"UPDATE `" . counterize_keywordTable() . "` "
			. " SET `count` = `count` + 1 "
			. " WHERE `keywordID` = %d ;",
			$keywordID
		);
		$wpdb->query( $sql );
	}
}

// gives the pageID back
function counterize_getPageID( $url )
{
	global $wpdb;
	global $post;
	$sql = $wpdb->prepare
	(
		"SELECT `pageID` "
		. " FROM `" . counterize_pageTable() . "` "
		. " WHERE `url` = %s ;",
		$url
	);
	$pageID = $wpdb->get_var( $sql );

	if( is_null( $pageID ) )
	{
		//search for an eventual post ID
		$post_id = ( ( is_single() || $post->post_type == 'page' ) ? $post->ID : 'NULL' );
		if( $post_id == 'NULL' )
		{
			$post_id = url_to_postid( $url );
			if( $post_id == 0 )
			{
				$post_id = 'NULL';
			}
		}

		// create a new page
		$sql = $wpdb->prepare
		(
			"INSERT IGNORE INTO `" . counterize_pageTable() . "` "
			. " ( `url`, `count`, `postID` ) "
			. " VALUES ( %s, 0, %d ) ;",
			$url,
			$post_id
		);
		$wpdb->query( $sql );
		return $wpdb->insert_id;
	}
	return $pageID;
}

function counterize_AddPageVisit( $pageID )
{
	global $wpdb;
	if( is_numeric( $pageID ) )
	{
		$sql = $wpdb->prepare
		(
			"UPDATE `" . counterize_pageTable() . "` "
			. " SET `count` = `count` + 1 "
			. " WHERE `pageID` = %d ;",
			$pageID
		);
		$wpdb->query( $sql );
	}
}



// gives the refererID back
function counterize_getRefererID( $referer )
{
	global $wpdb;
	$sql = $wpdb->prepare
	(
		"SELECT `refererID` "
		. " FROM `" . counterize_refererTable() . "`"
		. " WHERE `name` = %s ;",
		$referer
	);
	$refererID = $wpdb->get_var( $sql );

	if( is_null( $refererID ) )
	{
		$keywordID = counterize_getKeywordID( $referer );
		// create new referer
		$sql = $wpdb->prepare
		(
			"INSERT IGNORE INTO `" . counterize_refererTable() . "`"
			. " (`name`, `count`, `keywordID`) "
			. " VALUES ( %s, 0, %d ) ;",
			$referer,
			$keywordID
		);
		$wpdb->query( $sql );
		return $wpdb->insert_id;
	}
	return $refererID;
}

function counterize_AddRefererVisit( $refererID )
{
	global $wpdb;
	if( is_numeric( $refererID ) )
	{
		$sql = $wpdb->prepare
		(
			"UPDATE `" . counterize_refererTable() . "`"
			. " SET `count` = `count` + 1 "
			. " WHERE `refererID` = %d ;",
			$refererID
		);
		$wpdb->query( $sql );
	}
}


// gives the outlinkID back
function counterize_getOutlinkID( $outlink )
{
	global $wpdb;
	$sql = $wpdb->prepare
	(
		"SELECT `outlinkID` "
		. " FROM `" . counterize_outlinksTable() . "`"
		. " WHERE `url` = %s ;",
		$outlink
	);
	$outlinkID = $wpdb->get_var( $sql );

	if( is_null( $outlinkID ) )
	{
		// create new outlink record
		$sql = $wpdb->prepare
		(
			"INSERT IGNORE INTO `" . counterize_outlinksTable() . "`"
			. " (`count`, `url`) "
			. " VALUES ( 0, %s ) ;",
			$outlink
		);
		$wpdb->query( $sql );
		return $wpdb->insert_id;
	}
	return $outlinkID;
}

function counterize_AddOutlinkVisit( $outlinkID )
{
	global $wpdb;
	if( is_numeric( $outlinkID ) )
	{
		$sql = $wpdb->prepare
		(
			"UPDATE `" . counterize_outlinksTable() . "` "
			. " SET `count` = `count` + 1 "
			. " WHERE `outlinkID` = %d ;",
			$outlinkID
		);
		$wpdb->query( $sql );
	}
}


function counterize_copyright()
{
	?>

	<p style="text-align: center">
		<small><?php
			_e( 'Statistics recorded with <a href="http://www.gabsoftware.com/products/scripts/counterize/" title="Counterize - Statistics-plugin for WordPress by GabSoftware">Counterize</a>', COUNTERIZE_TD );
			echo ' - ' . __( 'Version' ) . ' ' . counterize_get_version( 'major' ) . "." . counterize_get_version( 'minor' ) . "." . counterize_get_version( 'revision' );
		?></small>
	</p>

	<?php
}








/*
 * Add an invisible space every 5 characters into words of more than 10 characters
 * in order to make the string breakable when fitting in a small box
 */
function counterize_wordwrap( $text )
{
	$split = explode( ' ', $text );
	foreach( $split as $key=>$value )
	{
		if( strlen( $value ) > 10 )
		{
			//$split[$key] = chunk_split( $value, 5, '&#8203;' );
			$split[$key] = chunk_split( $value, 5, ' ' );
		}
	}
	return implode( ' ', $split );
}



/*
 *
 * Show the Counterize History.
 * Modify with caution.
 */
function counterize_show_history( $frompage = "counterize_dashboard", $directcall = false )
{
	global $wpdb;

	if( $directcall )
	{
		if( ! counterize_getamount() )
		{
			_e( "There's no data in the database - You can't see stats until you have data.", COUNTERIZE_TD );
			return;
		}
	}

	$howmany = __( 'Latest entries', COUNTERIZE_TD );

	$amount = counterize_get_option( 'amount' );
	if( $amount == '' || $amount == ' ' )
	{
		$amount = 50;
	}

	$howmany = "{$howmany} &#x202A;({$amount})&#x202C;";

	$url = "admin.php?page={$frompage}";

	//check if ipfilter is set
	if( ! empty( $_POST['ipfilter'] ) )
	{
		$ipfilter = htmlspecialchars( $_POST['ipfilter'] );
	}
	elseif( ! empty( $_GET['ipfilter'] ) )
	{
		$ipfilter = htmlspecialchars( $_GET['ipfilter'] );
	}

	//check if countryfilter is set
	if( ! empty( $_POST['countryfilter'] ) )
	{
		$countryfilter = htmlspecialchars( $_POST['countryfilter'] );
	}
	elseif( ! empty( $_GET['countryfilter'] ) )
	{
		$countryfilter = htmlspecialchars( $_GET['countryfilter'] );
	}

	//check if urifilter is set
	if( ! empty( $_POST['urifilter'] ) )
	{
		$urifilter = htmlspecialchars( $_POST['urifilter'] );
	}
	elseif( ! empty( $_GET['urifilter'] ) )
	{
		$urifilter = htmlspecialchars( $_GET['urifilter'] );
	}

	//check if refererfilter is set
	if( ! empty( $_POST['refererfilter'] ) )
	{
		$refererfilter = htmlspecialchars( $_POST['refererfilter'] );
	}
	elseif( ! empty( $_GET['refererfilter'] ) )
	{
		$refererfilter = htmlspecialchars( $_GET['refererfilter'] );
	}

	//check if agentfilter is set
	if( ! empty( $_POST['agentfilter'] ) )
	{
		$agentfilter = htmlspecialchars( $_POST['agentfilter'] );
	}
	elseif( ! empty( $_GET['agentfilter'] ) )
	{
		$agentfilter = htmlspecialchars( $_GET['agentfilter'] );
	}

	//check if keywordfilter is set
	if( ! empty( $_POST['keywordfilter'] ) )
	{
		$keywordfilter = htmlspecialchars( $_POST['keywordfilter'] );
	}
	elseif( ! empty( $_GET['keywordfilter'] ) )
	{
		$keywordfilter = htmlspecialchars( $_GET['keywordfilter'] );
	}

	//check if datefilter1 and datefilter2 are set
	if( ! empty( $_POST['datefilter1'] ) && ! empty( $_POST['datefilter2'] ) )
	{
		$datefilter1 = htmlspecialchars( $_POST['datefilter1'] );
		$datefilter2 = htmlspecialchars( $_POST['datefilter2'] );
	}
	elseif( ! empty( $_GET['keywordfilter'] ) && ! empty( $_GET['datefilter2'] ) )
	{
		$datefilter1 = htmlspecialchars( $_GET['datefilter1'] );
		$datefilter2 = htmlspecialchars( $_GET['datefilter2'] );
	}

	//check if filtertype is set
	if( ! empty( $_POST['filtertype'] ) )
	{
		$filtertype = htmlspecialchars( $_POST['filtertype'] );
	}
	elseif( ! empty( $_GET['filtertype'] ) )
	{
		$filtertype = htmlspecialchars( $_GET['filtertype'] );
	}

	//check if filterdebug is set
	if( ! empty( $_POST['filterdebug'] ) )
	{
		$filterdebug = htmlspecialchars( $_POST['filterdebug'] );
	}
	elseif( ! empty( $_GET['filterdebug'] ) )
	{
		$filterdebug = htmlspecialchars( $_GET['filterdebug'] );
	}

	//check if counterize_gotopage is set
	if( ! empty( $_POST['counterize_gotopage'] ) )
	{
		$counterize_gotopage = htmlspecialchars( $_POST['counterize_gotopage'] );
	}
	elseif( ! empty( $_GET['counterize_gotopage'] ) )
	{
		$counterize_gotopage = htmlspecialchars( $_GET['counterize_gotopage'] );
	}

	?>

	<div class="wrap" id="counterizehistorytop">

		<h2><?php _e( 'Filters', COUNTERIZE_TD ); ?></h2>

		<!-- Form for filters -->
		<form action="<?php echo $url; ?>" method="post" name="form_filter" id="form_filter">

			<fieldset>

				<legend><?php _e( 'Define one or more filters (you can separate entries with "||")', COUNTERIZE_TD ); ?></legend>

				<table summary="<?php _e( 'Filter fields', COUNTERIZE_TD ); ?>">
					<tr>
						<td>
							<label for="ipfilter"><?php _e( 'Filter for these IP addresses:', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="text" size="60" placeholder="<?php _e( 'Example: 10.20.30.%' , COUNTERIZE_TD ) ?>" name="ipfilter" id="ipfilter"<?php echo ( isset( $ipfilter ) ) ? ' value="' . $ipfilter . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear this field' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'ipfilter' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="countryfilter"><?php _e( 'Filter for these countries:', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="text" size="60" placeholder="<?php _e( 'ISO 3166-1 Alpha-2 country codes. Example: FR for France' , COUNTERIZE_TD ) ?>" name="countryfilter" id="countryfilter"<?php echo ( isset( $countryfilter ) ) ? ' value="' . $countryfilter . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear this field' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'countryfilter' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="urifilter"><?php _e( 'Filter for these URLs:', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="text" size="60" placeholder="<?php _e( 'Example: /2012/01/01/my-awesome-post' , COUNTERIZE_TD ) ?>" name="urifilter" id="urifilter"<?php echo ( isset( $urifilter ) ) ? ' value="' . $urifilter . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear this field' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'urifilter' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="refererfilter"><?php _e( 'Filter for these referers:', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="text" size="60" placeholder="<?php _e( 'Example: %google.com%' , COUNTERIZE_TD ) ?>" name="refererfilter" id="refererfilter"<?php echo ( isset( $refererfilter ) ) ? ' value="' . $refererfilter . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear this field' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'refererfilter' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="agentfilter"><?php _e( 'Filter for these user-agents:', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="text" size="60" placeholder="<?php _e( 'Example: %Firefox%' , COUNTERIZE_TD ) ?>" name="agentfilter" id="agentfilter"<?php echo ( isset( $agentfilter ) ) ? ' value="' . $agentfilter . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear this field' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'agentfilter' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="keywordfilter"><?php _e( 'Filter for these keywords', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="text" size="60" placeholder="<?php _e( 'A list of keywords' , COUNTERIZE_TD ) ?>" name="keywordfilter" id="keywordfilter"<?php echo ( isset( $keywordfilter ) ) ? ' value="' . $keywordfilter . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear this field' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'keywordfilter' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="datefilter1"><?php _e( 'Between', COUNTERIZE_TD ) ?></label>
						</td>
						<td>
							<input type="datetime-local" name="datefilter1" id="datefilter1" placeholder="<?php echo date( 'Y-m-d\TH:i:s', ( time() - 86400 ) ); ?>"<?php echo ( isset( $datefilter1 ) ) ? ' value="' . $datefilter1 . '"' : ''; ?> />
							<label for="datefilter2"><?php _e( ' and ', COUNTERIZE_TD ) ?></label>
							<input type="datetime-local" name="datefilter2" id="datefilter2" placeholder="<?php echo date( 'Y-m-d\TH:i:s' ); ?>"<?php echo ( isset( $datefilter2 ) ) ? ' value="' . $datefilter2 . '"' : ''; ?> />
						</td>
						<td>
							<input type="button" class="button-secondary" title="<?php _e( 'Click this button to clear these fields' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'datefilter1', 'datefilter2' ] );" />
						</td>
					</tr>

					<tr>
						<td>
							<label for="filterinclude"><?php _e( 'Include filter', COUNTERIZE_TD ) ?></label>
						</td>
						<td colspan="2">
							<input type="radio" name="filtertype" id="filterinclude" value="in"<?php echo ( ( ! isset( $filtertype ) ) || ( isset( $filtertype ) && $filtertype == 'in' ) ) ? ' checked="checked"' : ''; ?> />
							<span><?php _e( '(Matches everything included in the filters)' , COUNTERIZE_TD ) ?></span>
						</td>
					</tr>

					<tr>
						<td>
							<label for="filterexclude"><?php _e( 'Exclude filter', COUNTERIZE_TD ) ?></label>
						</td>
						<td colspan="2">
							<input type="radio" name="filtertype" id="filterexclude" value="ex"<?php echo ( isset( $filtertype ) && $filtertype == 'ex' ) ? ' checked="checked"' : ''; ?> />
							<span><?php _e( '(Matches everything not included in the filters)' , COUNTERIZE_TD ) ?></span>
						</td>
					</tr>

					<tr>
						<td>
							<label for="filterlike"><?php _e( 'LIKE filter', COUNTERIZE_TD ) ?></label>
						</td>
						<td colspan="2">
							<input type="radio" name="filtertype" id="filterlike" title="<?php _e( "Escape wildcards characters with '\' if they should not to be processed as wildcards characters." , COUNTERIZE_TD ) ?>" value="like"<?php echo ( isset( $filtertype ) && $filtertype == 'like' ) ? ' checked="checked"' : ''; ?> />
							<span><?php _e( "(Wildcard characters '%' and '_' are allowed)", COUNTERIZE_TD ); ?></span>
						</td>
					</tr>

					<tr>
						<td>
							<label for="filternotlike"><?php _e( 'NOT LIKE filter', COUNTERIZE_TD ) ?></label>
						</td>
						<td colspan="2">
							<input type="radio" name="filtertype" id="filternotlike" title="<?php _e( "Escape wildcards characters with '\' if they should not to be processed as wildcards characters." , COUNTERIZE_TD ) ?>" value="notlike"<?php echo ( isset( $filtertype ) && $filtertype == 'notlike' ) ? ' checked="checked"' : ''; ?> />
							<span><?php _e( "(Wildcard characters '%' and '_' are allowed)", COUNTERIZE_TD ); ?></span>
						</td>
					</tr>

					<tr>
						<td>
							<label for="filterdebug"><?php _e( 'Debug query', COUNTERIZE_TD ) ?></label>
						</td>
						<td colspan="2">
							<input type="checkbox" name="filterdebug" id="filterdebug" value="filterdebug"<?php echo ( isset( $filterdebug ) && $filterdebug == 'filterdebug' ) ? ' checked="checked"' : ''; ?> />
							<span><?php _e( '(Check this to see the generated SQL query)' , COUNTERIZE_TD ) ?></span>
						</td>
					</tr>

					<tr>
						<td colspan="3"><?php _e( 'Click on the <a href="#contextual-help-wrap">Help link</a> above to learn more about what you can do with filters.', COUNTERIZE_TD ); ?></td>
					</tr>

				</table>

				<input type="hidden" name="counterize_hidden_check" id="counterize_hidden_check" value="present" />

				<?php wp_nonce_field( 'counterize_filter_data', 'counterize_filter_data_field' ); ?>

				<input type="submit" class="button-primary" title="<?php _e( 'Click this button to submit your filters' , COUNTERIZE_TD ) ?>" name="counterize_filter_submit" id="counterize_filter_submit" value="<?php _e( 'Submit filter', COUNTERIZE_TD ); ?>" />
				<input type="button" class="button-primary" title="<?php _e( 'Click this button to clear ALL the filters' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Clear form', COUNTERIZE_TD ); ?>" onclick="javascript:counterize_clear_filter_form( [ 'ipfilter', 'urifilter', 'refererfilter', 'agentfilter', 'countryfilter' ] );" />
				<input type="reset" class="button-primary" title="<?php _e( 'Click this button to cancel any change made to filters' , COUNTERIZE_TD ) ?>" value="<?php _e( 'Cancel changes', COUNTERIZE_TD ); ?>" />
				<a href="admin.php?page=<?php echo $frompage; ?>" title="<?php _e( 'Click this button to reset the whole form (will refresh the page)' , COUNTERIZE_TD ) ?>" class="button-secondary"><?php _e( 'Reset filters', COUNTERIZE_TD ); ?></a>

			</fieldset>

		</form>
		<br />

		<h2><?php echo $howmany; ?></h2>

		<?php

		$entries_tmp = counterize_getentries_sql( $amount );

		if( isset( $filterdebug ) && $filterdebug == 'filterdebug' )
		{
			echo '<div id="filterdebug"><h3>Debug</h3><p><pre>' . wordwrap( htmlspecialchars( $entries_tmp[0] ), 80, "\n" ) . '</pre></p></div>';
		}

		$entries = $wpdb->get_results( $entries_tmp[0] );
		$nbentries = $entries_tmp[1];
		$lastpage = $entries_tmp[2];

		//compute the current page number
		if( isset( $counterize_gotopage ) && is_numeric( $counterize_gotopage ) )
		{
			$currentpage = intval( $counterize_gotopage );
		}
		else
		{
			$currentpage = 1;
		}
		if( $currentpage < 1 )
		{
			$currentpage = 1;
		}
		elseif( $currentpage > $lastpage )
		{
			$currentpage = $lastpage;
		}

		//generate the navigation links
		$nav_link = wp_nonce_url( $url, 'action_nav_link' );
		$nav_link .= ( isset( $ipfilter      ) ? '&amp;ipfilter='      . htmlspecialchars( $ipfilter      ) : '' );
		$nav_link .= ( isset( $countryfilter ) ? '&amp;countryfilter=' . htmlspecialchars( $countryfilter ) : '' );
		$nav_link .= ( isset( $urifilter     ) ? '&amp;urifilter='     . htmlspecialchars( $urifilter     ) : '' );
		$nav_link .= ( isset( $refererfilter ) ? '&amp;refererfilter=' . htmlspecialchars( $refererfilter ) : '' );
		$nav_link .= ( isset( $agentfilter   ) ? '&amp;agentfilter='   . htmlspecialchars( $agentfilter   ) : '' );
		$nav_link .= ( isset( $keywordfilter ) ? '&amp;keywordfilter=' . htmlspecialchars( $keywordfilter ) : '' );
		$nav_link .= ( isset( $filtertype    ) ? '&amp;filtertype='    . htmlspecialchars( $filtertype    ) : '' );
		$nav_frst_link = $nav_link . '&amp;counterize_gotopage=1';
		$nav_prev_link = $nav_link . '&amp;counterize_gotopage=' . ( $currentpage - 1 );
		$nav_next_link = $nav_link . '&amp;counterize_gotopage=' . ( $currentpage + 1 );
		$nav_last_link = $nav_link . '&amp;counterize_gotopage=' . ( $lastpage );

		if( ! empty( $_POST ) && isset( $_POST['counterize_gotopage'] ) )
		{
			if( ! check_admin_referer( 'counterize_history_nav', 'counterize_history_nav_field' ) )
			{
			   echo "<p>" . __( 'Nonce not verified. What are you trying to do?', COUNTERIZE_TD ) . "</p>";
			   exit;
			}
		}

		?>

		<!-- Navigation bar -->
		<div id="counterize_history_navigationbar">
			<ul>
				<?php if( $currentpage > 1 ): ?>
				<li><a href="<?php echo $nav_frst_link; ?>" class="button-secondary"><?php _e( '&laquo;&nbsp;First page', COUNTERIZE_TD ); ?></a></li>
				<li><a href="<?php echo $nav_prev_link; ?>" class="button-secondary"><?php _e( '&lsaquo;&nbsp;Previous page', COUNTERIZE_TD ); ?></a></li>
				<?php endif; ?>
				<li>
					<form action="<?php echo $url; ?>" method="post" name="counterize_form_nav" id="counterize_form_nav">
						<label for="counterize_gotopage"><?php _e( 'Go to page:', COUNTERIZE_TD ); ?></label>
						<input type="text" id="counterize_gotopage" name="counterize_gotopage" size="2" value="<?php echo $currentpage; ?>" />
						<span>&nbsp;/&nbsp;<?php echo $lastpage; ?></span>
						<?php wp_nonce_field( 'counterize_history_nav', 'counterize_history_nav_field' ); ?>
						<input type="submit" value="<?php _e( 'OK', COUNTERIZE_TD ); ?>" class="button-secondary" />
					</form>
				</li>
				<?php if( $currentpage < $lastpage ): ?>
				<li><a href="<?php echo $nav_next_link; ?>" class="button-secondary"><?php _e( 'Next page&nbsp;&rsaquo;', COUNTERIZE_TD ); ?></a></li>
				<li><a href="<?php echo $nav_last_link; ?>" class="button-secondary"><?php _e( 'Last page&nbsp;&raquo;', COUNTERIZE_TD ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

		<?php
			$killmass_url    = "{$url}&amp;killmass=yes";
			$killmass_link = wp_nonce_url( $killmass_url, "action_killmass" );
		?>

		<!-- History data begins here -->
		<form method="post" action="<?php echo $killmass_link; ?>" name="tablesForm" id="tablesForm">

			<table id="counterizehistorytable" width="100%" cellpadding="3" cellspacing="3" rules="rows" summary="<?php _e( 'History', COUNTERIZE_TD ); ?>">
				<thead>
					<tr class="alternate">
						<th style="width: 3%"><?php _e( 'Select',       COUNTERIZE_TD ); ?></th>
						<th style="width: 3%"><?php _e( 'ID',           COUNTERIZE_TD ); ?></th>
						<th style="width: 10%"><?php _e( 'IP',          COUNTERIZE_TD ); ?></th>
						<th style="width: 10%"><?php _e( 'Country',     COUNTERIZE_TD ); ?></th>
						<th style="width: 10%"><?php _e( 'Timestamps',  COUNTERIZE_TD ); ?></th>
						<th style="width: 20%"><?php _e( 'URL',         COUNTERIZE_TD ); ?></th>
						<th style="width: 20%"><?php _e( 'Referers',    COUNTERIZE_TD ); ?></th>
						<th style="width: 12%"><?php _e( 'User-agents', COUNTERIZE_TD ); ?></th>
						<th style="width: 10%"><?php _e( 'Keywords',    COUNTERIZE_TD ); ?></th>
						<th style="width: 2%"><?php _e( 'Kill',         COUNTERIZE_TD ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr class="alternate">
						<th><?php _e( 'Select',      COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'ID',          COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'IP',          COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Country',     COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Timestamps',  COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'URL',         COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Referers',    COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'User-agents', COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Keywords',    COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Kill',        COUNTERIZE_TD ); ?></th>
					</tr>
				</tfoot>
				<tbody>

				<?php


		//$url = "admin.php?page={$frompage}";

		$uri_filter_link     = wp_nonce_url( $url, 'action_uri_filter'     );
		$ip_filter_link      = wp_nonce_url( $url, 'action_ip_filter'      );
		$country_filter_link = wp_nonce_url( $url, 'action_country_filter' );
		$referer_filter_link = wp_nonce_url( $url, 'action_referer_filter' );
		$agent_filter_link   = wp_nonce_url( $url, 'action_agent_filter'   );
		$keyword_filter_link = wp_nonce_url( $url, 'action_agent_keyword'  );
		$uri_kill_link       = wp_nonce_url( $url, 'action_uri_kill'       );

		if( ! empty( $_POST ) && isset( $_POST['counterize_hidden_check'] ) )
		{
			if( ! check_admin_referer( 'counterize_filter_data', 'counterize_filter_data_field' ) )
			{
			   echo '<p>' . __( 'Nonce not verified. What are you trying to do?', COUNTERIZE_TD ) . '</p>';
			   exit;
			}
		}



		$i = 0;
		$offset = 0;
		$entrycounter = 1;
		foreach( $entries as $entry )
		{
			if( $i % 25 == 0 && $i > 0 )
			{
				$offset++;
				?>

				</tbody>
				<tbody>
					<tr class="alternate repeat">
						<th><?php _e( 'Select',      COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'ID',          COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'IP',          COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Country',     COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Timestamps',  COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'URL',         COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Referers',    COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'User-agents', COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Keywords',    COUNTERIZE_TD ); ?></th>
						<th><?php _e( 'Kill',        COUNTERIZE_TD ); ?></th>
					</tr>

				<?php
			}


			?>

					<tr <?php if( ( $i + $offset ) % 2 ) { echo 'class="alternate"'; } ?>>
						<td style="text-align: center">
							<?php echo $entrycounter; $entrycounter++; ?>
							<small>
								<input type="checkbox"
								name='counterize_killemall[<?php echo $entry->id; ?>]'
								value="<?php echo $entry->id; ?>" />
							</small>
						</td>

						<td style="text-align: center">
							<small>
								<?php echo $entry->id; ?>
							</small>
						</td>


						<td style="text-align: center">
							<small>
								<?php
								printf( "<a href='%s&amp;ipfilter=%s' onclick='javascript:add_filter( \"%s\", \"ipfilter\" ); return false;' title='%s' class='button-secondary'>F</a>
								", $ip_filter_link, $entry->IP, $entry->IP, __( 'Add a filter for this IP address', COUNTERIZE_TD ) );

								printf( '<a target="_blank" href="%s%s" title="%s" class="button-secondary">G</a>
								', counterize_get_option( 'geoip' ), $entry->IP, __( 'Geo IP tool service', COUNTERIZE_TD ) );

								printf( ' <a target="_blank" href="%s%s" title="%s">&#x202A;%s&#x202C;</a>
								', counterize_get_option( 'whois' ), $entry->IP, __( 'Whois service for this IP address', COUNTERIZE_TD ), $entry->IP );
								?>

							</small>
						</td>

						<td style="text-align: center">
							<small>
								<a href="<?php echo $country_filter_link . "&amp;countryfilter=" . htmlspecialchars( $entry->countrycode ); ?>" onclick="javascript:counterize_add_filter( '<?php echo htmlspecialchars( $entry->countrycode ); ?>', 'countryfilter' ); return false;" title="<?php _e( 'Add a filter for this country', COUNTERIZE_TD ); ?>" class="button-secondary">F</a>
								 <span><?php echo counterize_get_countrycode3( $entry->countrycode ); ?></span>
								<?php
									if( file_exists( COUNTERIZE_PLUGIN_DIR . "/ip_files/flags/{$entry->countrycode}.gif" ) )
									{
										echo "&nbsp;<img src='" . COUNTERIZE_PLUGIN_URL . "/ip_files/flags/{$entry->countrycode}.gif' title='" . counterize_get_countryname( $entry->countrycode ) . " ({$entry->countrycode})' alt='" . sprintf( __( 'National flag of %s', COUNTERIZE_TD ), counterize_get_countryname( $entry->countrycode ) ) . "' />";
									}
									elseif( file_exists( COUNTERIZE_PLUGIN_DIR . "/ip_files/flags/" . strtolower( $entry->countrycode ) . ".gif" ) )
									{
										//sometimes some tools such as unzip modify the filenames to be lowercase (happened to me)
										echo "&nbsp;<img src='" . COUNTERIZE_PLUGIN_URL . "/ip_files/flags/" . strtolower( $entry->countrycode ) . ".gif' title='" . counterize_get_countryname( $entry->countrycode ) . " ({$entry->countrycode})' alt='" . sprintf( __( 'National flag of %s', COUNTERIZE_TD ), counterize_get_countryname( $entry->countrycode ) ) . "' />";
									}
								?>
							</small>
						</td>

						<td style="text-align: center">
							<small><?php echo $entry->timestamp; ?> </small>
						</td>

						<td>
							<small>
								<a href="<?php echo $uri_filter_link . "&amp;urifilter=" . htmlspecialchars( $entry->url ); ?>" onclick="javascript:counterize_add_filter( '<?php echo htmlspecialchars( $entry->url ); ?>', 'urifilter' ); return false;" title="<?php _e( 'Add a filter for this URL', COUNTERIZE_TD ); ?>" class="button-secondary">F</a>
								 <?php echo '<a href="' . htmlspecialchars( $entry->url ) . '" target="_blank">&#x202A;' . htmlspecialchars( counterize_wordwrap( $entry->url ) ); ?>&#x202C;</a>
							</small>
						</td>

						<td>
							<small>
							<?php
							if( $entry->referer != "unknown" )
							{
								?><a href="<?php echo $referer_filter_link . "&amp;refererfilter=" . htmlspecialchars( $entry->referer ); ?>" onclick="javascript:counterize_add_filter( '<?php echo htmlspecialchars( $entry->referer ); ?>', 'refererfilter' ); return false;" title="<?php _e( 'Add a filter for this referer', COUNTERIZE_TD ); ?>" class="button-secondary">F</a>
								<?php
								echo ' <a href="' . htmlspecialchars( $entry->referer ) . '" target="_blank">&#x202A;' . htmlspecialchars( counterize_wordwrap( $entry->referer ) ) . '&#x202C;</a>';
								?>
								<?php
							}
							else
							{
								?><a href="<?php echo $referer_filter_link . "&amp;refererfilter=" . htmlspecialchars( $entry->referer ); ?>" onclick="javascript:counterize_add_filter( '<?php echo htmlspecialchars( $entry->referer ); ?>', 'refererfilter' ); return false;" title="<?php _e( 'Add a filter for this referer', COUNTERIZE_TD ); ?>" class="button-secondary">F</a>
								 <?php
								echo htmlspecialchars( counterize_wordwrap( $entry->referer ) );
							}
							?>
							</small>
						</td>

						<td>
							<small>
								<span title="<?php echo htmlspecialchars( $entry->useragent ); ?>"><?php echo counterize_browser_string( $entry->useragent , true, '<br />' ); ?></span>
								 <a href="<?php echo $agent_filter_link . '&amp;agentfilter=' . htmlspecialchars( urlencode( $entry->useragent ) ); ?>" onclick="javascript:counterize_add_filter( '<?php echo htmlspecialchars( $entry->useragent ); ?>', 'agentfilter' ); return false;" title="<?php _e( 'Add a filter for this user-agent', COUNTERIZE_TD ); ?>" class="button-secondary">F</a>
							</small>
						</td>

						<td style="text-align: left">
							<small>
								<?php if( ! empty( $entry->keyword ) ): ?>
								<a href="<?php echo $keyword_filter_link . '&amp;keywordfilter=' . htmlspecialchars( $entry->keyword ); ?>" onclick="javascript:counterize_add_filter( '<?php echo htmlspecialchars( $entry->keyword ); ?>', 'keywordfilter' ); return false;" title="<?php _e( 'Add a filter for this keyword', COUNTERIZE_TD ); ?>" class="button-secondary">F</a>
								<?php endif; ?>
								<?php echo ( empty( $entry->keyword ) ? "&nbsp;" : counterize_wordwrap( htmlspecialchars( $entry->keyword ) ) ) ; ?>
							</small>
						</td>

						<td style="text-align: center">
							<a href="javascript:counterize_conf( '<?php echo $uri_kill_link . "&amp;kill={$entry->id}"; ?>' );" class="button-secondary counterize_history_killbutton" title="<?php _e( 'Click here to delete this entry', COUNTERIZE_TD ); ?>">X</a>
						</td>
					</tr>
			<?php
			$i++;
		}
		unset( $entries );
		?>
				</tbody>
			</table>

			<input
				type="button"
				class="button-primary"
				name="counterize_checkAll"
				value="<?php _e( 'Check all', COUNTERIZE_TD ); ?>"
				onclick="javascript:counterize_check_all( document.tablesForm );" />
			<input
				type="button"
				class="button-primary"
				name="counterize_uncheckAll"
				value="<?php _e( 'Uncheck all', COUNTERIZE_TD ); ?>"
				onclick="javascript:counterize_uncheck_all( document.tablesForm );" />
			<input
				type="submit"
				class="button-primary"
				value="<?php _e( 'Delete checked entries', COUNTERIZE_TD ); ?>" />
		</form>

		<div id="counterize_history_navigationbar2">
			<ul>
				<?php if( $currentpage > 1 ): ?>
				<li><a href="<?php echo $nav_frst_link; ?>" class="button-secondary"><?php _e( '&laquo;&nbsp;First page', COUNTERIZE_TD ); ?></a></li>
				<li><a href="<?php echo $nav_prev_link; ?>" class="button-secondary"><?php _e( '&lsaquo;&nbsp;Previous page', COUNTERIZE_TD ); ?></a></li>
				<?php endif; ?>
				<li><a href="#counterizehistorytop" class="button-secondary"><?php _e( '&uarr;&nbsp;Top of the page&nbsp;&uarr;', COUNTERIZE_TD ) ?></a></li>
				<?php if( $currentpage < $lastpage ): ?>
				<li><a href="<?php echo $nav_next_link; ?>" class="button-secondary"><?php _e( 'Next page&nbsp;&rsaquo;', COUNTERIZE_TD ); ?></a></li>
				<li><a href="<?php echo $nav_last_link; ?>" class="button-secondary"><?php _e( 'Last page&nbsp;&raquo;', COUNTERIZE_TD ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

	</div>

<?php

}

function counterize_updateText( $text = '', $color='red' )
{
	if( $text == '' )
	{
		$text = __( 'Configuration updated', COUNTERIZE_TD );
	}
	echo "<div id=\"message\" class=\"updated fade\"><p><font color=\"{$color}\">";
	echo $text;
	echo "</font></p></div>";
}




//display all the statistics
function counterize_showStats( $admin = false )
{
	if( ! counterize_getamount() )
	{
		_e( "<strong>There's no data in the database - You can't see stats until you have data.</strong>", COUNTERIZE_TD );
		return;
	}

	global $counterize_plugin_count;
	if( $counterize_plugin_count == 0 )
	{
		printf
		(
			__( '<h2 style="color: #F00;">Important</h2><strong>No Counterize plugin has been activated</strong>. Please activate the plugins you need in the <a href="%s">Plugins page</a>.', COUNTERIZE_TD ),
			get_admin_url( null, 'plugins.php' )
		 );
		return;
	}

	// Amount to pass as option to the graphs...
	$amount2 = counterize_get_option( 'amount2' );
	if( $amount2 == '' || $amount2 == ' ' || !is_numeric( $amount2 ) )
	{
		$amount2 = 10;
	}

	//allow plugins to show their data into the "Counterize" sub-menu page.
	do_action( 'counterize_show_data', $admin, $amount2, false );
}

//Call the proper function depending of the $pattern
function counterize_check_data( $data, $pattern, $function, $argc=0 )
{
	if( is_callable( $function ) && is_numeric( $argc ) )
	{
		while( preg_match( $pattern, $data, $matches ) )
		{
			ob_start();

			$correct = true;
			if( $argc == 1 && isset( $matches[2] ) )
			{
				if( is_numeric( $matches[2] ) )
				{
					$ret = call_user_func( $function, $matches[2] );
				}
				else
				{
					$correct = false;
				}
			}
			elseif( $argc == 2 && isset( $matches[2] ) && isset( $matches[3] ) )
			{
				if( is_numeric( $matches[2] ) && is_numeric( $matches[3] ) )
				{
					$ret = call_user_func( $function, $matches[2], $matches[3] );
				}
				else
				{
					$correct = false;
				}
			}
			else
			{
				$ret = call_user_func( $function );
			}

			if( $correct )
			{
				$content = ob_get_contents();
				ob_end_clean();
				$replace_pattern = $pattern;
				$data = preg_replace( $replace_pattern, $content, $data );
			}
		}
	}
	return $data;
}


//Allow the authors to integrate charts into their posts using the following syntax (in HTML mode):
// <!-- keyword -->
function counterize_filter_callback( $data )
{
	//search if the post has the magic word
	$pattern = '/(\<\!\-|\#)\-\s*counterize_stats/';

	if( preg_match( $pattern, $data ) )
	{
		//we probably want to add some statistics. Lets verify:

		//<!-- counterize_stats --> : Shows all the stats
		$data = counterize_check_data( $data, '/(\<\!\-|\#)\-\s*counterize_stats\s*\-(\-\>|\#)/'                              , 'counterize_showStats'                         , 0 );

		//<!-- counterize_stats_copyright --> : Shows a small copyright notice
		$data = counterize_check_data( $data, '/(\<\!\-|\#)\-\s*counterize_stats_copyright\s*\-(\-\>|\#)/'                    , 'counterize_copyright'                         , 0);

		//let plugins to add their own charts
		$data = apply_filters( 'counterize_check_data', $data );

	}

	return $data;
}

/*
 * Declare the [counterize] shortcode
 */
add_shortcode('counterize', 'counterize_shortcodes_callback');
function counterize_shortcodes_callback( $attr, $content = null )
{
	$output = '';
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
	switch( $type )
	{
		case 'all':
			$ret = counterize_showStats();
			break;

		case 'copyright':
		default:
			$ret = counterize_copyright();
			break;
	}
	$output = ob_get_contents();
	ob_end_clean();

	//let plugins add their own shortcode
	$output = apply_filters( 'counterize_shortcodes', $output, $attr, $content );

	return $output;
}

function counterize_pagefooter()
{
	?>
	<div class="wrap counterizefooter">
		<p>
			<?php _e( '<strong>Need more help? </strong>Go to <a href="http://www.gabsoftware.com/products/scripts/counterize/" target="_blank">the Counterize homepage</a> and search inside the comments. Someone may have had the same question or problem as you...', COUNTERIZE_TD ); ?>
		</p>
		<p>
			<?php _e( 'Alternatively, you can also post your questions, suggestions and report problems in <a href="http://forum.gabsoftware.com/" target="_blank">our forum</a> or contact us in the comments or by email.', COUNTERIZE_TD ); ?>
		</p>
		<p>
			<?php _e( 'If you like Counterize and want to support its development, consider <strong><a style="color: #f00" href="http://www.gabsoftware.com/donate/" target="_blank">making a donation</a></strong>.', COUNTERIZE_TD ); ?>
		</p>
	</div>
	<?php
}










//send a report if necessary
function counterize_check_send_report()
{
	$options = get_option( 'counterize_options' );
	if( ( ! empty( $options['enableemailreports'] ) && $options['enableemailreports'] > 0 ) && ( ! empty( $options['reportperiod'] ) && $options['reportperiod'] != 'never' ) )
	{
		if( ! isset( $options['next_report_timestamp'] ) )
		{
			counterize_set_option( 'next_report_timestamp' , 0 );
		}
		$timestamp = $options['next_report_timestamp'];
		$now = time();
		if( $timestamp <= $now )
		{
			counterize_update_next_report_date();
			counterize_send_report_by_email();
		}
	}
}






/*
 * Update the timestamp in the file
 */
function counterize_update_next_report_date( $input = FALSE, $period = FALSE, $custom = FALSE )
{
	if( $input === FALSE )
	{
		$options = get_option( 'counterize_options' );
		$period = $options['reportperiod'];
		$custom = $options['customperiod'];
	}

	$now = time();
	$next = 0;

	switch( $period )
	{
		case 'daily':
			//in one day
			$next = $now + 86400;
			break;
		case 'weekly':
			//in one week
			$next = $now + 604800;
			break;
		case '15days':
			//in 15 days
			$next = $now + 1296000;
			break;
		case 'monthly':
			//in 1 month
			$next = $now + 2592000;
			break;
		case '3months':
			//in 3 months
			$next = $now + 7776000;
			break;
		case 'custom':
			//custom
			$next = $now + intval( $custom );
			break;
		case 'never':
		default:
			//exit this function
			if( $input === FALSE )
			{
				return;
			}
			else
			{
				return $input;
			}
	}
	if( $input !== FALSE )
	{
		$input['next_report_timestamp'] = $next;
		return $input;
	}
	counterize_set_option( 'next_report_timestamp', $next );
}





//send an report by email
function counterize_send_report_by_email( $new_options = FALSE )
{
	if( $new_options !== FALSE )
	{
		$options = $new_options;
	}
	else
	{
		$options = get_option( 'counterize_options' );
	}
	$subject = __( '[Counterize] New report available', COUNTERIZE_TD );
	if( ! empty( $options['mailsubjectoverride'] ) )
	{
		$subject = $options['mailsubjectoverride'];
	}

	$report_what = array( 'all' );
	if( ! empty( $options['reportwhat'] ) )
	{
		$report_what = explode( ',', $options['reportwhat'] );
	}

	// Get the site domain and get rid of www.
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	$sender = 'wordpress@' . $sitename;

	$recipient_list = $sender_fallback = get_bloginfo( 'admin_email' );
	if( ! empty( $options['recipientlist'] ) )
	{
		$recipient_list = $options['recipientlist'];
	}

	//generate the report
	ob_start();
	foreach( $report_what as $what )
	{
		if( $what == 'all' )
		{
			$ret = counterize_showStats();
		}
	}
	$content = ob_get_contents();
	ob_end_clean();

	//let plugins add their own shortcode
	$content = apply_filters( 'counterize_report', $content, $report_what );

	//Fix for non ASCII characters. Many thanks to Vadym Shvachko!
	$subject = '=?UTF-8?B?' . base64_encode( $subject ) . '?=';

	$html = '<html><head><title>' . $subject . '</title>';
	$html .= '<link rel="stylesheet" id="counterize_stylesheet-css" href="' . COUNTERIZE_PLUGIN_URL . '/counterize.css.php" type="text/css" media="all" />';
	$html .= '<script type="text/javascript" src="' . COUNTERIZE_PLUGIN_URL . '/counterize.js.php"></script>';
	$html .= '</head><body><h1>' . __( 'Counterize report', COUNTERIZE_TD ) . '</h1>' . $content . '</body></html>';

	$headers = array
	(
		0 => 'MIME-Version: 1.0',
		1 => 'Content-Type: text/html; charset=utf-8',
		2 => 'From: Counterize <' . $sender . '>'
	);

	//send the email
	$sent = wp_mail( $recipient_list, $subject, $html, $headers );
	if( ! $sent )
	{
		//try with the fallback email address (the blog admin email address)
		$headers[1] = 'From: Counterize <' . $sender_fallback . '>';
		$sent = wp_mail( $recipient_list, $subject, $html, $headers );
		if( ! $sent )
		{
			//try with the PHP mail function
			$sent = mail( $recipient_list, $subject, $html, $headers );
		}
	}
	return $sent;
}







//Executed when WordPress is loaded but before any header is sent.
//Inside we register our javascript and stylesheet files.
function counterize_init_callback()
{
	//global $counterize_dashboard_capability;
	//global $counterize_module_manager;

	if( function_exists( 'load_plugin_textdomain' ) )
	{
		load_plugin_textdomain( COUNTERIZE_TD, false, dirname( plugin_basename( __FILE__ ) ) );
	}

	counterize_translate_days();

	do_action( 'counterize_init' );

	// Create API hook instead of placing code directly in the header
	add_action( 'wp_head', 'counterize_header_callback', 1 );
	add_action( 'admin_head', 'counterize_header_callback', 1 );
	add_action( 'wp_head', 'counterize_add_callback', 1 );
	add_filter( 'the_content', 'counterize_filter_callback' );

	wp_register_script( 'counterize_javascript', COUNTERIZE_PLUGIN_URL . '/counterize.js.php', __FILE__);
	wp_register_style( 'counterize_stylesheet', COUNTERIZE_PLUGIN_URL . '/counterize.css.php', __FILE__);

	add_action( 'wp_print_scripts', 'counterize_javascript_head_callback' );
	add_action( 'wp_print_styles', 'counterize_stylesheet_head_callback' );

	//Do the administrative stuff only if we are in the administration area
	if( is_admin() )
	{
		if( current_user_can( 'manage_options' ) )
		{
			//include admin-related files
			require_once( COUNTERIZE_PLUGIN_DIR . '/counterize_admin.php' );
			require_once( COUNTERIZE_PLUGIN_DIR . '/counterize_dashboard.php' );

			//check if we need to install Counterize
			counterize_should_install();

			//Add admin actions
			add_action( 'admin_init', 'counterize_admin_init_callback' );
			add_action( 'admin_menu', 'counterize_add_pages_admin_callback' );
		}
		else
		{
			//Non admin pages
			$capability = counterize_get_option( 'display_dashboard_capability' );
			if( $capability === FALSE )
			{
				$capability = 'manage_options';
				counterize_set_option( 'display_dashboard_capability', $capability );
			}

			if( current_user_can( $capability ) )
			{
				//$counterize_dashboard_capability = $capability;
				require_once( COUNTERIZE_PLUGIN_DIR . '/counterize_dashboard.php' );
				add_action( 'admin_menu', 'counterize_add_pages_callback' );
			}
		}

		//add the counterize MCE plugin
		if ( current_user_can( 'edit_posts' ) &&  current_user_can( 'edit_pages' ) )
		{
			add_filter( 'mce_external_plugins', 'counterize_mce_add_plugin' );
			add_filter( 'mce_buttons', 'counterize_mce_register_button' );
		}
	}

	//add a menu in the Toolbar
	add_action( 'admin_bar_menu', 'counterize_toolbar_menu', 999 );

	//send email report if necessary
	//we should call this function after the counterize plugins have been loaded.
	add_action( 'wp_loaded', 'counterize_check_send_report' );
}

/*
 * Adds the Counterize menu in the WordPress toolbar
 */
function counterize_toolbar_menu( $wp_admin_bar )
{
	$capability = counterize_get_option( 'display_dashboard_capability' );

	if( ! current_user_can( $capability ) )
	{
		return;
	}

	//add the parent menu
	$args = array
	(
		'id'     => COUNTERIZE_TOOLBAR_SLUG, // id of the node
		'title'  => '<span class="ab-icon" style="margin-top: 3px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/bar_chart_16x16.png" alt="Counterize icon" /></span>
					<span class="ab-label">' . __( 'Counterize', COUNTERIZE_TD ) . '</span>', // title of the node
		'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard' ), // link to the Counterize Dashboard
		'parent' => false // set parent to false to make it a top level (parent) node
	);
	$wp_admin_bar->add_node( $args );

	// add a group node for the All Stats entry
	$args = array
	(
		'id' => 'counterize_allstats_group',
		'parent' => COUNTERIZE_TOOLBAR_SLUG
	);
	$wp_admin_bar->add_group( $args );

	// add the All stats node to its own group
	$args = array
	(
		'id'     => 'counterize_toolbar_allstats',
		'title'  => __( 'All stats' ),
		'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard' ), // link to the Counterize All stats
		'parent' => 'counterize_allstats_group'
	);
	$wp_admin_bar->add_node( $args );

	// add a group node for other menu items
	$args = array
	(
		'id' => 'counterize_stats_group',
		'parent' => COUNTERIZE_TOOLBAR_SLUG
	);
	$wp_admin_bar->add_group( $args );

	//let plugins add their own menu items
	do_action( 'counterize_toolbar_add_submenu', $wp_admin_bar );

	//add or not add the History and Settings menu items depending on user privileges
	if( current_user_can( 'manage_options' ) )
	{
		// add a group node for the History item
		$args = array
		(
			'id' => 'counterize_history_group',
			'parent' => COUNTERIZE_TOOLBAR_SLUG
		);
		$wp_admin_bar->add_group( $args );

		// add the History node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_history',
			'title'  => '<span class="ab-icon" style="margin-top: 3px; margin-right: 5px;"><img src="' . COUNTERIZE_PLUGIN_URL . '/history_16x16.png" alt="Counterize icon" /></span>'
						. __( 'History' ),
			'href'   => get_admin_url( null, 'admin.php?page=counterize_dashboard_history' ), // link to the Counterize History
			'parent' => 'counterize_history_group'
		);
		$wp_admin_bar->add_node( $args );

		// add a group node for the History item
		$args = array
		(
			'id' => 'counterize_settings_group',
			'parent' => COUNTERIZE_TOOLBAR_SLUG
		);
		$wp_admin_bar->add_group( $args );

		// add the Settings node to a our parent item
		$args = array
		(
			'id'     => 'counterize_toolbar_settings',
			'title'  => '<div style="float: left; height: 16px; width: 16px; background-attachment: scroll; background-clip: border-box; background-color: transparent; background-image: url(' . "'" . get_admin_url( null, 'images/menu.png' ) . "'" .' ); background-origin: padding-box; background-position: -247px -40px; background-repeat: no-repeat; background-size: auto; margin-top: 5px; margin-right: 5px;"></div>'
						. __( 'Settings' ),
			'href'   => get_admin_url( null, 'options-general.php?page=counterize_options_page_id' ), // link to the Counterize Settings
			'parent' => 'counterize_settings_group'
		);
		$wp_admin_bar->add_node( $args );
	}
}

//registers a custom button in the MCE editor
function counterize_mce_register_button( $buttons )
{
	array_push( $buttons, '|', 'counterize' );
	return $buttons;
}

//add a plugin to the MCE editor
function counterize_mce_add_plugin( $plugin_array )
{
	$plugin_array['counterize'] = COUNTERIZE_PLUGIN_URL . '/counterize_mce_plugin.js.php';
	return $plugin_array;
}


//Add the Counterize widget into the dashboard
function counterize_add_dashboard_widget_callback()
{
	wp_add_dashboard_widget( 'dashboard_counterize', __( 'Counterize Status', COUNTERIZE_TD ), 'counterize_dashboard_callback' );
}

//Add the Counterize javascript file to the <head> section
function counterize_javascript_head_callback()
{
	wp_enqueue_script( 'counterize_javascript' );
}

//Add the Counterize stylesheet file to the <head> section
function counterize_stylesheet_head_callback()
{
	wp_enqueue_style( 'counterize_stylesheet' );
}

//add a Configure link in the plugins page
function counterize_filter_plugin_actions_callback( $links, $file )
{
	$settings_link = '<a href="options-general.php?page=counterize_options_page_id">' . __( 'Configure', COUNTERIZE_TD ) . '</a>';
	array_unshift( $links, $settings_link ); // add the configure link before other links
	return $links;
}

function counterize_add_dashboard( $capability )
{
	global $menu;

	//tries to detect an available menu position. Usually at position 3 (right after the Dashboard menu)
	//there is an opening, but if not, it will find the next available position.
	$counterize_menu_position = 0;
	for( $i=2; $i< 99; $i++ )
	{
		if( ! isset( $menu[$i] ) )
		{
			$counterize_menu_position = $i;
			break;
		}
	}

	//Add a top level menu in the Dashboard menu
	$counterize_dashboard_handles = array();
	$counterize_dashboard_handles['dashboard'] = add_menu_page( __( 'Counterize', COUNTERIZE_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_TD ), __( 'Counterize', COUNTERIZE_TD ), $capability, COUNTERIZE_MENU_SLUG, 'counterize_display_dashboard_page_callback', COUNTERIZE_PLUGIN_URL . '/bar_chart_20x20.png', $counterize_menu_position );

	//let plugins add their own submenu
	$counterize_dashboard_handles = apply_filters( 'counterize_dashboard_add_submenu', $counterize_dashboard_handles, $capability );

	if( current_user_can( 'manage_options' ) )
	{
		$counterize_dashboard_handles['history']   = add_submenu_page( COUNTERIZE_MENU_SLUG, __( 'Counterize', COUNTERIZE_TD ) . ' - ' . __( 'Dashboard', COUNTERIZE_TD ) . ' - ' . __( 'History', COUNTERIZE_TD ), __( 'History', COUNTERIZE_TD ), $capability, 'counterize_dashboard_history' , 'counterize_display_dashboard_history_page_callback' );
	}
	add_action( 'load-' . $counterize_dashboard_handles['history'], 'counterize_add_help_tabs_to_history_callback' );

	//Rename the first submenu entry to All stats
	global $submenu;
	$submenu[COUNTERIZE_MENU_SLUG][0][0] = __( 'All stats', COUNTERIZE_TD );

	//add scripts and stylesheets
	add_action( "admin_print_scripts-{$counterize_dashboard_handles['dashboard']}", 'counterize_javascript_head_callback' );
	add_action( "admin_print_styles-{$counterize_dashboard_handles['dashboard']}" , 'counterize_stylesheet_head_callback' );

	if( current_user_can( 'manage_options' ) )
	{
		add_action( "admin_print_scripts-{$counterize_dashboard_handles['history']}", 'counterize_javascript_head_callback' );
		add_action( "admin_print_styles-{$counterize_dashboard_handles['history']}" , 'counterize_stylesheet_head_callback' );
	}

	return $counterize_dashboard_handles;
}

/*
 * Add help pages to the History
 */
function counterize_add_help_tabs_to_history_callback()
{
		$screen = get_current_screen();
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-ip', // This should be unique for the screen.
				'title'   => __( 'IP address filter', COUNTERIZE_TD ),
				'content' => __( '<p>You can type one or more IP addresses separated by "||".</p>', COUNTERIZE_TD )
							.__( '<p>You may use the wildcard characters "%" and "_" (see the Wildcard tab for more information).</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-countries', // This should be unique for the screen.
				'title'   => __( 'Country filter', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You can type one or more ISO 3166-1 Alpha-2 country codes separated by "||". More information <a target="_blank" href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">here</a>.</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
							.__( '<p>You may use the wildcard characters "%" and "_" (see the Wildcard tab for more information).</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-urls', // This should be unique for the screen.
				'title'   => __( 'URL filter', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You can type one or more URLs separated by "||".</p>', COUNTERIZE_TD )
							.__( '<p>You may use the wildcard characters "%" and "_" (see the Wildcard tab for more information).</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-referers', // This should be unique for the screen.
				'title'   => __( 'Referer filter', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You can type one or more referers separated by "||".</p>', COUNTERIZE_TD )
							.__( '<p>You may use the wildcard characters "%" and "_" (see the Wildcard tab for more information).</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-ua', // This should be unique for the screen.
				'title'   => __( 'User-agent filter', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You can type one or more user-agents separated by "||".</p>', COUNTERIZE_TD )
							.__( '<p>You may use the wildcard characters "%" and "_" (see the Wildcard tab for more information).</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-keywords', // This should be unique for the screen.
				'title'   => __( 'Keywords filter', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You can type one or more keywords separated by "||".</p>', COUNTERIZE_TD )
							.__( '<p>You may use the wildcard characters "%" and "_" (see the Wildcard tab for more information).</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-dates', // This should be unique for the screen.
				'title'   => __( 'Date filter', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You can filter entries between two dates. Just choose two valid dates (or type them if your browser displays text fields).</p>
								<p>Valid dates should follow the RFC 3339 as described in <a target="_blank" href="http://dev.w3.org/html5/markup/datatypes.html#form.data.datetime-local">this document</a>.</p>
								<p>Each date consists on a date formated as yyyy-mm-dd, followed by the character T, followed by a time formatted as HH:mm:ss. Example: 2012-01-01T12:30:00 represents January 1st 2012 at 12 hours 30 minutes and 0 seconds.</p>
								<h3>Notes:</h3>
								<ul>
									<li>The time part is optional. In that case, do not add a T.</li>
									<li>You may ommit the seconds and minutes in the time part.</li>
									<li>If you prefer, you may use a space instead of the T character, it will work too.</li>
								</ul>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-wildcards', // This should be unique for the screen.
				'title'   => __( 'Wildcards', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>You may use the wildcard characters "%" and "_" in most filters.</p>
								<p>The "%" character stands for zero to any characters and the "_" character stands for only one character.</p>
								<p>Wildcard characters are only available when the LIKE  or NOT LIKE filters are checked.</p>
								<p>More information about the wildcard characters <a target="_blank" href="http://dev.mysql.com/doc/refman/5.0/en/string-comparison-functions.html">here</a>.</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-filtertypes', // This should be unique for the screen.
				'title'   => __( 'Filter types', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>There are 4 filter types: <strong>Include</strong>, <strong>Exclude</strong>, <strong>LIKE</strong> and <strong>NOT LIKE</strong>.</p>
								<p>The Include filter will retrieve the data that meet the criterias of each filter. The "||" separator will act like a logical OR.</p>
								<p>The Exclude filter will retrieve the data that correspond to the contrary of what is set in the filters. The "||" separator will act like a logical AND.</p>
								<p>The LIKE and NOT LIKE filters let you use the LIKE syntax of MySQL. More information <a target="_blank" href="http://dev.mysql.com/doc/refman/5.0/en/string-comparison-functions.html">here</a>.</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-debug', // This should be unique for the screen.
				'title'   => __( 'Debug', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<p>If you check the Debug checkbox, the query generated by your filters will be displayed in case you want to check why your filters do not retrieve the expected data or if you want to run the query manually.</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->add_help_tab
		(
			array
			(
				'id'      => 'counterize-history-help-buttons', // This should be unique for the screen.
				'title'   => __( 'Buttons', COUNTERIZE_PLUGIN_BROWSERS_TD ),
				'content' => __( '<h3>Filters form buttons</h3>
								<p>There are quite a few buttons in the filters form. Here is what they are used for:
									<ul>
										<li>The "Clear" buttons will clear the associated filter</li>
										<li>The "Submit" button will apply your filters</li>
										<li>The "Clear form" button will clearn all the filters</li>
										<li>The "Cancel changes" button will restore all the filters to their last submitted value if any, or to an empty string otherwise.</li>
										<li>The "Reset filters" button will refresh the page so you can start with a clean filters form.</li>
									</ul>
								</p>
								<h3>Latest entries buttons</h3>
								<p>
									<ul>
										<li>The "F" buttons will help you to add the associated value to the filters form</li>
										<li>The "G" button will take you to a GeoIP service for the selected IP address</li>
										<li>The <span style="font-weight: bold; color: red;">"X"</span> button will delete the related entry from the Counterize history</li>
										<li>The "Check all" button will check all the displayed entries to let you mass delete them</li>
										<li>The "Uncheck all" button will uncheck any checked entry</li>
										<li>The "Delete checked entries" button will mass-delete any checked entry</li>
										<li>The "Top of the page" button will take you back to the top of the page</li>
										<li>The "OK", "First page", "Previous page", "Next page" and "Last page" buttons let you navigate in the entries of the history</li>
									</ul>
								</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
				// Use 'callback' instead of 'content' for a function callback that renders the tab content.
			)
		);
		$screen->set_help_sidebar
		(
			__( '<p>Choose a tab to learn more about the History page.</p>', COUNTERIZE_PLUGIN_BROWSERS_TD )
		);
}

//Add options menus an pages for administrators
function counterize_add_pages_admin_callback()
{
	//specify that we want to add a dashboard widget
	add_action( 'wp_dashboard_setup', 'counterize_add_dashboard_widget_callback' );

	//add the Counterize dashboard to the Dashboard section
	$counterize_dashboard_handles = counterize_add_dashboard( 'manage_options' );

	//add the Counterize options page
	$counterize_options_page_handle = add_options_page( __( 'Counterize Options', COUNTERIZE_TD ), __( 'Counterize', COUNTERIZE_TD ), 'manage_options', "counterize_options_page_id", 'counterize_options_page_callback' );

	//specify that we want to add the javascript code only for the Counterize options pages
	add_action( "admin_print_scripts-{$counterize_options_page_handle}", 'counterize_javascript_head_callback' );
	add_action( "admin_print_styles-{$counterize_options_page_handle}", 'counterize_stylesheet_head_callback' );

	//specify that we want to alter the links in the plugins page
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__), 'counterize_filter_plugin_actions_callback', 10, 2 );
}

function counterize_add_pages_callback()
{
	//global $counterize_dashboard_capability;
	$capability = counterize_get_option( 'display_dashboard_capability' );

	//specify that we want to add a dashboard widget
	add_action( 'wp_dashboard_setup', 'counterize_add_dashboard_widget_callback' );

	$counterize_dashboard_handles = counterize_add_dashboard( $capability );
}


//check is Counterize should be installed or upgraded
function counterize_should_install()
{
	$MajorVersion = get_option( 'counterize_MajorVersion' );
	if( $MajorVersion === FALSE )
	{
		$MajorVersion = counterize_get_version( 'major' );
		$MinorVersion = counterize_get_version( 'minor' );
		$Revision     = counterize_get_version( 'revision' );
	}
	else
	{
		$MajorVersion = get_option( 'counterize_MajorVersion', 1 );
		$MinorVersion = get_option( 'counterize_MinorVersion', 0 );
		$Revision     = get_option( 'counterize_Revision'    , 0 );
	}

	if( $MajorVersion != COUNTERIZE_VERSION_MAJ || $MinorVersion != COUNTERIZE_VERSION_MIN || $Revision != COUNTERIZE_VERSION_REV )
	{
		do_action( 'counterize_before_install', $MajorVersion, $MinorVersion, $Revision );

		require_once( 'counterize_install.php' );
		counterize_install( COUNTERIZE_VERSION_MAJ, COUNTERIZE_VERSION_MIN, COUNTERIZE_VERSION_REV );

		do_action( 'counterize_after_install', $MajorVersion, $MinorVersion, $Revision );
	}
}

/*
 * This function will disable update notifications for plugins bundled with
 * Counterize when the plugins are not activated ONLY (and when Counterize is activated of course)
 * This is necessary because WordPress keeps saying that each Counterize plugin
 * in the Counterize directory needs to be updated.
 */
function counterize_plugin_disable_update_check_for_disabled_plugins_callback( $value = '' )
{
	if( ( isset( $value->response ) ) && ( count( $value->response ) ) )
	{
		$counterize_plugins_prefix = plugin_basename( dirname( __FILE__) ) . '/counterize_plugin';

		// Get the list cut current active plugins
		$active_plugins = get_option('active_plugins');

		if( $active_plugins )
		{
			// Here we start to compare the $value->response
			// items checking each against the active plugins list.
			foreach( $value->response as $plugin_idx => $plugin_item )
			{
				// If the response item is not an active plugin then remove it.
				// This will prevent WordPress from indicating the plugin needs update actions.
				if( ( ! in_array( $plugin_idx, $active_plugins ) ) && ( 0 === strpos( $plugin_idx, $counterize_plugins_prefix ) ) )
				{
					unset( $value->response[ $plugin_idx ] );
				}
			}
		}
	}
	return $value;
}

add_action( 'init', 'counterize_init_callback' );
add_filter( 'site_transient_update_plugins', 'counterize_plugin_disable_update_check_for_disabled_plugins_callback' );

?>