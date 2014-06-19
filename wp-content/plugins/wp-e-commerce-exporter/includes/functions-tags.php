<?php
// Returns a list of WP e-Commerce Product Tags to export process
function wpsc_ce_get_product_tags( $args = array() ) {

	$output = '';
	if( $args ) {
		$orderby = $args['tag_orderby'];
		$order = $args['tag_order'];
	}
	$term_taxonomy = 'product_tag';
	$args = array(
		'orderby' => $orderby,
		'order' => $order,
		'hide_empty' => 0
	);
	$tags = get_terms( $term_taxonomy, $args );
	if( $tags )
		$output = $tags;
	return $output;

}

// Returns a list of Product Tag export columns
function wpsc_ce_get_tag_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'term_id',
		'label' => __( 'Term ID', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Tag Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Tag Slug', 'wpsc_ce' ),
		'default' => 1
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wpsc_ce' ),
		'default' => 1
	);
*/

	// Allow Plugin/Theme authors to add support for additional Product Tag columns
	$fields = apply_filters( 'wpsc_ce_tag_fields', $fields );

	$remember = wpsc_ce_get_option( 'tags_fields' );
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
function wpsc_ce_get_tag_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_tag_fields();
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