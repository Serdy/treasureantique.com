<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Returns number of an Export type prior to export, used on Store Exporter screen
	function wpsc_ce_return_count( $dataset ) {

		global $wpdb;

		$count_sql = null;
		switch( $dataset ) {

			/* WP e-Commerce */

			case 'products':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_list`";
				break;

			case 'variations':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_variations`";
				break;

			case 'images':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_images`";
				break;

			case 'files':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_files`";
				break;

			case 'categories':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_categories`";
				break;

			case 'tags':
				$term_taxonomy = 'product_tag';
				$count = wp_count_terms( $term_taxonomy );
				break;

			case 'orders':
			case 'coupons':
			case 'customers':
				if( function_exists( 'wpsc_cd_return_count' ) )
					$count = wpsc_cd_return_count( $dataset );
				break;

			/* 3rd Party */

			case 'wishlist':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_wishlist`";
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
				$count = $wpdb->get_var( $count_sql );
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
					$columns = array(
						__( 'SKU', 'wpsc_ce' ),
						__( 'Product Name', 'wpsc_ce' ),
						__( 'Description', 'wpsc_ce' ),
						__( 'Additional Description', 'wpsc_ce' ),
						__( 'Price', 'wpsc_ce' ),
						__( 'Sale Price', 'wpsc_ce' ),
						__( 'Slug', 'wpsc_ce' ),
						__( 'Permalink', 'wpsc_ce' ),
						__( 'Weight', 'wpsc_ce' ),
						__( 'Weight Unit', 'wpsc_ce' ),
						__( 'Height', 'wpsc_ce' ),
						__( 'Height Unit', 'wpsc_ce' ),
						__( 'Width', 'wpsc_ce' ),
						__( 'Width Unit', 'wpsc_ce' ),
						__( 'Length', 'wpsc_ce' ),
						__( 'Length Unit', 'wpsc_ce' ),
						__( 'Category', 'wpsc_ce' ),
						__( 'Tag', 'wpsc_ce' ),
						__( 'Image', 'wpsc_ce' ),
						__( 'Quantity', 'wpsc_ce' ),
						__( 'File Download', 'wpsc_ce' ),
						__( 'External Link', 'wpsc_ce' ),
						__( 'Merchant Notes', 'wpsc_ce' ),
						__( 'Local Shipping Fee', 'wpsc_ce' ),
						__( 'International Shipping Fee', 'wpsc_ce' ),
						__( 'Product Status', 'wpsc_ce' )
					);
					$size = count( $columns );
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$csv .= wpsc_ce_escape_csv_value( $columns[$i] ) . "\n";
						else
							$csv .= wpsc_ce_escape_csv_value( $columns[$i] ) . $separator;
					}
					$products_sql = "SELECT `id` AS ID, `name`, `description`, `additional_description`, `publish` as status, `price`, `weight`, `weight_unit`, `pnp` as local_shipping, `international_pnp` as international_shipping, `quantity`, `special_price` as sale_price FROM `" . $wpdb->prefix . "wpsc_product_list` WHERE `active` = 1";
					$products = $wpdb->get_results( $products_sql );
					if( $products ) {
						foreach( $products as $product ) {

							$product->sku = get_product_meta( $product->ID, 'sku' );
							$product->slug = get_product_meta( $product->ID, 'url_name' );
							$product->permalink = wpsc_product_url( $product->ID );
							$product->dimensions = get_product_meta( $product->ID, 'dimensions' );
							if( $product->dimensions ) {
								$product->height = $product->dimensions['height'];
								$product->height_unit = $product->dimensions['height_unit'];
								$product->width = $product->dimensions['width'];
								$product->width_unit = $product->dimensions['width_unit'];
								$product->length = $product->dimensions['length'];
								$product->length_unit = $product->dimensions['length_unit'];
							}
							$product->external_link = get_product_meta( $product->ID, 'external_link' );
							$product->merchant_notes = get_product_meta( $product->ID, 'merchant_notes' );
							$product->category = wpsc_ce_get_product_categories( $product->ID );
							$product->tags = wpsc_ce_get_product_tags( $product->ID );

							foreach( $product as $key => $value )
								$product->$key = escape_csv_value( $value );

							$csv .= 
								$product->sku . $separator . 
								$product->name . $separator . 
								$product->description . $separator . 
								$product->additional_description . $separator . 
								$product->price . $separator . 
								$product->sale_price . $separator . 
								$product->permalink . $separator . 
								$product->weight . $separator . 
								$product->weight_unit . $separator . 
								$product->height . $separator . 
								$product->height_unit . $separator . 
								$product->width . $separator . 
								$product->width_unit . $separator . 
								$product->length . $separator . 
								$product->length_unit . $separator . 
								$product->category . $separator . 
								$product->tag . $separator . 
								$product->image . $separator . 
								$product->quantity . $separator . 
								$product->file_download . $separator . 
								$product->external_link . $separator . 
								$product->merchant_notes . $separator . 
								$product->local_shipping . $separator . 
								$product->international_shipping . $separator . 
								$product->status . 
							"\n";

						}
						unset( $products, $product );
					}
					break;

				/* Categories */
				case 'categories':
					$columns = array(
						__( 'Category', 'wpsc_ce' )
					);
					$size = count( $columns );
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$csv .= escape_csv_value( $columns[$i] ) . "\n";
						else
							$csv .= escape_csv_value( $columns[$i] ) . $separator;
					}
					$categories = wpsc_ce_get_product_categories();
					if( $categories ) {
						foreach( $categories as $category ) {
							$csv .= 
								$category->name
								 . 
							"\n";
						}
						unset( $categories, $category );
					}
					break;

				/* Tags */
				case 'tags':
					$term_taxonomy = 'product_tag';
					$args = array(
						'hide_empty' => 0
					);
					$tags = get_terms( $term_taxonomy, $args );
					if( $tags ) {
						$columns = array(
							__( 'Tags', 'wpsc_ce' )
						);
						for( $i = 0; $i < count( $columns ); $i++ ) {
							if( $i == ( count( $columns ) - 1 ) )
								$csv .= $columns[$i] . "\n";
							else
								$csv .= $columns[$i] . $separator;
						}
						foreach( $tags as $tag ) {
							$csv .= 
								$tag->name
								 . 
							"\n";
						}
						unset( $tags, $tag );
					}
					break;

				/* Orders */
				case 'orders':
				/* Customers */
				case 'customers':
				/* Coupons */
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

	// Returns Product Categories associated to a specific Product
	function wpsc_ce_get_product_categories( $product_id = null ) {

		global $export, $wpdb;

		$output = '';
		if( $product_id ) {
			$categories_sql = $wpdb->prepare( "SELECT wpsc_product_categories.`name` FROM `" . $wpdb->prefix . "wpsc_item_category_assoc` as item_category_assoc, `" . $wpdb->prefix . "wpsc_product_categories` as wpsc_product_categories WHERE item_category_assoc.category_id = wpsc_product_categories.id AND item_category_assoc.`product_id` = %d", $product_id );
			$categories = $wpdb->get_results( $categories_sql );
			if( $categories ) {
				foreach( $categories as $category ) {
					$output .= $category->name . $export->category_separator;
				}
				$output = substr( $output, 0, -1 );
			} else {
				$output .= __( 'Uncategorized', 'wpsc_ce' );
			}
		}
		return $output;

	}

	// Returns a list of WP e-Commerce Product Categories to export process
	function wpsc_ce_get_product_assoc_categories() {

		global $wpdb;

		$output = '';
		$categories_sql = "SELECT `name` FROM `" . $wpdb->prefix . "wpsc_product_categories` WHERE `active` = 1";
		$categories = $wpdb->get_results( $categories_sql );
		if( $categories )
			$output = $categories;
		return $output;

	}

	if( !function_exists( 'wpsc_find_purchlog_status_name' ) ) {
		function wpsc_find_purchlog_status_name( $status ) {

			global $wpdb;

			$output = $status;
			if( !empty( $status ) ) {
				$status_name_sql = $wpdb->prepare( "SELECT `name` FROM `" . $wpdb->prefix . "wpsc_purchase_statuses` WHERE `id` = %d LIMIT 1", $status );
				$status_name = $wpdb->get_var( $status_name_sql );
				if( $status_name )
					$output = $status_name;
			}
			return $output;

		}
	}

	/* End of: WordPress Administration */

}
?>