<?php

// security check
if( ! defined( 'WP_PLUGIN_DIR' ) || ! current_user_can( 'manage_options' ) )
{
	die( 'There is nothing to see here, sorry.' );
}

// Used for first-time initialization
// create tables if not present...
function counterize_install( $vermaj, $vermin, $verrev )
{
	$MajorVersion = get_option( 'counterize_MajorVersion' );
	if( $MajorVersion === FALSE )
	{
		//this option has been deleted, we got at least 3.0.12
		$MajorVersion = intval( counterize_get_version( 'major' ) );
		$MinorVersion = intval( counterize_get_version( 'minor' ) );
		$Revision     = intval( counterize_get_version( 'revision' ) );
	}
	else
	{
		$MajorVersion = intval( get_option( 'counterize_MajorVersion', 1 ) );
		$MinorVersion = intval( get_option( 'counterize_MinorVersion', 0 ) );
		$Revision     = intval( get_option( 'counterize_Revision'    , 0 ) );
	}

	//die( "$MajorVersion.$MinorVersion.$Revision to $vermaj.$vermin.$verrev" );

	//updates can set any of these flags to true to perform the action
	$should_update_user_agents = false;
	$should_delete_bots        = false;

	global $wpdb;

	if( $MajorVersion < 2 )
	{
		$sql = "SHOW TABLES LIKE '" . counterize_logTable() . "'";
		$results = $wpdb->query( $sql );

		if( $results == 0 )
		{
			// Update to Version 1
			$sql = "CREATE TABLE `". counterize_logTable() . "` (
				`id` INTEGER NOT NULL AUTO_INCREMENT,
				`IP` VARCHAR( 16 ) NOT NULL,
				`timestamp` DATETIME NOT NULL,
				`url` VARCHAR( 255 ) NOT NULL DEFAULT 'unknown',
				`referer` VARCHAR( 255 ) NOT NULL DEFAULT 'unknown',
				`useragent` TEXT,
				PRIMARY KEY( `id` )
			)";

			$results = $wpdb->query( $sql );
		}

		// update to Version 2
		$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "`"
		. " ADD `pageID` INT( 11 ) NOT NULL,"
		. " ADD `agentID` INT( 11 ) NOT NULL,"
		. " ADD `refererID` INT( 11 ) NOT NULL";
		$wpdb->query( $sql );

		$sql = "CREATE TABLE `" . counterize_pageTable() . "` (
			`pageID` INT( 11 ) NOT NULL AUTO_INCREMENT,
			`url` VARCHAR( 255 ) NOT NULL,
			`count` INT( 11 ) NOT NULL DEFAULT '1',
			`postID` BIGINT( 20 ) DEFAULT NULL,
			PRIMARY KEY ( `pageID` ),
			UNIQUE `url` ( `url` ),
			KEY `count` ( `count` )
			)";
		$wpdb->query( $sql );

		$sql ="CREATE TABLE `" . counterize_refererTable() . "` (
			`refererID` INT( 11 ) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR( 255 ) NOT NULL,
			`count` INT( 11 ) NOT NULL DEFAULT '1',
			PRIMARY KEY ( `refererID` ),
			UNIQUE `name` ( `name` ),
			KEY `count` ( `count` )
			)";
		$wpdb->query( $sql );

		$sql = "CREATE TABLE `" . counterize_agentsTable() . "` (
			`agentID` INT( 11 ) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR( 255 ) NOT NULL,
			`count` INT( 11 ) NOT NULL DEFAULT '1',
			PRIMARY KEY ( `agentID` ),
			UNIQUE `name` ( `name` ),
			KEY `count` ( `count` )
			) ";
		$wpdb->query( $sql );

		$sql = "INSERT IGNORE INTO `" . counterize_pageTable() . "` ( `url`, `count` )
			SELECT
			`url`, COUNT( `url` )
			FROM `" . counterize_logTable() . "`
			GROUP BY `url`;";
		$wpdb->query( $sql );

		$sql = "INSERT IGNORE INTO `" . counterize_refererTable() . "` ( `name`, `count` )
			SELECT
			`referer`, COUNT( `referer` )
			FROM `" . counterize_logTable() . "`
			GROUP BY `referer`;";
		$wpdb->query( $sql );

		$sql = "INSERT IGNORE INTO `" . counterize_agentsTable() . "` ( `name`, `count` )
			SELECT
			`useragent`, COUNT( `useragent` )
			FROM `" . counterize_logTable() . "`
			GROUP BY `useragent`;";
		$wpdb->query( $sql );

		/*
		 * Avoid a long loop thanks to Daniel from chaosonline.de
		 *
		$entries = $wpdb->get_results( "SELECT * FROM " . counterize_logTable() );
		foreach( $entries as $entry )
		{
			$pageID = $wpdb->get_var( "SELECT pageID FROM `" . counterize_pageTable() . "` WHERE url='" . $entry->url . "'" );
			$agentID = $wpdb->get_var( "SELECT agentID FROM `" . counterize_agentsTable() . "` WHERE name='" . $entry->useragent . "'" );
			$refererID = $wpdb->get_var( "SELECT refererID FROM `" . counterize_refererTable() . "` WHERE name='" . $entry->referer . "'" );
			if( !$pageID )
				$pageID = "null";
			if( !$agentID )
				$agentID = "null";
			if( !$refererID )
				$refererID = "null";
				$sql = "UPDATE `" . counterize_logTable() . "` SET pageID = {$pageID}, agentID = {$agentID}, refererID = {$refererID} WHERE id = " . $entry->id;
				$wpdb->query( $sql );
		}
		*/
		$sql = "UPDATE IGNORE `" . counterize_logTable() . "` c SET"
			. " c.pageID = ( SELECT p.`pageID` FROM `" . counterize_pageTable() . "` p WHERE p.`url` = c.`url` ),"
			. " c.agentID = ( SELECT a.`agentID` FROM `" . counterize_agentsTable() . "` a WHERE a.`name` = c.`useragent` ),"
			. " c.refererID = ( SELECT r.`refererID` FROM `" . counterize_refererTable() . "` r WHERE r.`name` = c.`referer` )";
		$wpdb->query( $sql );

		$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` DROP `url`, DROP `useragent`, DROP `referer`;";
		$wpdb->query( $sql );

		$MajorVersion = 2;
		$MinorVersion = 0;
		$Revision     = 0;
	}

	if( $MajorVersion < 3 )
	{


		// now we have Version 2
		if( $MinorVersion < 4 )
		{
			update_option( 'counterize_whois', 'http://ws.arin.net/cgi-bin/whois.pl?queryinput=' );

			$sql = "CREATE TABLE `" . counterize_keywordTable() . "` (
				`keywordID` INT( 11 ) NOT NULL AUTO_INCREMENT,
				`keyword` VARCHAR( 255 ) NOT NULL,
				`count` INT( 11 ) NOT NULL DEFAULT '1',
				PRIMARY KEY ( `keywordID` ),
				UNIQUE `keyword` ( `keyword` )
				);";
			$wpdb->query( $sql );

			$sql = "ALTER IGNORE TABLE `" . counterize_refererTable() . "` ADD `keywordID` INT( 11 ) NOT NULL ;";
			$wpdb->query( $sql );

			// update 100 keywords a time
			$refererCount = $wpdb->get_var( "SELECT COUNT( * ) FROM `" . counterize_refererTable() . "`" );
			$loopCount = ceil( $refererCount / 100 );

			for( $i = 0; $i < $loopCount; $i++ )
			{
				$referers = $wpdb->get_results( "SELECT `refererID`, `name`, `count` FROM `" . counterize_refererTable() . "`" );
				foreach( $referers as $referer )
				{
					$keywordID = counterize_getKeywordID( $referer->name );
					$wpdb->query( "UPDATE IGNORE `" . counterize_refererTable() . "` SET `keywordID` = {$keywordID} WHERE `refererID` = {$referer->refererID}" );
					$wpdb->query( "UPDATE IGNORE `" . counterize_keywordTable() . "` SET `count` = `count` + {$referer->count} WHERE `keywordID` = {$keywordID}" );
				}
				// free memory
				unset( $referers );
			}
		}

		if( $MinorVersion < 8 )
		{
			update_option( 'counterize_maxWidth', 50 );

			$sql = "ALTER IGNORE TABLE `" . counterize_agentsTable() . "`"
				. " ADD `browserName` VARCHAR( 255 ) NOT NULL, "
				. " ADD `browserCode` VARCHAR( 255 ) NOT NULL, "
				. " ADD `browserVersion` VARCHAR( 255 ) NOT NULL, "
				. " ADD `osName` VARCHAR( 255 ) NOT NULL, "
				. " ADD `osCode` VARCHAR( 255 ) NOT NULL, "
				. " ADD `osVersion` VARCHAR( 255 ) NOT NULL";
			$wpdb->query( $sql );
		}

		if( $MinorVersion < 12 )
		{
			$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` ADD INDEX ( `timestamp` );";
			$wpdb->query( $sql );
		}

		if( $MinorVersion < 13 )
		{
			//commented to avoid users of Counterize II < 2.13 to see their precious IP data converted to a MD5 hash
			//$sql = "UPDATE `" . counterize_logTable() . "` SET `IP` = SUBSTR( MD5( `IP` ), 1, 16 );";
			//$wpdb->query( $sql );
		}

		if( $MinorVersion < 15 )
		{
			$sql = "ALTER IGNORE TABLE `" . counterize_agentsTable() . "`"
				. " ADD `browserURL` VARCHAR( 255 ) NOT NULL AFTER `browserVersion`,"
				. " ADD `osURL` VARCHAR( 255 ) NOT NULL AFTER `osVersion` ;";
			$wpdb->query( $sql );

			counterize_update_all_userAgents();
		}

		$MajorVersion = 3;
		$MinorVersion = 0;
		$Revision     = 0;

	}



	//now we have version 3!
	if( $MajorVersion == 3 )
	{
		if( $MinorVersion == 0 )
		{
			if( $Revision < 3 )
			{
				// default to 50 because a value of 0 leads to out of memory errors after some time.
				update_option( 'counterize_amount', 50 );
			}

			/*if(  $Revision < 10 )
			{
				//There was an issue with the botlist.txt being not found, so some bots made it to the Counterize DB. Now we delete them.
				counterize_delete_bots();
			}*/

			if(  $Revision < 12 )
			{
				//we update the old option system to the new one

				//create the new options
				$newoptions['amount']        = get_option( 'counterize_amount', 50 );
				$newoptions['amount2']       = get_option( 'counterize_amount2', 50 );
				$newoptions['maxwidth']      = get_option( 'counterize_maxWidth', 50 );
				$newoptions['excludedusers'] = get_option( 'counterize_excluded_users', 1 );
				$newoptions['excludedip']    = get_option( 'counterize_excluded', '192.168.1.1,127.0.0.1' );
				if( $newoptions['excludedip'] === FALSE || $newoptions['excludedip'] == '' )
				{
					$newoptions['excludedip'] = '192.168.1.1,127.0.0.1';
				}
				$newoptions['logbots'] = get_option( 'counterize_logbots', 'disabled' ) == 'disabled' ? 0 : 1;
				$newoptions['whois'] =   get_option( 'counterize_whois', 'http://www.ripe.net/whois?form_type=simple&amp;searchtext=' );
				$newoptions['enableip'] = 0;
				$newoptions['flushdb'] = '';
				$newoptions['refreshua'] = '';
				$newoptions['deletebots'] = '';


				//add the new options
				add_option( 'counterize_options', $newoptions );
				add_option( 'counterize_version', '3.0.12' );

				//delete the old unused options
				delete_option( 'counterize_amount' );
				delete_option( 'counterize_amount2' );
				delete_option( 'counterize_maxWidth' );
				delete_option( 'counterize_excluded_users' );
				delete_option( 'counterize_excluded' );
				delete_option( 'counterize_logbots' );
				delete_option( 'counterize_whois' );

				delete_option( 'counterize_MajorVersion' );
				delete_option( 'counterize_MinorVersion' );
				delete_option( 'counterize_Revision' );
			}

			if( $Revision < 13 )
			{
				/*update the database */

				//add a field
				$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` ADD `haship` VARCHAR( 16 ) NOT NULL AFTER `IP` ;";
				$wpdb->query( $sql );
				unset( $sql );

				//define a new hash when an IP address is in the IP field,
				// copy the current hash from the IP field if it's not an IP in the IP field,
				// set a default value for the previous md5 ips
				$sql = "UPDATE IGNORE `" . counterize_logTable() . "` SET"
					. " `haship` = IF( INSTR( `IP`, '.' ) > 0, LEFT( MD5( `IP` ), 16 ), `IP`), "
					. " `IP` = IF( INSTR( `IP`, '.' ) = 0, 'unavailable', `IP` ) "
					. " WHERE `IP` <> 'unavailable' ;";
				$wpdb->query( $sql );
				unset( $sql );

				update_option( 'counterize_version', '3.0.13' );
			}

			if( $Revision < 16 )
			{
				counterize_set_option( 'display_dashboard_capability', 'manage_options' );
				update_option( 'counterize_version', '3.0.16' );
			}
			/*if( $Revision < 17 )
			{
				//botlist.txt has been updated
				counterize_delete_bots();
			}*/

			if( $Revision < 18 )
			{
				counterize_set_option( 'geoip', 'http://whatismyipaddress.com/ip/' );
				update_option( 'counterize_version', '3.0.18' );
			}

			if( $Revision < 22 )
			{
				/*
				 * We add the country detection support.
				 *
Note from the plugin author:
The migration from Counterize 3.0.21 to 3.0.22 is particularly CPU demanding because we have to compute the country from each IP in the database.
If the webserver throws a timeout, you should be able to resume the upgrade where it stopped.
On my web server I was able to run the upgrade script in 13 seconds, for about 6500+ IP addresses in the database.
My apologies for any inconvenience caused by this upgrade.
Consider to make backups of your Counterize tables before upgrading.
Upgrading your database on a local web server should circumvent almost any limitations that your web hosting company may have set on your hosting.
In case something wrong happens, here is the SQL code to get back to the previous database state (assuming you were using 3.0.21 before, and that your wordpress tables prefix is 'wp_'):
ALTER IGNORE TABLE `wp_Counterize` DROP INDEX `IP` ;
ALTER IGNORE TABLE `wp_Counterize` DROP COLUMN `countryID` ;
DROP TABLE IF EXISTS `wp_Counterize_Countries` ;
UPDATE IGNORE `wp_options` SET `option_value`='3.0.21' WHERE `option_name`='counterize_version';
				 *
				 */

				//add the countryID field to the Counterize log table
				$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` "
					. " ADD `countryID` INT( 11 ) NOT NULL DEFAULT 0 ;";
				$wpdb->query( $sql );
				unset( $sql );

				//add an index to speed up IP lookups
				$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` "
					. " ADD INDEX `IP` ( `IP` ASC ) ;";
				if( ! counterize_index_exists( counterize_pageTable(), 'IP' ) )
				{
					$wpdb->query( $sql );
				}
				unset( $sql );

				//Create the new countries table
				$sql = "CREATE TABLE IF NOT EXISTS `" . counterize_countryTable() . "` ( "
					. " `countryID` INT( 11 ) NOT NULL AUTO_INCREMENT, "
					. " `code` VARCHAR( 16 ) NOT NULL, "
					. " `count` INT( 11 ) NOT NULL DEFAULT '1', "
					. " PRIMARY KEY ( `countryID` ), "
					. " UNIQUE `code` ( `code` ) "
					. " );";
				$wpdb->query( $sql );
				unset( $sql );

				//we get all the ip count and ip
				$sql_ips = "SELECT COUNT( `IP` ) AS NB, `IP` "
					. " FROM `" . counterize_logTable() . "` "
					. " WHERE `IP` <> 'unavailable' "
					. " AND `countryID` = 0 "
					. " GROUP BY `IP` "
					. " ORDER BY `IP` ASC";
				//$ips = $wpdb->get_results( $sql );

				// Count the total number of distinct IP
				$sql_ip_count = "SELECT COUNT( DISTINCT `IP` ) "
					. " FROM `" . counterize_logTable() . "` "
					. " WHERE `IP` <> 'unavailable' "
					. " AND `countryID` = 0 ;";

				$sql_country_count = "SELECT COUNT( `countryID` ) "
					. " FROM `" . counterize_countryTable() . "`;";

				$sql_country_update = "UPDATE IGNORE `" . counterize_countryTable() . "`"
					. " SET `count` = `count` + %d "
					. " WHERE `code` = '%s';\n";

				$sql_add_1 = "INSERT IGNORE INTO `" . counterize_countryTable() . "`"
					. " ( `code`, `count` ) VALUES";
				$sql_add_2_1 = " ( '%s', 0 )\n";
				$sql_add_2_2 = ", ( '%s', 0 )\n";
				$sql_add_3 = ";\n";

				$sql_country_id = "SELECT `countryID` "
					. " FROM `" . counterize_countryTable() . "`"
					. " WHERE `code` = '%s';";

				$sql_log_update = "UPDATE IGNORE `" . counterize_logTable() . "` "
					. " SET `countryID` = %d "
					. " WHERE `IP` = '%s';\n";

				//when necessary, we will not use $wpdb functions in order to speed up things
				$link = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, true );
				if( $link !== false )
				{
					$db_selected = mysql_select_db( DB_NAME, $link );
					if( $db_selected !== false )
					{
						//check if the countries have been added already. If yes, we skip it.
						if( $wpdb->get_var( $sql_country_count ) == 0 )
						{
							//we add all the countries in one time
							$sql_query = $sql_add_1;
							$first = true;
							global $counterize_countries;
							foreach( $counterize_countries as $key => $value )
							{
								if( $first )
								{
									$first = false;
									$sql_query .= sprintf( $sql_add_2_1, $key );
								}
								else
								{
									$sql_query .= sprintf( $sql_add_2_2, $key );
								}
							}
							$sql_query .= $sql_add_3;
							mysql_unbuffered_query( $sql_query, $link );
							unset( $sql_query );
						}

						//The critical loop that must be as fast as possible to avoid server timeouts.
						//Unfortunately in most servers PHP forbids the execution of multiple SQL queries so I have to run possibly thousand of separate queries.
						//There may be a faster way, but I searched a few hours and came with that solution.
						//It takes about 13 seconds for 6500+ records in my server.
						//If the server stops the script during the upgrade, user should be able to reload
						// the page and resume their upgrade because only records with countryID=0 are in this loop..
						//We will update ips by group of 100 on the advice of Daniel from chaosonline.de

						//Get the total number of distinct IP addresses
						$ip_count = $wpdb->get_var( $sql_ip_count );

						//compute how many groups of 100 IP addresses we have
						$loop_count = ceil( $ip_count / 100 );

						//die( $sql_ip_count . " || " . $ip_count . ", " . $loop_count );

						//Now begin the loop
						for( $i = 0; $i < $loop_count; $i++ )
						{
							//fetch a group of 100 IP addresses
							$sql_ip_count_tmp = $sql_ips . ' LIMIT ' . ( $i * 100 ) . ', 100 ;';

							$ips = $wpdb->get_results( $sql_ip_count_tmp );

							//For each IP address
							foreach( $ips as $ip )
							{
								//get the two chars country code corresponding to this IP
								$country_code = counterize_iptocountrycode( $ip->IP );

								//We update the counter of the country.
								$sql_query = sprintf( $sql_country_update, $ip->NB, $country_code );
								mysql_unbuffered_query( $sql_query, $link );

								//get the "countryID" corresponding to the country code we found earlier
								$sql_query = sprintf( $sql_country_id, $country_code );
								$results = mysql_query( $sql_query, $link );
								if( ( $country_id = mysql_result( $results, 0 ) ) !== FALSE )
								{
									//We update the countryID of the counterize log table
									$sql_query = sprintf( $sql_log_update, $country_id, $ip->IP );
									mysql_unbuffered_query( $sql_query, $link );
									mysql_free_result( $results );
								}
								unset( $country_code, $sql_query );
								//$country_id = $wpdb->get_var( sprintf( $sql_country_id, $country_code ) );
							}

							//frees some memory
							unset( $ips, $sql_ip_count_tmp );
						}
					}
				}
				mysql_close( $link );
				unset( $sql_ips, $sql_ip_count, $sql_country_count, $sql_country_update, $sql_add_1, $sql_add_2_1, $sql_add_2_2, $sql_add_3, $sql_country_id, $sql_log_update );
				update_option( 'counterize_version', '3.0.22' );
			}

			if( $Revision < 23 )
			{
				//correct a bug in History
				if( $wpdb->get_var( "SELECT COUNT(`countryID`) FROM `" . counterize_countryTable() . "` WHERE `code` = '00';" ) == 0 )
				{
					$sql = "INSERT IGNORE INTO `" . counterize_countryTable() . "` ( `code`, `count` ) VALUES ( '00', 0 );";
					$wpdb->query( $sql );
					unset( $sql );
				}

				$sql = "SELECT `countryID` "
					. " FROM `" . counterize_countryTable() . "` "
					. " WHERE `code` = '00';";
				$country_id = $wpdb->get_var( $sql );
				unset( $sql );

				$sql = "UPDATE IGNORE `" . counterize_logTable() . "` "
					. " SET `countryID` = %d "
					. " WHERE `IP` = %s;";
				$wpdb->query( $wpdb->prepare( $sql, $country_id, 'unavailable' ) );
				unset( $sql, $country_id );

				update_option( 'counterize_version', '3.0.23' );
			}

			if( $Revision < 27 )
			{
				/*
				 * We make some changes to the database to speed up things in the future.
				 */

				//Remove redundant indexes
				$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` "
					. " DROP INDEX `timestamp_%d` ;";
				for( $i = 1; $i <= 10; $i++ )
				{
					if( counterize_index_exists( counterize_logTable(), 'timestamp_' . $i ) )
					{
						$wpdb->query( sprintf( $sql, $i ) );
					}
				}
				unset( $sql );

				$sql = "ALTER IGNORE TABLE `" . counterize_logTable() . "` "
					. " ADD INDEX `%1\$s` ( `%1\$s` ASC ) ;";
				foreach( array( 'pageID', 'agentID', 'refererID', 'countryID' ) as $index )
				{
					if( ! counterize_index_exists( counterize_logTable(), $index ) )
					{
						$wpdb->query( sprintf( $sql, $index ) );
					}
				}
				unset( $sql );

				//Add new UNIQUE index
				$sql1 = "ALTER IGNORE TABLE `" . counterize_pageTable() . "` "
					. " DROP INDEX `url`, "
					. " ADD UNIQUE INDEX `url` ( `url` ASC ) ;";
				$sql2 = "ALTER IGNORE TABLE `" . counterize_pageTable() . "` "
					. " ADD UNIQUE INDEX `url` ( `url` ASC ) ;";
				if( counterize_index_exists( counterize_pageTable(), 'url' ) )
				{
					$wpdb->query( $sql1 );
				}
				else
				{
					$wpdb->query( $sql2 );
				}
				unset( $sql1, $sql2 );

				//Add new UNIQUE index
				$sql1 = "ALTER IGNORE TABLE `" . counterize_refererTable() . "` "
					. " DROP INDEX `name`, "
					. " ADD UNIQUE INDEX `name` ( `name` ASC ) ;";
				$sql2 = "ALTER IGNORE TABLE `" . counterize_refererTable() . "` "
					. " ADD UNIQUE INDEX `name` ( `name` ASC ) ;";
				if( counterize_index_exists( counterize_refererTable(), 'name' ) )
				{
					$wpdb->query( $sql1 );
				}
				else
				{
					$wpdb->query( $sql2 );
				}
				unset( $sql1, $sql2 );


				//Add new UNIQUE index
				$sql1 = "ALTER IGNORE TABLE `" . counterize_agentsTable() . "` "
					. " DROP INDEX `name`, "
					. " ADD UNIQUE INDEX `name` ( `name` ASC ) ;";
				$sql2 = "ALTER IGNORE TABLE `" . counterize_agentsTable() . "` "
					. " ADD UNIQUE INDEX `name` ( `name` ASC ) ;";
				if( counterize_index_exists( counterize_agentsTable(), 'name' ) )
				{
					$wpdb->query( $sql1 );
				}
				else
				{
					$wpdb->query( $sql2 );
				}
				unset( $sql1, $sql2 );

				//Add new UNIQUE index
				$sql1 = "ALTER IGNORE TABLE `" . counterize_keywordTable() . "` "
					. " DROP INDEX `keyword`, "
					. " ADD UNIQUE INDEX `keyword` ( `keyword` ASC ) ;";
				$sql2 = "ALTER IGNORE TABLE `" . counterize_keywordTable() . "` "
					. " ADD UNIQUE INDEX `keyword` ( `keyword` ASC ) ;";
				if( counterize_index_exists( counterize_keywordTable(), 'keyword' ) )
				{
					$wpdb->query( $sql1 );
				}
				else
				{
					$wpdb->query( $sql2 );
				}
				unset( $sql1, $sql2 );

				//Add new UNIQUE index
				$sql1 = "ALTER IGNORE TABLE `" . counterize_countryTable() . "` "
					. " DROP INDEX `code`, "
					. " ADD UNIQUE INDEX `code` ( `code` ASC ) ;";
				$sql2 = "ALTER IGNORE TABLE `" . counterize_countryTable() . "` "
					. " ADD UNIQUE INDEX `code` ( `code` ASC ) ;";
				if( counterize_index_exists( counterize_keywordTable(), 'keyword' ) )
				{
					$wpdb->query( $sql1 );
				}
				else
				{
					$wpdb->query( $sql2 );
				}
				unset( $sql1, $sql2 );

				//we updated the bots
				//counterize_delete_bots();

				update_option( 'counterize_version', '3.0.27' );
			}

			if( $Revision < 28 )
			{
				//we fix the regression introduced in the 3.0.27 version about country code detection
				$sql_ips = "SELECT COUNT( `IP` ) AS NB, `IP`, l.`countryID` "
					. " FROM `" . counterize_logTable() . "` l, `" . counterize_countryTable() . "` c"
					. " WHERE `IP` <> 'unavailable' "
					. " AND l.`countryID` = c.`countryID` "
					. " AND c.`code` = 'unknown' "
					. " GROUP BY `IP` "
					. " ORDER BY `IP` ASC ;";

				$sql_country_id = "SELECT `countryID` "
								. "FROM `" . counterize_countryTable()  . "` "
								. "WHERE `code` = '%s' ;";

				$sql_update_log = "UPDATE `" . counterize_logTable() . "` "
							. "SET `countryID` = %d "
							. "WHERE `countryID` = %d "
							. "AND `IP` = '%s' ;";

				$sql_update_country = "UPDATE `" . counterize_countryTable() . "` "
							. "SET `count` = `count` + %d "
							. "WHERE `countryID` = %d ;";

				$sql_delete_country = "DELETE FROM `" . counterize_countryTable() . "` "
									. "WHERE `code` = 'unknown' ;";

				$ips = $wpdb->get_results( $sql_ips );
				foreach( $ips as $ip )
				{
					//get the two chars country code corresponding to this IP
					$country_code = counterize_iptocountrycode( $ip->IP );

					//get the actual country code
					$country_id = $wpdb->get_var( sprintf( $sql_country_id, $country_code ) );

					//update the log table with the new country id
					$wpdb->query( sprintf( $sql_update_log, $country_id, $ip->countryID, $ip->IP ) );

					//update the country table with the count of the previous erroneous country id for this IP
					$wpdb->query( sprintf( $sql_update_country, $ip->NB, $country_id ) );

					unset( $country_code, $country_id );
				}
				//Finally, delete the "unknown" country
				$wpdb->query( $sql_delete_country );

				//frees memory
				unset( $ips, $sql_ips, $sql_country_id, $sql_update_log, $sql_update_country, $sql_delete_country );

				update_option( 'counterize_version', '3.0.28' );
			}

			if( $Revision < 32 )
			{
				/*
				 * correct the postID bug
				 */

				//try to find valid post ID with the URL
				$pageslist = $wpdb->get_results( 'SELECT `url` FROM `' . counterize_pageTable() . '`' );
				$update_page = 'UPDATE IGNORE `' . counterize_pageTable() . '` SET `postID` = %d WHERE `url` = %s';
				foreach( $pageslist as $onepage )
				{
					$postid = url_to_postid( $onepage->url );
					if( $postid > 0 )
					{
						$wpdb->query( $wpdb->prepare( $update_page, $postid, $onepage->url ) );
					}
				}
				unset( $pageslist, $update_page );

				//delete pages not in the log table
				$delete_pages = 'DELETE FROM `' . counterize_pageTable() . '` '
							.  ' WHERE `pageID` NOT IN ( SELECT `pageID` FROM `'. counterize_logTable() .'` ) ';
				$wpdb->query( $delete_pages );
				unset( $delete_pages );

				//delete log table records that are not in the pages table
				$delete_records = 'DELETE FROM `' . counterize_logTable() . '` '
							.  ' WHERE `pageID` NOT IN ( SELECT `pageID` FROM `'. counterize_pageTable() .'` ) ';
				$wpdb->query( $delete_records );
				unset( $delete_records );

				//update the pages table count field
				$update_pages = 'UPDATE `' . counterize_pageTable() . '` '
							.  ' SET `count` = ( SELECT COUNT( 1 ) '
							.  ' FROM `' . counterize_logTable() . '` '
							.  ' WHERE `' . counterize_logTable() . '`.`pageID` = `' . counterize_pageTable() . '`.`pageID` ) ';
				$wpdb->query( $update_pages );
				unset( $update_pages );

				/*
				 * Update the other tables count field because I noticed some differences over time
				 */

				 //update the referers table count field
				$update_referers = 'UPDATE `' . counterize_refererTable() . '` '
							.  ' SET `count` = ( SELECT COUNT( 1 ) '
							.  ' FROM `' . counterize_logTable() . '` '
							.  ' WHERE `' . counterize_logTable() . '`.`refererID` = `' . counterize_refererTable() . '`.`refererID` ) ';
				$wpdb->query( $update_referers );
				unset( $update_referers );

				 //update the useragents table count field
				$update_agents = 'UPDATE `' . counterize_agentsTable() . '` '
							.  ' SET `count` = ( SELECT COUNT( 1 ) '
							.  ' FROM `' . counterize_logTable() . '` '
							.  ' WHERE `' . counterize_logTable() . '`.`agentID` = `' . counterize_agentsTable() . '`.`agentID` ) ';
				$wpdb->query( $update_agents );
				unset( $update_agents );

				 //update the countries table count field
				$update_countries = 'UPDATE `' . counterize_countryTable() . '` '
							.  ' SET `count` = ( SELECT COUNT( 1 ) '
							.  ' FROM `' . counterize_logTable() . '` '
							.  ' WHERE `' . counterize_logTable() . '`.`countryID` = `' . counterize_countryTable() . '`.`countryID` ) ';
				$wpdb->query( $update_countries );
				unset( $update_countries );

				 //update the keywords table count field
				$update_keywords = 'UPDATE `' . counterize_keywordTable() . '` '
							.  ' SET `count` = ( SELECT COUNT( 1 ) '
							.  ' FROM `' . counterize_refererTable() . '` '
							.  ' WHERE `' . counterize_refererTable() . '`.`keywordID` = `' . counterize_keywordTable() . '`.`keywordID` ) ';
				$wpdb->query( $update_keywords );
				unset( $update_keywords );

				update_option( 'counterize_version', '3.0.32' );
			}

			if( $Revision < 34 )
			{
				/*
				 * add outlinks tracking
				 */

				//remove any outlinks table created previously
				$sql = 'DROP TABLE IF EXISTS `' . counterize_outlinksTable() . ';';
				$wpdb->query( $sql );

				//remove index and column
				$sql = 'ALTER IGNORE TABLE `' . counterize_logTable() . '` '
					. ' DROP INDEX `outlinkID`, '
					. ' DROP COLUMN `outlinkID` ; ';
				$wpdb->query( $sql );

				//create the new outlinks table
				$sql = 'CREATE  TABLE `' . counterize_outlinksTable() . '`( '
					. ' `outlinkID` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT , '
					. ' `count` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0 , '
					. ' `url` VARCHAR( 255 ) NOT NULL , '
					. ' PRIMARY KEY ( `outlinkID` ) , '
					. ' UNIQUE INDEX `url` ( `url` ASC ) , '
					. ' KEY `count` ( `count` ) ) ; ';
				$wpdb->query( $sql );

				//add a new column and index in the log table
				$sql = 'ALTER TABLE `' . counterize_logTable() . '` '
					. ' ADD COLUMN `outlinkID` INT( 11 ) NOT NULL DEFAULT 0 AFTER `countryID` , '
					. ' ADD INDEX `outlinkID` ( `outlinkID` ASC ) ; ';
				$wpdb->query( $sql );

				update_option( 'counterize_version', '3.0.34' );
			}

			$MinorVersion = 1;
			$Revision = 0;
		}

		//we now have version 3.1.0

		if( $MinorVersion == 1 )
		{
			if( $Revision == 0 )
			{
				/*
				 * update the country table with the new countries
				 */

				//we prepare the query
				$sql_add_1 = "INSERT IGNORE INTO `" . counterize_countryTable() . "`"
					. " ( `code`, `count` ) VALUES";
				$sql_add_2 = " ( '%s', 0 )";
				$sql_add_3 = ";";

				//we get the current country codes and store them in an array
				$sql = 'SELECT code FROM ' . counterize_countryTable();
				$rows = $wpdb->get_results( $sql );
				foreach( $rows as $row )
				{
					$dbcountries[] = $row->code;
				}

				//we begin to build a query
				$sql = $sql_add_1;
				$comma = '';

				//we loop through the list of countries
				global $counterize_countries;
				foreach( $counterize_countries as $code => $country )
				{
					//if a country code is not in the database, then
					if( ! in_array( $code, $dbcountries ) )
					{
						//here we only got the new countries, we continue to build the query
						$sql .= $comma . sprintf( $sql_add_2, $code );

						//the comma will be non-empty string from now on
						$comma = ',';
					}
				}
				$sql .= $sql_add_3;

				//run the query
				$wpdb->query( $sql );

				//adds indexes (super slow otherwise !)
				$sql = 'ALTER IGNORE TABLE `' . counterize_refererTable() . '` '
					. ' ADD INDEX ( `keywordID` ) ; ';
				$wpdb->query( $sql );
				$sql = 'ALTER IGNORE TABLE `' . counterize_keywordTable() . '` '
					. ' ADD INDEX ( `count` ) ; ';
				$wpdb->query( $sql );
				$sql = 'ALTER IGNORE TABLE `' . counterize_countryTable() . '` '
					. ' ADD INDEX ( `count` ) ; ';
				$wpdb->query( $sql );
				$sql = 'ALTER IGNORE TABLE `' . counterize_pageTable() . '` '
					. ' ADD INDEX ( `postID` ) ; ';
				$wpdb->query( $sql );

				//set the new options to their default values
				counterize_set_option( 'enable_hostname_lookup', FALSE );
				counterize_set_option( 'enableemailreports'    , FALSE );
				counterize_set_option( 'recipientlist'         , get_bloginfo( 'admin_email' ) );
				counterize_set_option( 'reportperiod'          , 'never' );
				counterize_set_option( 'customperiod'          , 0 );
				counterize_set_option( 'mailsubjectoverride'   , '' );
				counterize_set_option( 'reportwhat'            , 'all' );
				counterize_set_option( 'sendreportnow'         , FALSE );
				counterize_set_option( 'next_report_timestamp' , 0 );
			}

			if( $Revision < 5 )
			{
				$Revision = 5;
			}
			// Here we have Counterize 3.1.5
		}
	}

	if( $should_delete_bots )
	{
		//force to update the user agents table
		counterize_delete_bots_new();
	}
	if( $should_update_user_agents )
	{
		//force to update the user agents table
		counterize_update_all_userAgents();
	}

	// Set new Version
	update_option( 'counterize_version', "{$vermaj}.{$vermin}.{$verrev}" );

}

//return true if the index exists.
function counterize_index_exists( $table, $index )
{
	global $wpdb;
	$sql = "SHOW INDEX FROM `{$table}` WHERE Key_name='{$index}'";
	return $wpdb->query( $sql ) > 0;
}
?>