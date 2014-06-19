<?php
function wpsc_ce_file_encoding( $content = '' ) {

	global $export;

	if( function_exists( 'mb_convert_encoding' ) ) {
		$to_encoding = $export->encoding;
		// $from_encoding = 'auto';
		$from_encoding = 'ISO-8859-1';
		if( !empty( $to_encoding ) )
			$content = mb_convert_encoding( trim( $content ), $to_encoding, $from_encoding );
	}
	return $content;

}

function wpsc_ce_clean_html( $content = '' ) {

	$content = trim( $content );
	// $content = str_replace( ',', '&#44;', $content );
	// $content = str_replace( "\n", '<br />', $content );
	return $content;

}

if( !function_exists( 'escape_csv_value' ) ) {
	function escape_csv_value( $value ) {

		$value = str_replace( '"', '""', $value ); // First off escape all " and make them ""
		$value = str_replace( PHP_EOL, ' ', $value );
		return '"' . $value . '"'; // If I have new lines or commas escape them

	}
}

function wpsc_ce_display_memory( $memory = 0 ) {

	$output = '-';
	if( !empty( $output ) )
		$output = sprintf( __( '%s MB', 'wpsc_ce' ), $memory );
	echo $output;

}

function wpsc_ce_display_time_elapsed( $from, $to ) {

	$output = __( '1 second', 'wpsc_ce' );
	$time = $to - $from;
	$tokens = array (
		31536000 => __( 'year', 'wpsc_ce' ),
		2592000 => __( 'month', 'wpsc_ce' ),
		604800 => __( 'week', 'wpsc_ce' ),
		86400 => __( 'day', 'wpsc_ce' ),
		3600 => __( 'hour', 'wpsc_ce' ),
		60 => __( 'minute', 'wpsc_ce' ),
		1 => __( 'second', 'wpsc_ce' )
	);
	foreach ($tokens as $unit => $text) {
		if ($time < $unit) continue;
		$numberOfUnits = floor($time / $unit);
		$output = $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
	}
	return $output;

}

// This function escapes all cells in 'Excel' CSV escape formatting of a CSV file, also converts HTML entities to plain-text
function wpsc_ce_escape_csv_value( $value = '', $delimiter = ',', $format = 'all' ) {

	$output = $value;
	if( !empty( $output ) ) {
		$output = str_replace( '"', '""', $output );
		// $output = str_replace( PHP_EOL, ' ', $output );
		$output = wp_specialchars_decode( $output );
		$output = str_replace( PHP_EOL, "\r\n", $output );
		switch( $format ) {
	
			case 'all':
				$output = '"' . $output . '"';
				break;

			case 'excel':
				if( strstr( $output, $delimiter ) !== false || strstr( $output, "\r\n" ) !== false )
					$output = '"' . $output . '"';
				break;
	
		}
	}
	return $output;

}

function wpsc_ce_count_object( $object = 0, $exclude_post_types = array() ) {

	$count = 0;
	if( is_object( $object ) ) {
		if( $exclude_post_types ) {
			$size = count( $exclude_post_types );
			for( $i = 0; $i < $size; $i++ ) {
				if( isset( $object->$exclude_post_types[$i] ) )
					unset( $object->$exclude_post_types[$i] );
			}
		}
		if( !empty( $object ) ) {
			foreach( $object as $key => $item )
				$count = $item + $count;
		}
	} else {
		$count = $object;
	}
	return $count;

}

function wpsc_ce_format_product_status( $product_status = '', $product ) {

	$output = $product_status;
	if( $product_status ) {
		switch( $product_status ) {

			case 'publish':
				$output = __( 'Publish', 'wpsc_ce' );
				break;

			case 'draft':
				$output = __( 'Draft', 'wpsc_ce' );
				break;

			case 'trash':
				$output = __( 'Trash', 'wpsc_ce' );
				break;

		}
	}
	if( $product->is_variation && $product_status <> 'draft' )
		$output = '';
	return $output;

}

function wpsc_ce_format_comment_status( $comment_status, $product ) {

	$output = $comment_status;
	switch( $comment_status ) {

		case 'open':
			$output = __( 'Open', 'wpsc_ce' );
			break;

		case 'closed':
			$output = __( 'Closed', 'wpsc_ce' );
			break;

	}
	if( $product->is_variation )
		$output = '';
	return $output;

}

function wpsc_ce_format_tax_bands( $tax_band_id ) {

	if( !empty( $tax_band_id ) ) {
		$tax_bands = get_option( 'wpec_taxes_bands', true );
		print_r( $tax_bands );
	}

}

function wpsc_ce_format_gpf_availability( $availability = null ) {

	$output = '';
	if( $availability ) {
		switch( $availability ) {

			case 'in stock':
				$output = __( 'In Stock', 'wpsc_ce' );
				break;

			case 'available for order':
				$output = __( 'Available For Order', 'wpsc_ce' );
				break;

			case 'preorder':
				$output = __( 'Pre-order', 'wpsc_ce' );
				break;

		}
	}
	return $output;

}

function wpsc_ce_format_gpf_condition( $condition ) {

	switch( $condition ) {

		case 'new':
			$output = __( 'New', 'wpsc_ce' );
			break;

		case 'refurbished':
			$output = __( 'Refurbished', 'wpsc_ce' );
			break;

		case 'used':
			$output = __( 'Used', 'wpsc_ce' );
			break;

	}
	return $output;

}

function wpsc_ce_format_product_filters( $product_filters = array() ) {

	$output = array();
	if( !empty( $product_filters ) ) {
		foreach( $product_filters as $product_filter ) {
			$output[] = $product_filter;
		}
	}
	return $output;

}

function wpsc_ce_format_user_role_filters( $user_role_filters = array() ) {

	$output = array();
	if( !empty( $user_role_filters ) ) {
		foreach( $user_role_filters as $user_role_filter ) {
			$output[] = $user_role_filter;
		}
	}
	return $output;

}

function wpsc_ce_format_user_role_label( $user_role = '' ) {

	global $wp_roles;

	$output = $user_role;
	if( $user_role ) {
		$user_roles = wpsc_ce_get_user_roles();
		if( isset( $user_roles[$user_role] ) )
			$output = ucfirst( $user_roles[$user_role]['name'] );
	}
	return $output;

}

function wpsc_ce_convert_product_raw_weight( $weight = null, $weight_unit = null ) {

	$output = '';
	if( $weight && $weight_unit )
		$output = wpsc_convert_weight( $weight, 'pound', $weight_unit, false );
	return $output;

}

function wpsc_ce_format_order_date( $date ) {

	$output = $date;
	if( $date )
		$output = str_replace( '/', '-', $date );
	return $output;

}

function wpsc_ce_format_date( $date = '' ) {

	$output = '';
	if( $date )
		$output = mysql2date( wpsc_ce_get_option( 'date_format', 'd/m/Y' ), $date );
	return $output;

}

function wpsc_ce_expand_country_name( $country_prefix = '' ) {

	global $wpdb;

	$output = $country_prefix;
	if( $country_prefix ) {
		$country_sql = $wpdb->prepare( "SELECT `country` FROM `" . $wpdb->prefix . "wpsc_currency_list` WHERE `isocode` = '%s' LIMIT 1", $country_prefix );
		$country = $wpdb->get_var( $country_sql );
		if( $country )
			$output = $country;
	}
	return $output;

}
?>