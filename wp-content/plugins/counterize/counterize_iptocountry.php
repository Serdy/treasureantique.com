<?php

// security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	die( 'There is nothing to see here.' );
}

$countries = array();

require_once( COUNTERIZE_PLUGIN_DIR . "/ip_files/countries.php" );

$counterize_countries = $countries;
unset( $countries );

//returns the 2 characters country code for the specified IP address
function counterize_iptocountrycode( $ip )
{
	$numbers = explode( '.', $ip );

	if( count( $numbers ) != 4 )
	{
		return '00';
	}

	$ranges = array();
	$country = 'unknown';

	include( COUNTERIZE_PLUGIN_DIR . '/ip_files/' . $numbers[0] . '.php' );

	$code = ( $numbers[0] * 0x1000000 ) + ( $numbers[1] * 0x10000 ) + ( $numbers[2] * 0x100 ) + $numbers[3];

	foreach( $ranges as $key => $value )
	{
		if( $key <= $code && $value[0] >= $code )
		{
			$country = $value[1];
			break;
		}
	}
	return $country;
}

//returns the 3 characters country code from the 2 characters country code in parameter
function counterize_get_countrycode3( $code2 )
{
	global $counterize_countries;
	if( $code2 != '00' && isset( $counterize_countries[$code2] ) )
	{
		return $counterize_countries[$code2][0];
	}
	else
	{
		return '???';
	}
}

//returns the complete country name from the 2 characters country code in parameter
function counterize_get_countryname( $code2 )
{
	global $counterize_countries;
	if( $code2 != '00' && isset( $counterize_countries[$code2] ) )
	{
		return $counterize_countries[$code2][1];
	}
	else
	{
		return __( 'Unknown', COUNTERIZE_TD );
	}
}

//return a img tag of the country
function counterize_get_flag_tag( $code, $alt )
{
	$alt = htmlspecialchars( $alt );
	$code = htmlspecialchars( $code );
	$src = counterize_get_flag_url( $code );
	$res = '';

	if( $src != '' )
	{
		$res .= "
								<img
									src='{$src}'
									alt='" . sprintf( __( 'National flag of %s', COUNTERIZE_TD ), $alt ) . "'
									title='" . counterize_get_countryname( $code ) . " ({$code})'
									height='" . COUNTERIZE_ICON_SIZE . "'
									class='countryflag'
								 />
									";
	}

	return $res;
}

//return the URL of the country flag
function counterize_get_flag_url( $code )
{
	if( file_exists( COUNTERIZE_PLUGIN_DIR . "/ip_files/flags/{$code}.gif" ) )
	{
		return COUNTERIZE_PLUGIN_URL . "/ip_files/flags/{$code}.gif";
	}
	elseif( file_exists( COUNTERIZE_PLUGIN_DIR . "/ip_files/flags/" . strtolower( $code ) . ".gif" ) )
	{
		return COUNTERIZE_PLUGIN_URL . "/ip_files/flags/" . strtolower( $code ) . ".gif";
	}
	else
	{
		return '';
	}
}

?>