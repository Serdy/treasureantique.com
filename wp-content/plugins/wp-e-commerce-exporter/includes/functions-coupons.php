<?php
// Returns a list of Coupon export columns
function wpsc_ce_get_coupon_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'coupon_code',
		'label' => __( 'Coupon Code', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'coupon_value',
		'label' => __( 'Coupon Value', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'use_once',
		'label' => __( 'Use Once', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'active',
		'label' => __( 'Active', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'every_product',
		'label' => __( 'Apply to All Products', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'start',
		'label' => __( 'Valid From', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'expiry',
		'label' => __( 'Valid To', 'wpsc_ce' ),
		'default' => 1
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wpsc_ce' ),
		'default' => 1
	);
*/

	// Allow Plugin/Theme authors to add support for additional Coupon columns
	$fields = apply_filters( 'wpsc_ce_coupon_fields', $fields );

	$remember = wpsc_ce_get_option( 'coupons_fields' );
	if( $remember ) {
		$remember = maybe_unserialize( $remember );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( !array_key_exists( $fields[$i]['name'], $remember ) )
				$fields[$i]['default'] = 0;
		}
	}

	switch( $format ) {

		case 'summary':
			$output = array();
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ )
				$output[$fields[$i]['name']] = 'on';
			return $output;
			break;

		case 'full':
		default:
			return $fields;

	}

}

// Returns the export column header label based on an export column slug
function wpsc_ce_get_coupon_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_coupon_fields();
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
	}
	return $output;

}
?>