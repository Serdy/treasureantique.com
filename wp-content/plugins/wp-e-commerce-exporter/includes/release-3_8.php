<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Add Store Export to WordPress Administration menu
	function wpsc_ce_admin_page_item( $menu = array() ) {

		$title = __( 'Store Export', 'wpsc_ce' );
		$link = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_ce' ), 'edit.php' );
		$description = __( 'Export store details out of WP e-Commerce into a CSV-formatted file.', 'wpsc_ce' );

		$menu[] = array( 'title' => $title, 'link' => $link, 'description' => $description );

		return $menu;

	}
	add_filter( 'wpsc_sm_store_admin_page', 'wpsc_ce_admin_page_item', 1 );

	// Returns number of an Export type prior to export, used on Store Exporter screen
	function wpsc_ce_return_count( $dataset ) {

		global $wpdb;

		$count_sql = null;
		switch( $dataset ) {

			case 'products':
				$post_type = 'wpsc-product';
				$count = wp_count_posts( $post_type );
				break;

			case 'variations':
				$post_type = 'wpsc-variation';
				$count = wp_count_posts( $post_type );
				break;

			case 'images':
				$post_type = 'attachment';
				$count_sql = $wpdb->prepare( "SELECT COUNT(`id`) FROM `" . $wpdb->posts . "` WHERE `post_type` = '%s' AND `post_mime_type` LIKE 'image/%'", $post_type );
				break;

			case 'files':
				$post_type = 'wpsc-product-file';
				$count = wp_count_posts( $post_type );
				break;

			case 'categories':
				$term_taxonomy = 'wpsc_product_category';
				$count = wp_count_terms( $term_taxonomy );
				break;

			case 'tags':
				$term_taxonomy = 'product_tag';
				$count = wp_count_terms( $term_taxonomy );
				break;

			case 'orders':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_purchase_logs`";
				break;

			case 'coupons':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_coupon_codes`";
				break;

			case 'customers':
				$count_sql = "SELECT COUNT( DISTINCT wpsc_submited_form_data.`value` ) FROM `" . $wpdb->prefix . "wpsc_checkout_forms` as wpsc_checkout_forms, `" . $wpdb->prefix . "wpsc_submited_form_data` as wpsc_submited_form_data WHERE wpsc_checkout_forms.`id` = wpsc_submited_form_data.`form_id` AND wpsc_checkout_forms.unique_name = 'billingemail'";
				break;

			/* 3rd Party */

			case 'wishlist':
				$post_type = 'wpsc-wishlist';
				$count = wp_count_posts( $post_type );
				break;

			case 'enquiries':
				$post_type = 'wpsc-enquiry';
				$count = wp_count_posts( $post_type );
				break;

			case 'credit-cards':
				$post_type = 'offline_payment';
				$count = wp_count_posts( $post_type );
				break;

			case 'related-products':
				break;

		}
		if( isset( $count ) || $count_sql ) {
			if( isset( $count ) ) {
				if( is_object( $count ) ) {
					$count_object = $count;
					$count = 0;
					foreach( $count_object as $key => $item )
						$count = $item + $count;
				}
				return $count;
			} else {
				if( $count_sql )
					$count = $wpdb->get_var( $count_sql );
				else
					$count = 0;
			}
			return $count;
		} else {
			return 0;
		}

	}

	// Export process for CSV file
	function wpsc_ce_export_dataset( $dataset, $args = array() ) {

		global $wpdb, $export;

		$csv = '';
		if( $export->bom )
			$csv .= chr(239) . chr(187) . chr(191) . '';
		$separator = $export->delimiter;
		$export->args = $args;
		foreach( $dataset as $datatype ) {

			$csv = '';
			switch( $datatype ) {

				// Products
				case 'products':
					$fields = wpsc_ce_get_product_fields( 'summary' );
					if( $export->fields = array_intersect_assoc( $fields, $export->fields ) ) {
						if( function_exists( 'wpsc_cf_install' ) )
							$export->args['custom_fields'] = array();
						if( class_exists( 'wpec_simple_product_options_admin' ) )
							$export->args['simple_product_options'] = array();
						foreach( $export->fields as $key => $field ) {
							$export->columns[] = wpsc_ce_get_product_field( $key );
							if( strpos( $key, 'attribute_' ) !== false )
								$export->args['custom_fields'][] = str_replace( 'attribute_', '', $key );
							if( strpos( $key, 'simple_product_option_' ) !== false )
								$export->args['simple_product_options'][] = str_replace( 'simple_product_option_', '', $key );
						}
					}
					$export->data_memory_start = wpsc_ce_current_memory_usage();
					if( $products = wpsc_ce_get_products( $export->args ) ) {
						$export->total_rows = count( $products );
						$size = count( $export->columns );
						$export->total_columns = $size;
						for( $i = 0; $i < $size; $i++ ) {
							if( $i == ( $size - 1 ) )
								$csv .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
							else
								$csv .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
						}
						unset( $export->columns );
						foreach( $products as $product ) {
							foreach( $export->fields as $key => $field ) {
								if( isset( $product->$key ) ) {
									if( is_array( $field ) ) {
										foreach( $field as $array_key => $array_value ) {
											if( !is_array( $array_value ) )
												$csv .= wpsc_ce_escape_csv_value( $array_value, $export->delimiter, $export->escape_formatting );
										}
									} else {
										$csv .= wpsc_ce_escape_csv_value( $product->$key, $export->delimiter, $export->escape_formatting );
									}
								}
								$csv .= $separator;
							}
							$csv = substr( $csv, 0, -1 ) . "\n";
						}
						unset( $products, $product );
					}
					$export->data_memory_end = wpsc_ce_current_memory_usage();
					break;

				// Categories
				case 'categories':
					$fields = wpsc_ce_get_category_fields( 'summary' );
					if( $export->fields = array_intersect_assoc( $fields, $export->fields ) ) {
						foreach( $export->fields as $key => $field )
							$export->columns[] = wpsc_ce_get_category_field( $key );
					}
					$export->data_memory_start = wpsc_ce_current_memory_usage();
					if( $categories = wpsc_ce_get_product_categories( $export->args ) ) {
						$export->total_rows = count( $categories );
						$size = count( $export->columns );
						$export->total_columns = $size;
						for( $i = 0; $i < $size; $i++ ) {
							if( $i == ( $size - 1 ) )
								$csv .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
							else
								$csv .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
						}
						unset( $export->columns );
						foreach( $categories as $category ) {
							foreach( $export->fields as $key => $field ) {
								if( isset( $category->$key ) )
									$csv .= wpsc_ce_escape_csv_value( wpsc_ce_clean_html( $category->$key ), $export->delimiter, $export->escape_formatting );
								$csv .= $separator;
							}
							$csv = substr( $csv, 0, -1 ) . "\n";
						}
						unset( $categories, $category );
					}
					$export->data_memory_end = wpsc_ce_current_memory_usage();
					break;

				// Tags
				case 'tags':
					$fields = wpsc_ce_get_tag_fields( 'summary' );
					if( $export->fields = array_intersect_assoc( $fields, $export->fields ) ) {
						foreach( $export->fields as $key => $field )
							$export->columns[] = wpsc_ce_get_tag_field( $key );
					}
					$export->data_memory_start = wpsc_ce_current_memory_usage();
					if( $tags = wpsc_ce_get_product_tags( $export->args ) ) {
						$export->total_rows = count( $tags );
						$size = count( $export->columns );
						$export->total_columns = $size;
						for( $i = 0; $i < $size; $i++ ) {
							if( $i == ( $size - 1 ) )
								$csv .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
							else
								$csv .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
						}
						unset( $export->columns );
						foreach( $tags as $tag ) {
							foreach( $export->fields as $key => $field ) {
								if( isset( $tag->$key ) )
									$csv .= wpsc_ce_escape_csv_value( wpsc_ce_clean_html( $tag->$key ), $export->delimiter, $export->escape_formatting );
								$csv .= $separator;
							}
							$csv = substr( $csv, 0, -1 ) . "\n";
						}
						unset( $tags, $tag );
					}
					$export->data_memory_end = wpsc_ce_current_memory_usage();
					break;

				// Orders
				case 'orders':
				// Customers
				case 'customers':
				// Coupons
				case 'coupons':
					$csv = apply_filters( 'wpsc_ce_export_dataset', $datatype, $export );
					break;

			}
			if( $csv ) {
				$csv = wpsc_ce_file_encoding( $csv );
				$csv = utf8_decode( $csv );
				if( WPSC_CE_DEBUG )
					set_transient( WPSC_CE_PREFIX . '_debug_log', base64_encode( $csv ), wpsc_ce_get_option( 'timeout', MINUTE_IN_SECONDS ) );
				else
					return $csv;
			} else {
				return false;
			}

		}

	}

	/* End of: WordPress Administration */

}

// Returns a list of WP e-Commerce Products to export process
function wpsc_ce_get_products( $args = array() ) {

	$limit_volume = -1;
	$offset = 0;
	$product_categories = false;
	$product_tags = false;
	$product_status = false;
	if( $args ) {
		$limit_volume = $args['limit_volume'];
		$offset = $args['offset'];
		if ( isset( $args['custom_fields'] ) && !empty( $args['custom_fields'] ) )
			$custom_fields = $args['custom_fields'];
		if ( isset( $args['simple_product_options'] ) && !empty( $args['simple_product_options'] ) )
			$simple_product_options = $args['simple_product_options'];
		if( !empty( $args['product_categories'] ) )
			$product_categories = $args['product_categories'];
		if( !empty( $args['product_tags'] ) )
			$product_tags = $args['product_tags'];
		if( !empty( $args['product_status'] ) )
			$product_status = $args['product_status'];
		$orderby = $args['product_orderby'];
		$order = $args['product_order'];
	}
	$post_type = 'wpsc-product';
	$args = array(
		'post_type' => $post_type,
		'numberposts' => $limit_volume,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'post_status' => wpsc_ce_post_statuses( array( 'inherit' ) ),
		'cache_results' => false
	);
	if( $product_categories ) {
		$term_taxonomy = 'wpsc_product_category';
		$args['tax_query'] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_categories
			)
		);
	}
	if( $product_tags ) {
		$term_taxonomy = 'product_tag';
		$args['tax_query'] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_tags
			)
		);
	}
	if( $product_status )
		$args['post_status'] = wpsc_ce_post_statuses( $product_status, true );
	$products = get_posts( $args );
	if( $products ) {
		foreach( $products as $key => $product ) {
			$product_data = wpsc_ce_get_product_meta( $product->ID );

			$products[$key]->parent_id = '';
			$products[$key]->parent_sku = '';
			if( $product->post_parent ) {
				$products[$key]->parent_id = $product->post_parent;
				$products[$key]->parent_sku = get_product_meta( $product->post_parent, 'sku', true );
			}
			$products[$key]->product_id = $product->ID;
			$products[$key]->sku = wpsc_ce_clean_html( get_product_meta( $product->ID, 'sku', true ) );
			$products[$key]->name = wpsc_ce_clean_html( get_the_title( $product->ID ) );
			$products[$key]->is_variation = false;
				$term_taxonomy = 'wpsc-variation';
			if( $product->variations = wp_get_object_terms( $product->ID, $term_taxonomy ) ) {
				$products[$key]->is_variation = true;
				if( count( $product->variations ) == 1 ) {
					$product->variations = $product->variations[0];
					$variation = get_term( $product->variations->term_id, $term_taxonomy );
					$parent_variation = get_term( $product->variations->parent, $term_taxonomy );
					$products[$key]->name = wpsc_ce_clean_html( str_replace( ' (' . $variation->name . ')', '', $product->post_title ) . '|' . $parent_variation->name . '|' . $variation->name );
				}
			}
			$products[$key]->description = wpsc_ce_clean_html( $product->post_content );
			$products[$key]->additional_description = wpsc_ce_clean_html( $product->post_excerpt );
			$products[$key]->price = 0;
			if( get_product_meta( $product->ID, 'price', true ) )
				$products[$key]->price = get_product_meta( $product->ID, 'price', true );
				$products[$key]->sale_price = 0;
			if( get_product_meta( $product->ID, 'special_price', true ) )
				$products[$key]->sale_price = get_product_meta( $product->ID, 'special_price', true );
			$products[$key]->slug = $product->post_name;
			$products[$key]->permalink = get_permalink( $product->ID );
			$products[$key]->post_date = wpsc_ce_format_date( $product->post_date );
			$products[$key]->post_modified = wpsc_ce_format_date( $product->post_modified );
			$products[$key]->weight = wpsc_ce_convert_product_raw_weight( $product_data['weight'], $product_data['weight_unit'] );
			if( !$products[$key]->weight )
				$products[$key]->weight = 0;
			if( isset( $product_data['weight_unit'] ) )
				$products[$key]->weight_unit = $product_data['weight_unit'];
			if( !$products[$key]->weight_unit )
				$products[$key]->weight_unit = 'kg';
			if( isset( $product_data['dimensions']['height'] ) )
				$products[$key]->height = trim( $product_data['dimensions']['height'] );
			if( !$products[$key]->height )
				$products[$key]->height = 0;
			if( isset( $product_data['dimensions']['height_unit'] ) )
				$products[$key]->height_unit = $product_data['dimensions']['height_unit'];
			if( !$products[$key]->height_unit )
				$products[$key]->height_unit = 'in';
			if( isset( $product_data['dimensions']['width'] ) )
				$products[$key]->width = trim( $product_data['dimensions']['width'] );
			if( !$products[$key]->width )
				$products[$key]->width = 0;
			if( isset( $product_data['dimensions']['width_unit'] ) )
				$products[$key]->width_unit = $product_data['dimensions']['width_unit'];
			if( !$products[$key]->width_unit )
				$products[$key]->width_unit = 'in';
			if( isset( $product_data['dimensions']['length'] ) )
				$products[$key]->length = trim( $product_data['dimensions']['length'] );
			if( !$products[$key]->length )
				$products[$key]->length = 0;
			if( isset( $product_data['dimensions']['length_unit'] ) )
				$products[$key]->length_unit = $product_data['dimensions']['length_unit'];
			if( !$products[$key]->length_unit )
				$products[$key]->length_unit = 'in';
			$products[$key]->category = wpsc_ce_clean_html( wpsc_ce_get_product_assoc_categories( $product->ID ) );
			$products[$key]->tag = wpsc_ce_clean_html( wpsc_ce_get_product_assoc_tags( $product->ID ) );
			$products[$key]->image = wpsc_ce_get_product_assoc_images( $product->ID );
			$products[$key]->quantity_limited = $product_data['quantity_limited'];
			$products[$key]->quantity = get_product_meta( $product->ID, 'stock', true );
			if( $products[$key]->quantity_limited && empty( $products[$key]->quantity ) )
				$products[$key]->quantity = 0;
			$products[$key]->notify_oos = __( 'No', 'wpsc_ce' );
			if( isset( $product_data['notify_when_none_left'] ) )
				$products[$key]->notify_oos = __( 'Yes', 'wpsc_ce' );
			$products[$key]->unpublish_oos = __( 'No', 'wpsc_ce' );
			if( isset( $product_data['unpublish_when_none_left'] ) )
				$products[$key]->unpublish_oos = __( 'Yes', 'wpsc_ce' );
			if( isset( $product_data['external_link'] ) )
				$products[$key]->external_link = $product_data['external_link'];
			if( isset( $product_data['external_link_text'] ) )
				$products[$key]->external_link_text = wpsc_ce_clean_html( $product_data['external_link_text'] );
			if( isset( $product_data['external_link_target'] ) )
				$products[$key]->external_link_target = $product_data['external_link_target'];
			if( isset( $product_data['shipping']['local'] ) )
				$products[$key]->local_shipping = $product_data['shipping']['local'];
			if( isset( $product_data['shipping']['international'] ) )
				$products[$key]->international_shipping = $product_data['shipping']['international'];
			$products[$key]->no_shipping = __( 'No', 'wpsc_ce' );
			if( $product_data['no_shipping'] == 1 )
				$products[$key]->no_shipping = __( 'Yes', 'wpsc_ce' );
			$products[$key]->taxable_amount = $product_data['wpec_taxes_taxable_amount'];
			$products[$key]->tax_bands = wpsc_ce_format_tax_bands( $product_data['wpec_taxes_band'] );
			$products[$key]->not_taxable = __( 'No', 'wpsc_ce' );
			if( $products[$key]->taxable_amount )
				$products[$key]->not_taxable = __( 'Yes', 'wpsc_ce' );
			$products[$key]->product_status = wpsc_ce_format_product_status( $product->post_status, $product );
			$products[$key]->comment_status = wpsc_ce_format_comment_status( $product->comment_status, $product );

			/* Allow Plugin/Theme authors to add support for additional Product columns */
			$products[$key] = apply_filters( 'wpsc_ce_product_item', $products[$key], $product->ID );

			// Advanced Google Product Feed
			if( function_exists( 'wpec_gpf_install' ) ) {
				$products[$key]->gpf_data = get_post_meta( $product->ID, '_wpec_gpf_data', true );
				$products[$key]->gpf_availability = wpsc_ce_format_gpf_availability( $product->gpf_data['availability'] );
				$products[$key]->gpf_condition = wpsc_ce_format_gpf_condition( $product->gpf_data['condition'] );
				$products[$key]->gpf_brand = $product->gpf_data['brand'];
				$products[$key]->gpf_product_type = $product->gpf_data['product_type'];
				$products[$key]->gpf_google_product_category = $product->gpf_data['google_product_category'];
				$products[$key]->gpf_gtin = $product->gpf_data['gtin'];
				$products[$key]->gpf_mpn = $product->gpf_data['mpn'];
				$products[$key]->gpf_gender = $product->gpf_data['gender'];
				$products[$key]->gpf_age_group = $product->gpf_data['age_group'];
				$products[$key]->gpf_color = $product->gpf_data['color'];
				$products[$key]->gpf_size = $product->gpf_data['size'];
			}

			// All in One SEO Pack
			if( function_exists( 'aioseop_activate' ) ) {
				$products[$key]->aioseop_keywords = get_post_meta( $product->ID, '_aioseop_keywords', true );
				$products[$key]->aioseop_description = get_post_meta( $product->ID, '_aioseop_description', true );
				$products[$key]->aioseop_title = get_post_meta( $product->ID, '_aioseop_title', true );
				$products[$key]->aioseop_titleatr = get_post_meta( $product->ID, '_aioseop_titleatr', true );
				$products[$key]->aioseop_menulabel = get_post_meta( $product->ID, '_aioseop_menulabel', true );
			}

			// Custom Fields
			if( isset( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field )
					$product->{'attribute_' . $custom_field} = get_product_meta( $product->ID, $custom_field, true );
			}

			// Related Products
			if( isset( $product_data['wpsc_rp_manual'] ) )
				$products[$key]->related_products = wpsc_ce_get_product_assoc_related_products( $product->ID );

			// Simple Product Options
			if( isset( $simple_product_options ) ) {
				foreach( $simple_product_options as $simple_product_option )
					$product->{'simple_product_option_' . $simple_product_option} = wpsc_ce_get_product_assoc_simple_product_options( $product->ID, $simple_product_option );
			}

		}
	}
	return $products;

}

function wpsc_ce_get_product_meta( $product_id = 0 ) {

	$product_data = array();
	if( $product_id ) {
		$defaults = array(
			'weight' => '',
			'weight_unit' => '',
			'dimensions' => array( 'height_unit' => '', 'width_unit' => '', 'length_unit' => '', 'height' => '', 'width' => '', 'length' => '' ),
			'shipping'   => array( 'local' => '', 'international' => '' ),
			'no_shipping' => '',
			'display_weight_as' => '',
			'quantity_limited' => '',
			'external_link' => '',
			'wpec_taxes_band' => '',
			'wpec_taxes_taxable_amount' => ''
		);
		$product_data = get_post_meta( $product_id, '_wpsc_product_metadata', true );
		$product_data = wp_parse_args( $product_data, $defaults );
	}
	return $product_data;

}

// Returns the Product Images associated to a specific Product
function wpsc_ce_get_product_assoc_images( $product_id = 0 ) {

	global $export;

	$output = '';
	$post_type = 'attachment';
	$args = array(
		'post_type' => $post_type,
		'post_parent' => $product_id,
		'post_status' => 'inherit',
		'post_mime_type' => 'image',
		'numberposts' => -1
	);
	$images = get_children( $args );
	if( $images ) {
		/* Check for Featured Image */
		$featured_image = get_post_meta( $product_id, '_thumbnail_id', true );
		if( $featured_image ) {
			$image = get_post( $featured_image );
			$output .= $image->guid . $export->category_separator;
		} else {
			$featured_image = 0;
		}
		foreach( $images as $image ) {
			if( $featured_image <> $image->ID )
				$output .= $image->guid . $export->category_separator;
		}
		unset( $featured_image );
		$output = substr( $output, 0, -1 );
	}
	unset( $images );
	return $output;

}

// Returns Product Categories associated to a specific Product
function wpsc_ce_get_product_assoc_categories( $product_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = 'wpsc_product_category';
	if( $product_id )
		$categories = wp_get_object_terms( $product_id, $term_taxonomy );
	if( $categories ) {
		$size = count( $categories );
		for( $i = 0; $i < $size; $i++ ) {
			if( $categories[$i]->parent == '0' ) {
				$output .= $categories[$i]->name . $export->category_separator;
			} else {
				// Check if Parent -> Child
				$parent_category = get_term( $categories[$i]->parent, $term_taxonomy );
				// Check if Parent -> Child -> Subchild
				if( $parent_category->parent == '0' ) {
					$output .= $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
					$output = str_replace( $parent_category->name . $export->category_separator, '', $output );
				} else {
					$root_category = get_term( $parent_category->parent, $term_taxonomy );
					$output .= $root_category->name . '>' . $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
					$output = str_replace( array(
						$root_category->name . '>' . $parent_category->name . $export->category_separator,
						$parent_category->name . $export->category_separator
					), '', $output );
				}
				unset( $root_category, $parent_category );
			}
		}
		$output = substr( $output, 0, -1 );
	} else {
		$output .= __( 'Uncategorized', 'wpsc_ce' );
	}
	return $output;

}

// Returns Related Products associated to a specific Product
function wpsc_ce_get_product_assoc_related_products( $product_id ) {

	global $export;

	$output = '';
	$product_data = maybe_unserialize( get_product_meta( $product_id, 'product_metadata', true ) );
	if( isset( $product_data['wpsc_rp_manual'] ) && $product_data['wpsc_rp_manual'] ) {
		foreach( $product_data['wpsc_rp_manual'] as $related_product )
			$output .= $related_product . $export->category_separator;
		$output = substr( $output, 0, -1 );
	}
	return $output;

}

// Returns Simple Product Options associated to a specific Product
function wpsc_ce_get_product_assoc_simple_product_options( $product_id = 0, $product_option = '' ) {

	global $export;

	$output = '';
	if( $product_id ) {
		$term_taxonomy = 'wpec_product_option';
		$term = get_term_by( 'slug', $product_option, $term_taxonomy );
		$simple_product_options = wp_get_object_terms( $product_id, $term_taxonomy );
		if( $simple_product_options ) {
			$size = count( $simple_product_options );
			for( $i = 0; $i < $size; $i++ ) {
				if( $simple_product_options[$i]->parent == $term->term_id )
					$output .= $simple_product_options[$i]->name . $export->category_separator;
			}
			$output = substr( $output, 0, -1 );
		}
	}
	return $output;

}

// Returns a list of WP e-Commerce Product Categories to export process
function wpsc_ce_get_product_categories( $args = array(), $tree_structure = false ) {

	global $export;

	$output = '';
	if( $args ) {
		$orderby = $args['category_orderby'];
		$order = $args['category_order'];
	}
	$term_taxonomy = 'wpsc_product_category';
	$args = array(
		'orderby' => $orderby,
		'order' => $order,
		'hide_empty' => 0
	);
	$categories = get_terms( $term_taxonomy, $args );
	if( $tree_structure ) {
		$output = array();
		if( $categories ) {
			$size = count( $categories );
			for( $i = 0; $i < $size; $i++ ) {
				if( $categories[$i]->parent == '0' ) {
					$output[] = $categories[$i]->name;
				} else {
					// Check if Parent -> Child
					$parent_category = get_term( $categories[$i]->parent, $term_taxonomy );
					// Check if Parent -> Child -> Subchild
					if( $parent_category->parent == '0' ) {
						$temp = $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
						$output[] = substr( str_replace( $parent_category->name . $export->category_separator, '', $temp ), 0, -1 );
					} else {
						$root_category = get_term( $parent_category->parent, $term_taxonomy );
						$temp = $root_category->name . '>' . $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
						$output[] = substr( str_replace( array(
							$root_category->name . '>' . $parent_category->name . $export->category_separator,
							$parent_category->name . $export->category_separator
						), '', $temp ), 0, -1 );
					}
					unset( $root_category, $parent_category );
				}
			}
		}
	} else {
		if( $categories ) {
			foreach( $categories as $key => $category ) {
				$categories[$key]->parent_id = $category->parent;
			}
		}
		if( $categories )
			$output = $categories;
	}
	return $output;

}

// Returns a list of Category export columns
function wpsc_ce_get_category_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'term_id',
		'label' => __( 'Term ID', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Category Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Category Slug', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent Term ID', 'wpsc_ce' ),
		'default' => 1
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wpsc_ce' ),
		'default' => 1
	);
*/

	// Allow Plugin/Theme authors to add support for additional Category columns
	$fields = apply_filters( 'wpsc_ce_category_fields', $fields );

	$remember = wpsc_ce_get_option( 'categories_fields' );
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
function wpsc_ce_get_category_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_category_fields();
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