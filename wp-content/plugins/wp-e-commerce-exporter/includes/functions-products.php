<?php
// Returns Product Tags associated to a specific Product
function wpsc_ce_get_product_assoc_tags( $product_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = 'product_tag';
	$tags = wp_get_object_terms( $product_id, $term_taxonomy );
	if( $tags ) {
		$size = count( $tags );
		for( $i = 0; $i < $size; $i++ ) {
			$tag = get_term( $tags[$i]->term_id, $term_taxonomy );
			$output .= $tag->name . $export->category_separator;
		}
		$output = substr( $output, 0, -1 );
	}
	return $output;

}
// Returns a list of Product export columns
function wpsc_ce_get_product_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent ID', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'parent_sku',
		'label' => __( 'Parent SKU', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'sku',
		'label' => __( 'Product SKU', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Product Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Slug', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'permalink',
		'label' => __( 'Permalink', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'additional_description',
		'label' => __( 'Additional Description', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'post_date',
		'label' => __( 'Product Published', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'post_modified',
		'label' => __( 'Product Modified', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'price',
		'label' => __( 'Price', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'sale_price',
		'label' => __( 'Sale Price', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'weight',
		'label' => __( 'Weight', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'weight_unit',
		'label' => __( 'Weight Unit', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'height',
		'label' => __( 'Height', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'height_unit',
		'label' => __( 'Height Unit', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'width',
		'label' => __( 'Width', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'width_unit',
		'label' => __( 'Width Unit', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'length',
		'label' => __( 'Length', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'length_unit',
		'label' => __( 'Length Unit', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'category',
		'label' => __( 'Category', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'tag',
		'label' => __( 'Tag', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Image', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'quantity',
		'label' => __( 'Quantity', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'notify_oos',
		'label' => __( 'Notify OOS', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'unpublish_oos',
		'label' => __( 'Unpublish OOS', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'file_download',
		'label' => __( 'File Download', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'external_link',
		'label' => __( 'External Link', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'external_link_text',
		'label' => __( 'External Link Text', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'external_link_target',
		'label' => __( 'External Link Target', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'local_shipping',
		'label' => __( 'Local Shipping Fee', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'international_shipping',
		'label' => __( 'International Shipping Fee', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'no_shipping',
		'label' => __( 'No Shipping', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'taxable_amount',
		'label' => __( 'Taxable Amount', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'tax_bands',
		'label' => __( 'Tax Bands', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'not_taxable',
		'label' => __( 'Not Taxable', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'product_status',
		'label' => __( 'Product Status', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'comment_status',
		'label' => __( 'Comment Status', 'wpsc_ce' ),
		'default' => 1
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wpsc_ce' ),
		'default' => 1
	);
*/

	// Allow Plugin/Theme authors to add support for additional Product columns
	$fields = apply_filters( 'wpsc_ce_product_fields', $fields );

	/* Advanced Google Product Feed */
	if( function_exists( 'wpec_gpf_install' ) ) {
		$fields[] = array(
			'name' => 'gpf_availability',
			'label' => __( 'Advanced Google Product Feed - Availability', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_condition',
			'label' => __( 'Advanced Google Product Feed - Condition', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_brand',
			'label' => __( 'Advanced Google Product Feed - Brand', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_productype',
			'label' => __( 'Advanced Google Product Feed - Product Type', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_google_product_category',
			'label' => __( 'Advanced Google Product Feed - Google Product Category', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_gtin',
			'label' => __( 'Advanced Google Product Feed - Global Trade Item Number (GTIN)', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_mpn',
			'label' => __( 'Advanced Google Product Feed - Manufacturer Part Number (MPN)', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_gender',
			'label' => __( 'Advanced Google Product Feed - Gender', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_agegroup',
			'label' => __( 'Advanced Google Product Feed - Age Group', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_colour',
			'label' => __( 'Advanced Google Product Feed - Colour', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'gpf_size',
			'label' => __( 'Advanced Google Product Feed - Size', 'wpsc_ce' ),
			'default' => 0
		);
	}

	/* All in One SEO Pack */
	if( function_exists( 'aioseop_activate' ) ) {
		$fields[] = array(
			'name' => 'aioseop_keywords',
			'label' => __( 'All in One SEO - Keywords', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'aioseop_description',
			'label' => __( 'All in One SEO - Description', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'aioseop_title',
			'label' => __( 'All in One SEO - Title', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'aioseop_title_attributes',
			'label' => __( 'All in One SEO - Title Attributes', 'wpsc_ce' ),
			'default' => 0
		);
		$fields[] = array(
			'name' => 'aioseop_menu_label',
			'label' => __( 'All in One SEO - Menu Label', 'wpsc_ce' ),
			'default' => 0
		);
	}

	/* Custom Fields */
	if( function_exists( 'wpsc_cf_install' ) ) {
		$attributes = maybe_unserialize( get_option( 'wpsc_cf_data' ) );
		if( !empty( $attributes ) ) {
			foreach( $attributes as $key => $attribute ) {
				$fields[] = array(
					'name' => sprintf( 'attribute_%s', $attribute['slug'] ),
					'label' => sprintf( __( 'Attribute: %s', 'wpsc_ce' ), $attribute['name'] ),
					'default' => 1
				);
			}
			unset( $attributes, $attribute );
		}
	}

	/* Related Products */
	if( function_exists( 'wpsc_rp_pd_options_addons' ) ) {
		$fields[] = array(
			'name' => 'related_products',
			'label' => __( 'Related Products', 'wpsc_ce' ),
			'default' => 0
		);
	}

	/* Simple Product Options */
	if( class_exists( 'wpec_simple_product_options_admin' ) ) {
		$args = array(
			'hide_empty' => false,
			'parent' => 0
		);
		$product_options = get_terms( 'wpec_product_option', $args );
		if( $product_options ) {
			foreach( $product_options as $product_option ) {
				$fields[] = array(
					'name' => sprintf( 'simple_product_option_%s', $product_option->slug ),
					'label' => sprintf( __( 'Simple Product Option: %s', 'wpsc_ce' ), $product_option->name ),
					'default' => 1
				);
			}
		}
	}

	$remember = wpsc_ce_get_option( 'products_fields' );
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
function wpsc_ce_get_product_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_product_fields();
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