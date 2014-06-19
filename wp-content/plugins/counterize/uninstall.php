<?php
/*
 * This will uninstall Counterize complely. Counterize options will be deleted,
 * and the Counterize tables will be dropped from the WordPress database.
 *
 * Make backups if you don't want to loose your data!
 */

/* Security checks */
if( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! current_user_can( 'delete_plugins' ) )
{
	exit();
}

/* Delete Counterize options */

//old unused options
delete_option( 'counterize_amount' );
delete_option( 'counterize_amount2' );
delete_option( 'counterize_logbots' );
delete_option( 'counterize_whois' );
delete_option( 'counterize_maxWidth' );
delete_option( 'counterize_MajorVersion' );
delete_option( 'counterize_MinorVersion' );
delete_option( 'counterize_Revision' );
delete_option( 'counterize_excluded_users' );

//current options
delete_option( 'counterize_options' );
delete_option( 'counterize_version' );


/* Delete Counterize tables */
global $wpdb;

$counterize_table_kw = $wpdb->prefix . 'Counterize_Keywords';
$counterize_table_co = $wpdb->prefix . 'Counterize_Countries';
$counterize_table_pg = $wpdb->prefix . 'Counterize_Pages';
$counterize_table_rf = $wpdb->prefix . 'Counterize_Referers';
$counterize_table_ua = $wpdb->prefix . 'Counterize_UserAgents';
$counterize_table_ol = $wpdb->prefix . 'Counterize_Outlinks';
$counterize_table    = $wpdb->prefix . 'Counterize';

$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table_kw}" );
$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table_co}" );
$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table_pg}" );
$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table_rf}" );
$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table_ua}" );
$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table_ol}" );
$wpdb->query( "DROP TABLE IF EXISTS {$counterize_table}" );

/* Counterize is now *completely* uninstalled! */

?>