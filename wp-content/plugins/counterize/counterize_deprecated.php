<?php
/*
 * The functions below are deprecated and should not be used anymore. They are kept for compatibility and history reasons, for a few more releases at least.
 */


// security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	die( 'There is nothing to see here.' );
}





//get statistics data for each day of the month (deprecated)
function counterize_getdailystats( $only_this_month = false )
{
	global $wpdb;

	$sql = "SELECT "
		. " 	DAYOFMONTH(`timestamp`) AS label, "
		. " 	DAYOFMONTH(`timestamp`) AS label2, "
		. " 	COUNT(1) AS amount "
		. " FROM `" . counterize_logTable() . "`";

	if( $only_this_month )
	{
		$sql .= " WHERE `timestamp` >= '" . date( 'Y-m-01' ) . "'";
	}
	$sql .= " GROUP BY label";
	return $wpdb->get_results($sql);
}

// get the monthly stats (deprecated)
function counterize_getmonthlystats()
{
	global $wpdb;

	$sql = "SELECT "
		. " 	CONCAT( CONCAT( SUBSTRING( MONTHNAME( `timestamp` ), 1, 3 ), ' ' ), SUBSTRING( YEAR( `timestamp` ), 3, 2 ) ) AS label, "
		. " 	CONCAT( CONCAT( SUBSTRING( MONTHNAME( `timestamp` ), 1, 3 ), ' ' ), SUBSTRING( YEAR( `timestamp` ), 3, 2 ) ) AS label2, "
		. " 	COUNT( 1 ) AS amount, "
		. " 	MONTH( `timestamp` ) AS m, "
		. " 	YEAR( `timestamp` ) AS y "
		. " FROM `" . counterize_logTable() . "`"
		. " GROUP BY label "
		. " ORDER BY y,m";

	return $wpdb->get_results($sql);
}


// get the  weekly stats (deprecated)
function counterize_getweeklystats()
{
	global $wpdb;
	$sql = "SELECT "
		. " 	DAYNAME( `timestamp` ) AS label, "
		. " 	DAYNAME( `timestamp` ) AS label2, "
		. " 	COUNT( 1 ) AS amount, "
		. " 	DAYOFWEEK(`timestamp`) AS day "
		. " FROM `" . counterize_logTable() . "`"
		. " GROUP BY label "
		. " ORDER BY day";
	return $wpdb->get_results($sql);
}



// render the  hourly stats (deprecated)
function counterize_gethourlystats($hour = "undef", $type = "both")
{
	global $wpdb;

	$sql = "SELECT "
		. " 	HOUR(`timestamp`) AS label, "
		. " 	HOUR(`timestamp`) AS label2, "
		. " 	COUNT(1) AS amount "
		. " FROM `" . counterize_logTable() . "`";

	$sql .= " GROUP BY label";
	return $wpdb->get_results($sql);
}









// show the most popular pages/posts (deprecated)
function counterize_most_popular_posts($number = 10, $width = 300, $tn_width = 50, $tn_height = 50)
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT c.`postID`, c.`count` AS amount , c.`url` AS url, w.`post_title` AS label, w.`post_title` AS label2 "
		. " FROM `" . counterize_pageTable() . "` c, `" . $wpdb->posts . "` w "
		. " WHERE c.`postID` = w.`ID` "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results($sql);

	reset( $rows );
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];

		//tries to retrieve the permalink instead of the raw url
		$row->url = get_permalink( $row->postID );

		//tries to get the post thumbnail if any
		if( ( function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail( $row->postID ) ) )
		{
			$row->label = get_the_post_thumbnail( $row->postID, array( $tn_width, $tn_height ), array( 'title' => sprintf( __( 'Click here to view the post "%s"', COUNTERIZE_TD ), $row->label ), 'alt' => $row->label, 'class' => 'counterize_thumb', 'border' => '0' ) ) . '&nbsp;' . $row->label;
		}
		else
		{
			$row->label = trim( stripslashes( $row->label ) );
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Posts', COUNTERIZE_TD ), $width, false, "100%", false, false);
}





// show the most popular pages/posts during the last 24 hours (deprecated)
function counterize_most_popular_posts24hrs($number = 10, $width = 300, $tn_width = 50, $tn_height = 50 )
{
	global $wpdb;
	$onedayago = date( 'Y-m-d H:i:s', time() - 86400 );
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT COUNT( l.`id` ) AS amount, c.`postID`, c.`url` AS url, w.`post_title` AS label, w.`post_title` AS label2 "
		. " FROM `" . counterize_logTable() . "` l, `" . counterize_pageTable() . "` c, `" . $wpdb->posts . "` w "
		. " WHERE l.`pageID` = c.`pageID` "
		. " AND c.`postID` = w.`ID` "
		. " AND l.`timestamp` >= '{$onedayago}'"
		. " GROUP BY c.`postID` "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results($sql);

	reset( $rows );
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];

		//tries to retrieve the permalink instead of the raw url
		$row->url = get_permalink( $row->postID );

		//tries to get the post thumbnail if any
		if( ( function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail( $row->postID ) ) )
		{
			$row->label = get_the_post_thumbnail( $row->postID, array( $tn_width, $tn_height ), array( 'title' => sprintf( __( 'Click here to view the post "%s"', COUNTERIZE_TD ), $row->label ), 'alt' => $row->label, 'class' => 'counterize_thumb', 'border' => '0' ) ) . '&nbsp;' . $row->label;
		}
		else
		{
			$row->label = trim( stripslashes( $row->label ) );
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Posts', COUNTERIZE_TD ), $width, false, "100%", false, false );
}



// show the most requested pages (deprecated)
function counterize_most_requested_urls($number = 10, $width = 300)
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT `count` AS amount , `url` AS url, `url` AS label, `url` AS label2 "
		. " FROM `" . counterize_pageTable() . "`"
		. " ORDER BY `amount` DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );
	counterize_renderstats_vertical( $rows, __( 'URL', COUNTERIZE_TD ), $width, false, "100%", true, true );
}
//deprecated
function counterize_most_visited_pages( $number = 10, $width = 300 )
{
	counterize_most_requested_urls( $number, $width );
}


//shows the most requested pages during the last 24 hours (deprecated)
function counterize_most_requested_urls24hrs($number = 10, $width = 300)
{
	global $wpdb;
	$onedayago = date("Y-m-d H:i:s", time() - 86400);
	$sql = "SELECT COUNT(m.`id`) AS amount, p.`url` AS url, p.`url` AS label, p.`url` AS label2 "
		. " FROM `" . counterize_logTable() . "` m, `" . counterize_pageTable() . "` p "
		. " WHERE m.`pageID` = p.`pageID`"
		. " AND m.`timestamp` >= '{$onedayago}'"
		. " GROUP BY p.`url` "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results($sql);
	counterize_renderstats_vertical($rows, __('URL', COUNTERIZE_TD), $width, false, "100%", true, true);
}
//deprecated
function counterize_most_visited_pages24hrs($number = 10, $width = 300)
{
	counterize_most_requested_urls24hrs( $number, $width );
}






//get the most seen referers (deprecated)
function counterize_most_visited_referrers( $number = 10, $width = 300 )
{
	global $wpdb;
	$sql = "SELECT `count` AS amount, `name` AS label, `name` AS label2, `name` AS url "
		. " FROM `" . counterize_refererTable() . "`"
		. " WHERE `name` <> 'unknown' "
		. " AND `name` NOT LIKE " . $wpdb->prepare( "%s", get_option( "home" ) . "%%" ) . " "
		. " AND `name` NOT LIKE " . $wpdb->prepare( "%s", get_option( "siteurl" ) . "%%" ) . " "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );
	counterize_renderstats_vertical( $rows, __( 'Referers', COUNTERIZE_TD ), $width, false, "100%", true, true );
}



//get the most seen referers for the last 24 hours (deprecated)
function counterize_most_visited_referrers24hrs( $number = 10, $width = 300 )
{
	global $wpdb;
	$onedayago = date("Y-m-d H:i:s", time() - 86400);
	$sql = "SELECT COUNT(m.id) AS amount, r.name AS label, r.name AS label2, r.name AS url "
		. " FROM `" . counterize_logTable() . "` m, `" . counterize_refererTable() . "` r "
		. " WHERE m.refererID = r.refererID "
		. " AND r.name <> 'unknown' "
		. " AND r.name NOT LIKE '" . $wpdb->prepare( "%s", get_option( "home" ) . "%%" ) . " "
		. " AND r.name NOT LIKE '" . $wpdb->prepare( "%s", get_option( "siteurl" ) . "%%" ) . " "
		. " AND m.timestamp >= '{$onedayago}'"
		. " GROUP BY r.name "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );
	counterize_renderstats_vertical( $rows, __( 'Referers', COUNTERIZE_TD ), $width, false, "100%", true, true );
}








//show the most active IP addresses (deprecated)
function counterize_most_visited_ips( $number = 10, $width = 300 )
{
	global $wpdb;
	$geoip = $wpdb->prepare( "%s", counterize_get_option( 'geoip' ) );
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT COUNT(`IP`) AS amount, `IP` AS label, `IP` AS label2, CONCAT({$geoip}, `IP`) AS url "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `IP` <> 'unavailable' "
		. " GROUP BY `IP` "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );

	counterize_renderstats_vertical( $rows, __( 'IP', COUNTERIZE_TD ), $width );
}




//show the most active IP addresses of the last 24 hours (deprecated)
function counterize_most_visited_ips24hrs( $number = 10, $width = 300 )
{
	global $wpdb;
	$onedayago = date( "Y-m-d H:i:s", time() - 86400 );
	$number = $wpdb->prepare( "%d", $number );
	$geoip = $wpdb->prepare( "%s", counterize_get_option( 'geoip' ) );
	$sql = "SELECT COUNT(`IP`) AS amount, `IP` AS label, `IP` AS label2, CONCAT({$geoip}, `IP`) AS url "
		. " FROM `" . counterize_logTable() . "`"
		. " WHERE `IP` <> 'unavailable' "
		. " AND `timestamp` >= '{$onedayago}'"
		. " GROUP BY `IP` "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );

	counterize_renderstats_vertical( $rows, __( 'IP', COUNTERIZE_TD ), $width );
}






//show the most searched keywords (deprecated)
function counterize_most_searched_keywords( $number = 10, $width = 300 )
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT `count` AS amount, `keyword` AS label, `keyword` AS label2 "
		. " FROM `" . counterize_keywordTable() . "`"
		. " WHERE `keywordID` <> 1 "
		. " GROUP BY `keyword` "
		. " ORDER BY `count` DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );
	counterize_renderstats_vertical( $rows, __( 'Keywords', COUNTERIZE_TD ), $width );
}



//show the most searched keywords today (deprecated)
function counterize_most_searched_keywords_today( $number = 10, $width = 300 )
{
	global $wpdb;
	$today = date( 'Y-m-d' );
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT COUNT(1) AS amount, k.keyword AS label, k.keyword AS label2 "
		. " FROM " . counterize_keywordTable() . " k, " . counterize_logTable() . " l, " . counterize_refererTable() . " r "
		. " WHERE r.refererID = l.refererID "
		. " AND r.keywordID = k.keywordID "
		. " AND k.keywordID <> 1 "
		. " AND l.timestamp >= '{$today}' "
		. " GROUP BY k.keyword "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";

	$rows = $wpdb->get_results( $sql );

	counterize_renderstats_vertical( $rows, __( 'Keywords', COUNTERIZE_TD ), $width );
}









// show the most visiting countries (deprecated)
function counterize_most_visiting_countries( $number = 10, $width = 300 )
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT `count` AS amount , `code`, `code` AS label, `code` AS label2 "
		. " FROM `" . counterize_countryTable() . "`"
		. " WHERE `count` > 0 "
		. " ORDER BY `amount` DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );

	reset( $rows );
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];
		if( $row->label == " " || $row->label == "" || $row->code == "00" )
		{
			$row->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$row->label = counterize_get_flag_tag( $row->code, $row->label ) . "&nbsp;" . counterize_get_countryname( $row->code );
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Countries', COUNTERIZE_TD ), $width, false, "100%", false, false );
}







// show the most visiting countries of these last 24 hours (deprecated)
function counterize_most_visiting_countries24hrs( $number = 10, $width = 300 )
{
	global $wpdb;
	$onedayago = date( "Y-m-d H:i:s", time() - 86400 );
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT COUNT(m.id) AS amount, c.code AS code, c.code AS label, c.code AS label2 "
		. " FROM `" . counterize_logTable() . "` m, `" . counterize_countryTable() . "` c "
		. " WHERE m.countryID = c.countryID"
		. " AND m.timestamp >= '{$onedayago}'"
		. " GROUP BY c.code "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );

	reset( $rows );
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];
		if($row->label == " " || $row->label == "" || $row->code == "00")
		{
			$row->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$row->label = counterize_get_flag_tag( $row->code, $row->label ) . "&nbsp;" . counterize_get_countryname( $row->code );
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Countries', COUNTERIZE_TD ), $width, false, "100%", false, false );
}










//display a collapsible list of browsers (deprecated)
function counterize_most_used_browsers_collapsible($nb_parent_items = 10, $nb_child_items = 15, $width = 300)
{
	global $wpdb;

	$nb_parent_items = (int) $wpdb->prepare( "%d", $nb_parent_items );
	$nb_child_items  = (int) $wpdb->prepare( "%d", $nb_child_items );

	$sql_parents =
			"SELECT `browserName` AS label, `browserName` AS label2, `browserCode` AS code, `browserURL` as url, SUM( `count` ) AS amount "
		. "  FROM `" . counterize_agentsTable() . "`"
		. "  GROUP BY label "
		. "  ORDER BY amount DESC , label ASC "
		. "  LIMIT {$nb_parent_items} ";

	$sql_child =
			"SELECT `browserVersion` AS label, SUM( `count` ) AS amount "
		. "  FROM `" . counterize_agentsTable() . "`"
		. "  WHERE `browserName` = %s "
		. "  GROUP BY label "
		. "  ORDER BY amount DESC , label ASC "
		. "  LIMIT %d";

	$rows_parents = $wpdb->get_results( $sql_parents );

	$rows_child = array();

	$counter = 0;

	foreach( $rows_parents as $parent )
	{
		$sql = $wpdb->prepare( $sql_child, $parent->label, $nb_child_items );
		$rows_child[$counter] = $wpdb->get_results( $sql );
		unset( $sql );

		if($parent->label == " " || $parent->label == "")
		{
			$parent->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$parent->label = counterize_get_image_tag( $parent->code, $parent->label, $parent->url ) . "&nbsp;" . $parent->label;
		}

		$counter++;
	}

	$random = counterize_get_random_number(1000000, 1999999);

	counterize_renderstats_vertical_collapsed( $rows_parents, $rows_child, $random, __( 'Browsers', COUNTERIZE_TD ), $width, true, "100%", false, false );
}


// show the most used browsers without version (deprecated)
function counterize_most_used_browsers_without_version($number = 10, $width = 300)
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT SUM(`count`) AS amount, `browserName` AS label, `browserName` AS label2, `browserCode` AS code, `browserURL` AS url "
		. " FROM `" . counterize_agentsTable() . "`"
		. " GROUP BY label "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );

	reset( $rows );
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];
		if($row->label == " " || $row->label == "")
		{
			$row->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$row->label = counterize_get_image_tag( $row->code, $row->label, $row->url ) . "&nbsp;" . $row->label;
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Browsers', COUNTERIZE_TD ), $width, true, "100%", false );
}



// show the most used browsers  (deprecated)
function counterize_most_used_browsers($number = 10, $width = 300)
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT "
		. " 	SUM(`count`) AS amount, "
		. " 	CONCAT( CONCAT( `browserName`, ' ' ), `browserVersion` ) AS label, "
		. " 	CONCAT( CONCAT( `browserName`, ' ' ), `browserVersion` ) AS label2, "
		. " 	`browserCode` AS code, "
		. " 	`browserURL` AS url "
		. " FROM `" . counterize_agentsTable() . "`"
		. " GROUP BY label "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";
	$rows = $wpdb->get_results( $sql );

	reset($rows);
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];
		if($row->label == " " || $row->label == "")
		{
			$row->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$row->label = counterize_get_image_tag( $row->code, $row->label, $row->url ) . '&nbsp;' . $row->label;
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Browsers', COUNTERIZE_TD ), $width, true, "100%", false );
}









//shows the graph of the most used browser with collapsible version statistics (deprecated)
function counterize_most_used_os_collapsible($nb_parent_items = 10, $nb_child_items = 15, $width = 300)
{
	global $wpdb;

	$nb_parent_items = (int) $wpdb->prepare( "%d", $nb_parent_items );
	$nb_child_items  = (int) $wpdb->prepare( "%d", $nb_child_items );

	$sql_parents =
			"SELECT `osName` AS label, `osName` AS label2, `osCode` AS code, `osURL` as url, SUM( `count` ) AS amount "
		. "  FROM `" . counterize_agentsTable() . "`"
		. "  GROUP BY label "
		. "  ORDER BY amount DESC , label ASC "
		. "  LIMIT {$nb_parent_items} ";

	$sql_child =
			"SELECT `osVersion` AS label, SUM( `count` ) AS amount "
		. "  FROM `" . counterize_agentsTable() . "`"
		. "  WHERE osName = %s "
		. "  GROUP BY label "
		. "  ORDER BY amount DESC , label ASC "
		. "  LIMIT %d";

	$rows_parents = $wpdb->get_results( $sql_parents );

	$rows_child = array();

	$counter = 0;
	foreach( $rows_parents as $parent )
	{
		$sql = $wpdb->prepare( $sql_child, $parent->label, $nb_child_items );
		$rows_child[$counter] = $wpdb->get_results( $sql );

		if($parent->label == " " || $parent->label == '')
		{
			$parent->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$parent->label = counterize_get_image_tag( $parent->code, $parent->label, $parent->url ) . "&nbsp;" . $parent->label;
		}

		$counter++;
	}

	$random = counterize_get_random_number( 2000000, 2999999 );

	counterize_renderstats_vertical_collapsed( $rows_parents, $rows_child, $random, __( 'Operating systems', COUNTERIZE_TD ), $width, true, "100%", false, false );
}



// show the most used os without version (deprecated)
function counterize_most_used_os_without_version($number = 10, $width = 300)
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT SUM(`count`) AS amount, `osName` AS label, `osName` AS label2, `osCode` AS code, `osURL` AS url "
		. " FROM `" . counterize_agentsTable() . "`"
		. " GROUP BY label "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";

	$rows = $wpdb->get_results( $sql );

	reset($rows);
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];
		if($row->label == " " || $row->label == "")
		{
			$row->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$row->label = counterize_get_image_tag( $row->code, $row->label, $row->url ) . "&nbsp;" . $row->label;
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Operating systems', COUNTERIZE_TD ), $width, true, "100%", false );
}




// show the most used os (deprecated)
function counterize_most_used_os( $number = 10, $width = 300 )
{
	global $wpdb;
	$number = $wpdb->prepare( "%d", $number );
	$sql = "SELECT SUM( `count` ) AS amount, CONCAT( CONCAT( `osName`, ' ' ), `osVersion` ) AS label, CONCAT( CONCAT( `osName`, ' ' ), `osVersion` ) AS label2, `osCode`, `osURL` AS url "
		. " FROM `" . counterize_agentsTable() . "`"
		. " GROUP BY label "
		. " ORDER BY amount DESC "
		. " LIMIT {$number}";

	$rows = $wpdb->get_results( $sql );

	reset( $rows );
	while( list( $i, $r ) = each( $rows ) )
	{
		$row =& $rows[$i];
		if($row->label == " " || $row->label == "")
		{
			$row->label = __( 'unknown', COUNTERIZE_TD );
		}
		else
		{
			$row->label = counterize_get_image_tag( $row->osCode, $row->label, $row->url ) . "&nbsp;" . $row->label;
		}
	}

	counterize_renderstats_vertical( $rows, __( 'Operating systems', COUNTERIZE_TD ), $width, true, "100%", false );
}














//render statistics with collapsible detailed statistics for each item (deprecated)
function counterize_renderstats_vertical_collapsed($rows_parents, $rows_all_child, $uniqueid, $header, $max_width = "500", $nofollow = true, $maxwidth = "100%", $shorten = true, $htmlspecialchars_for_label = false)
{
	$max_label = counterize_get_option( 'maxwidth' );
	$items = 0;
	$complete_amount = 0;
	$max = 0;
	foreach($rows_parents as $parent)
	{
		$items++;
		$complete_amount += $parent->amount;
		if($parent->amount > $max)
		{
			$max = $parent->amount;
		}
	}

	?>

			<table width="<?php echo $maxwidth; ?>" summary="<?php _e( 'Statistics', COUNTERIZE_TD ); ?>">
				<tr class="alternate">
					<td style="width: 25%"><small><strong><?php echo htmlspecialchars( __( $header, COUNTERIZE_TD ) ); ?></strong></small></td>
					<td style="width: 10%"><small><strong><?php _e( 'Amount', COUNTERIZE_TD ); ?></strong></small></td>
					<td style="width: 65%"><small><strong><?php _e( 'Percentage', COUNTERIZE_TD ); ?></strong></small></td>
				</tr>

	<?php
	$counter = 0;
	foreach($rows_parents as $parent)
	{
		$percent = round($parent->amount / $complete_amount * 100, 2);

		if( isset($parent->amount) || array_key_exists( 'amount', get_object_vars( $parent ) ) )
		{
			$width = round($parent->amount * $max_width / $max);
		}
		else
		{
			$width = 0;
		}

		$group = round($width / $max_width * 100);
		?>

				<tr<?php if($counter % 2) { echo " class=\"alternate\""; } ?>>
					<td style="width: 25%">
						<?php
						$is_unknown = true;
						if( $parent->code != 'unknown' )
						{
							$is_unknown = false;
							?>

						<span><a id="counterize_parent_<?php echo $uniqueid . "_" . $counter; ?>" href="javascript:counterize_fold( 'counterize_parent_<?php echo $uniqueid . "_" . $counter; ?>', 'counterize_child_<?php echo $uniqueid . "_" . $counter; ?>' );">[+]</a></span>

						<?php
						}
						?><small><?php
		if(strlen($parent->label) > $max_label && $shorten == true)
		{
			$label = substr($parent->label, 0, $max_label) . '...';
		}
		else
		{
			$label = $parent->label;
		}
		if( isset($parent->url) || array_key_exists('url', get_object_vars($parent)) )
		{
			echo "\n\t\t\t\t\t\t\t<a href=\"" . htmlspecialchars( $parent->url ) . "\"";
			if($nofollow)
			{
				echo " rel=\"nofollow\"";
			}
			echo " target=\"_blank\">\n\t\t\t\t\t\t\t\t" . ( $htmlspecialchars_for_label ? htmlspecialchars( $label ) : $label ) . "\n\t\t\t\t\t\t\t</a>\n";
		}
		else
		{
			echo ( $htmlspecialchars_for_label ? htmlspecialchars( $label ) : $label );
		}
		?>
						</small>
					</td>

					<td style="width: 10%">
						<small><?php echo htmlspecialchars( $parent->amount ); ?></small>
					</td>

					<td style="width: 65%">
						<img	src="<?php echo htmlspecialchars( COUNTERIZE_PLUGIN_URL );
				if ($group < 40)
						echo "/counterize_red.png";
				else if ($group < 80)
						echo "/counterize_yellow.png";
				else
						echo "/counterize_green.png";
				?>"
							style="height:8px; width:<?php echo $width; ?>px; vertical-align:bottom"
							alt="<?php
				echo $header . ' - ' . htmlspecialchars($parent->label2) . ' - ' . htmlspecialchars( $parent->amount ) . ' - ' . htmlspecialchars( $percent ) . ' %';
				?>" />
						<small><strong><?php echo htmlspecialchars( $percent ); ?> %</strong></small>
					</td>
				</tr>

				<?php
				if ( !$is_unknown)
				{
				?>

				<tr id="counterize_child_<?php echo $uniqueid . "_" . $counter; ?>" class="collapsed">
					<td colspan="3">
						<table style="background-color: #ddd;" width="<?php echo $maxwidth; ?>" summary="<?php _e( 'Detailed statistics for this entry', COUNTERIZE_TD ); ?>">

				<?php
					$rows_child = $rows_all_child[$counter];

					$child_items = 0;
					$child_complete_amount = 0;
					$child_max = 0;
					foreach($rows_child as $child)
					{
						$child_items++;
						$child_complete_amount += $child->amount;
						if($child->amount > $child_max)
						{
							$child_max = $child->amount;
						}
						if($child->label == "" || $child->label == " ")
						{
							$child->label = __( '(no version)', COUNTERIZE_TD );
						}
					}

					$child_counter = 0;
					foreach($rows_child as $child)
					{


						$child_percent = round($child->amount / $child_complete_amount * 100, 2);

						if( $child_max > 0 && ( isset($child->amount) || array_key_exists('amount', get_object_vars($child)) ) )
						{
							$child_width = round($child->amount * $max_width / $child_max);
						}
						else
						{
							$child_width = 0;
						}

						$child_group = round($child_width / $max_width * 100);


						?>

								<tr<?php if($child_counter % 2) { echo " class=\"alternate\""; } ?>>

									<td style="width: 25%">
										<small><?php

						if(strlen($child->label) > $max_label && $shorten == true)
						{
							$child_label = substr($child->label, 0, $max_label) . '...';
						}
						else
						{
							$child_label = $child->label;
						}
						echo $child_label;
						?>

										</small>
									</td>

									<td style="width: 10%">
										<small><?php echo htmlspecialchars( $child->amount ); ?></small>
									</td>

									<td style="width: 65%">
										<img src="<?php echo COUNTERIZE_PLUGIN_URL;
						if ($child_group < 40)
							echo "/counterize_red.png";
						else if ($child_group < 80)
							echo "/counterize_yellow.png";
						else
							echo "/counterize_green.png";
						?>"
											style="height:8px; width:<?php echo $child_width; ?>px; vertical-align:bottom"
											alt="<?php
						echo htmlspecialchars( $header . ' - ' . $parent->label2 . " " . $child->label . ' - ' . $child->amount . ' - ' . $child_percent ) . ' %';
						?>" />
										<small><strong><?php echo htmlspecialchars( $child_percent ); ?> %</strong></small>
									</td>
								</tr>

						<?php

						$child_counter++;

					}

				?>

						</table>
					</td>

				</tr>
				<?php

				}

		$counter++;
	}
	?>

			</table>

	<?php
}





//render statistics vertically (deprecated)
function counterize_renderstats_vertical($rows, $header, $max_width = "500", $nofollow = true, $maxwidth = "100%", $shorten = true, $htmlspecialchars_for_label = false)
{
	$max_label = counterize_get_option( 'maxwidth' );
	$items = 0;
	$complete_amount = 0;
	$max = 0;
	foreach($rows as $row)
	{
		$items++;
		$complete_amount += $row->amount;
		if($row->amount > $max)
		{
			$max = $row->amount;
		}
	}

	?>

			<table width="<?php echo $maxwidth; ?>" summary="<?php _e( 'Statistics', COUNTERIZE_TD ); ?>">
				<tr class="alternate">
					<td style="width: 25%"><small><strong><?php echo htmlspecialchars( __( $header, COUNTERIZE_TD ) ); ?></strong></small></td>
					<td style="width: 10%"><small><strong><?php _e( 'Amount', COUNTERIZE_TD ); ?></strong></small></td>
					<td style="width: 65%"><small><strong><?php _e( 'Percentage', COUNTERIZE_TD ); ?></strong></small></td>
				</tr>

	<?php
	$counter = 0;
	foreach($rows as $row)
	{
		$percent = round($row->amount / $complete_amount * 100, 2);

		if( isset($row->amount) || array_key_exists('amount', get_object_vars($row)) )
		{
			$width = round($row->amount * $max_width / $max);
		}
		else
		{
			$width = 0;
		}

		$group = round($width / $max_width * 100);
		?>

				<tr<?php if( $counter % 2 ) { echo " class=\"alternate\""; } ?>>
					<td style="width: 25%">
						<small><?php
		if( strlen( $row->label ) > $max_label && $shorten == true)
		{
			$label = substr( $row->label, 0, $max_label ) . '...';
		}
		else
		{
			$label = $row->label;
		}
		if( isset($row->url) || array_key_exists('url', get_object_vars($row)) )
		{
			// ( $htmlspecialchars_for_url ? htmlspecialchars( $row->url ) : $row->url )
			echo "\n\t\t\t\t\t\t\t<a href=\"" . htmlspecialchars( $row->url ) . "\"";
			if($nofollow)
			{
				echo " rel=\"nofollow\"";
			}
			echo " target=\"_blank\">\n\t\t\t\t\t\t\t\t" . ( $htmlspecialchars_for_label ? htmlspecialchars( $label ) : $label ) . "\n\t\t\t\t\t\t\t</a>\n";
		}
		else
		{
			echo ( $htmlspecialchars_for_label ? htmlspecialchars( $label ) : $label );
		}
		?>
						</small>
					</td>

					<td style="width: 10%">
						<small><?php echo htmlspecialchars( $row->amount ); ?></small>
					</td>

					<td style="width: 65%">
						<img	src="<?php echo htmlspecialchars( COUNTERIZE_PLUGIN_URL );
				if ($group < 40)
						echo "/counterize_red.png";
				else if ($group < 80)
						echo "/counterize_yellow.png";
				else
						echo "/counterize_green.png";
				?>"
							style="height:8px; width:<?php echo $width ?>px; vertical-align:bottom"
							alt="<?php
				echo htmlspecialchars( $header . ' - ' . $row->label2 . ' - ' . $row->amount . ' - ' . $percent ) . ' %';
				?>" />
						<small><strong><?php echo htmlspecialchars( $percent ); ?> %</strong></small>
					</td>
				</tr>

	<?php $counter++;
	}
	?>

			</table>

<?php
}







//render stats horizontally (deprecated)
function counterize_renderstats($rows, $max_height = 80, $maxwidth = "100%")
{
	?>

	<table width="<?php echo $maxwidth; ?>" summary="<?php _e( 'Statistics', COUNTERIZE_TD ); ?>">

		<tr>

	<?php

	$items = 0;
	$complete_amount = 0;
	$max = 0;

	foreach($rows as $row)
	{
		$items++;
		$complete_amount += $row->amount;
		if($row->amount > $max)
		{
			$max = $row->amount;
		}
	}

	$i = 0;
	foreach($rows as $row)
	{
		$percent = round($row->amount / $complete_amount * 100,2);

		if($row->amount)
		{
			$height = round($row->amount * $max_height / $max);
		}
		else
		{
			$height = 0;
		}

		$group = round($height / $max_height * 100);

		echo "
			<td style=\"width:3%\"";
				if($i%2)
				{
					echo " class=\"alternate\"";
				}

				echo " align=\"center\" valign=\"bottom\"><small>";
				echo htmlspecialchars( $row->amount );
				?>
				<br />
				<img src="<?php echo htmlspecialchars( COUNTERIZE_PLUGIN_URL );
				if ($group < 40)
				{
					echo "/counterize_red.png";
				}
				else if ($group < 80)
				{
					echo "/counterize_yellow.png";
				}
				else
				{
					echo "/counterize_green.png";
				}
				?>" style="width:8px; height:<?php echo $height ?>px; vertical-align:bottom" alt="Statistics" />
				<?php
				echo "<br />{$percent}<br />%</small>
			</td>
			";
			$i++;
	}
	?>
		</tr>
		<tr>
	<?php
	$i = 0;
	foreach($rows as $row)
	{
		echo "
			<td style=\"width:3%\"";
		if($i % 2)
		{
			echo " class=\"alternate\"";
		}
		echo " align=\"center\"><small><strong>" . htmlspecialchars( $row->label ) . "</strong></small></td>";
		$i++;
	}
	?>

		</tr>

	</table>

	<?php
}




?>