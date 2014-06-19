<?php
// Returns a list of Customer export columns
function wpsc_ce_get_customer_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_full_name',
		'label' => __( 'Billing: Full Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_first_name',
		'label' => __( 'Billing: First Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_last_name',
		'label' => __( 'Billing: Last Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_street_address',
		'label' => __( 'Billing: Street Address', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_city',
		'label' => __( 'Billing: City', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_state',
		'label' => __( 'Billing: State (prefix)', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_zip_code',
		'label' => __( 'Billing: ZIP Code', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_country',
		'label' => __( 'Billing: Country (prefix)', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_country_full',
		'label' => __( 'Billing: Country', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_phone_number',
		'label' => __( 'Billing: Phone Number', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'billing_email',
		'label' => __( 'E-mail Address', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_full_name',
		'label' => __( 'Shipping: Full Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_first_name',
		'label' => __( 'Shipping: First Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_last_name',
		'label' => __( 'Shipping: Last Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_street_address',
		'label' => __( 'Shipping: Street Address', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_city',
		'label' => __( 'Shipping: City', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_state',
		'label' => __( 'Shipping: State (prefix)', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_zip_code',
		'label' => __( 'Shipping: ZIP Code', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_country',
		'label' => __( 'Shipping: Country (prefix)', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_country_full',
		'label' => __( 'Shipping: Country', 'wpsc_ce' ),
		'default' => 1
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wpsc_ce' ),
		'default' => 1
	);
*/

	// Allow Plugin/Theme authors to add support for additional Customer columns
	$fields = apply_filters( 'wpsc_ce_customer_fields', $fields );

	$remember = wpsc_ce_get_option( 'customers_fields' );
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
function wpsc_ce_get_customer_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_customer_fields();
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