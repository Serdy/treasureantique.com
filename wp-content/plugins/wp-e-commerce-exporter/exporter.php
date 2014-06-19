<?php
/*
Plugin Name: WP e-Commerce - Store Exporter
Plugin URI: http://www.visser.com.au/wp-ecommerce/plugins/exporter/
Description: Export store details out of WP e-Commerce into a CSV-formatted file.
Version: 1.5.4
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
License: GPL2
*/

define( 'WPSC_CE_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'WPSC_CE_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'WPSC_CE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSC_CE_PREFIX', 'wpsc_ce' );

// Turn this on to enable additional debugging options at export time
define( 'WPSC_CE_DEBUG', false );

include_once( WPSC_CE_PATH . 'includes/functions.php' );
include_once( WPSC_CE_PATH . 'includes/functions-alternatives.php' );
include_once( WPSC_CE_PATH . 'includes/common.php' );

switch( wpsc_get_major_version() ) {

	case '3.7':
		include_once( WPSC_CE_PATH . 'includes/release-3_7.php' );
		break;

	case '3.8':
		include_once( WPSC_CE_PATH . 'includes/release-3_8.php' );
		break;

}

function wpsc_ce_i18n() {

	load_plugin_textdomain( 'wpsc_ce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'wpsc_ce_i18n' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Add Export and Docs links to the Plugins screen
	function wpsc_ce_add_settings_link( $links, $file ) {

		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if( $file == $this_plugin ) {
			$docs_url = 'http://www.visser.com.au/docs/';
			$docs_link = sprintf( '<a href="%s" target="_blank">' . __( 'Docs', 'wpsc_ce' ) . '</a>', $docs_url );
			if( function_exists( 'wpsc_find_purchlog_status_name' ) )
				$export_link = sprintf( '<a href="%s">' . __( 'Export', 'wpsc_ce' ) . '</a>', add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_ce' ), 'edit.php' ) );
			else
				$export_link = sprintf( '<a href="%s">' . __( 'Export', 'wpsc_ce' ) . '</a>', add_query_arg( 'page', 'wpsc_ce', 'admin.php' ) );
			array_unshift( $links, $docs_link );
			array_unshift( $links, $export_link );
		}
		return $links;

	}
	add_filter( 'plugin_action_links', 'wpsc_ce_add_settings_link', 10, 2 );

	// Load CSS and jQuery scripts for Store Exporter screen
	function wpsc_ce_enqueue_scripts( $hook ) {

		$pages = array( 'wpsc-product_page_wpsc_ce', 'store_page_wpsc_ce' );
		if( in_array( $hook, $pages ) ) {
			// Date Picker
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/templates/admin/jquery-ui-datepicker.css', __FILE__ ) );

			// Chosen
			wp_enqueue_script( 'jquery-chosen', plugins_url( '/js/chosen.jquery.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'jquery-chosen', plugins_url( '/templates/admin/chosen.css', __FILE__ ) );

			// Common
			wp_enqueue_style( 'wpsc_ce_styles', plugins_url( '/templates/admin/wpsc-admin_ce-export.css', __FILE__ ) );
			wp_enqueue_script( 'wpsc_ce_scripts', plugins_url( '/templates/admin/wpsc-admin_ce-export.js', __FILE__ ), array( 'jquery' ) );
		}

	}
	add_action( 'admin_enqueue_scripts', 'wpsc_ce_enqueue_scripts' );

	// Add Store Export menu to alternative Store menu
	function wpsc_ce_store_admin_menu() {

		add_submenu_page( 'wpsc_sm', __( 'Store Export', 'wpsc_ce' ), __( 'Store Export', 'wpsc_ce' ), 'manage_options', 'wpsc_ce', 'wpsc_ce_html_page' );
		remove_filter( 'wpsc_additional_pages', 'wpsc_ce_add_modules_admin_pages', 10 );

	}
	add_action( 'wpsc_sm_store_admin_subpages', 'wpsc_ce_store_admin_menu' );

	// Initial scripts and export process
	function wpsc_ce_admin_init() {

		global $export, $wp_roles;

		include_once( 'includes/formatting.php' );

		$action = wpsc_get_action();
		switch( $action ) {

			case 'dismiss_memory_prompt':
				wpsc_ce_update_option( 'dismiss_memory_prompt', 1 );
				$url = add_query_arg( 'action', null );
				wp_redirect( $url );
				exit();
				break;

			case 'export':
				$export = new stdClass();
				$export->start_time = time();
				$export->idle_memory_start = wpsc_ce_current_memory_usage();
				$export->delimiter = $_POST['delimiter'];
				if( $export->delimiter <> wpsc_ce_get_option( 'delimiter' ) )
					wpsc_ce_update_option( 'delimiter', $export->delimiter );
				$export->category_separator = $_POST['category_separator'];
				if( $export->category_separator <> wpsc_ce_get_option( 'category_separator' ) )
					wpsc_ce_update_option( 'category_separator', $export->category_separator );
				$export->bom = $_POST['bom'];
				if( $export->bom <> wpsc_ce_get_option( 'bom' ) )
					wpsc_ce_update_option( 'bom', $export->bom );
				$export->escape_formatting = $_POST['escape_formatting'];
				if( $export->escape_formatting <> wpsc_ce_get_option( 'escape_formatting' ) )
					wpsc_ce_update_option( 'escape_formatting', $export->escape_formatting );
				$export->limit_volume = -1;
				if( !empty( $_POST['limit_volume'] ) ) {
					$export->limit_volume = $_POST['limit_volume'];
					if( $export->limit_volume <> wpsc_ce_get_option( 'limit_volume' ) )
						wpsc_ce_update_option( 'limit_volume', $export->limit_volume );
				}
				$export->offset = 0;
				if( !empty( $_POST['offset'] ) ) {
					$export->offset = (int)$_POST['offset'];
					if( $export->offset <> wpsc_ce_get_option( 'offset' ) )
						wpsc_ce_update_option( 'offset', $export->offset );
				}
				$export->delete_temporary_csv = 0;
				if( !empty( $_POST['delete_temporary_csv'] ) ) {
					$export->delete_temporary_csv = (int)$_POST['delete_temporary_csv'];
					if( $export->limit_volume <> wpsc_ce_get_option( 'delete_csv' ) )
						wpsc_ce_update_option( 'delete_csv', $export->delete_temporary_csv );
				}
				$export->encoding = 'UTF-8';
				if( !empty( $_POST['encoding'] ) ) {
					$export->encoding = (string)$_POST['encoding'];
					if( $export->encoding <> wpsc_ce_get_option( 'encoding' ) )
						wpsc_ce_update_option( 'encoding', $export->encoding );
				}
				if( !empty( $_POST['date_format'] ) ) {
					$export->date_format = (string)$_POST['date_format'];
					if( $export->date_format <> wpsc_ce_get_option( 'date_format' ) )
						wpsc_ce_update_option( 'date_format', $export->date_format );
				}
				$export->fields = false;
				$export->product_categories = false;
				$export->product_tags = false;
				$export->product_status = false;
				$export->order_dates_filter = false;
				$export->order_dates_from = '';
				$export->order_dates_to = '';
				$export->order_status = false;
				$export->order_customer = false;
				$export->order_user_roles = false;
				$export->order_orderby = false;
				$export->order_order = false;

				$dataset = array();
				$export->type = $_POST['dataset'];
				switch( $export->type ) {

					case 'products':
						$dataset[] = 'products';
						$export->fields = ( isset( $_POST['product_fields'] ) ) ? $_POST['product_fields'] : false;
						$export->product_categories = ( isset( $_POST['product_filter_categories'] ) ) ? wpsc_ce_format_product_filters( $_POST['product_filter_categories'] ) : false;
						$export->product_tags = ( isset( $_POST['product_filter_tags'] ) ) ? wpsc_ce_format_product_filters( $_POST['product_filter_tags'] ) : false;
						$export->product_status = ( isset( $_POST['product_filter_status'] ) ) ? wpsc_ce_format_product_filters( $_POST['product_filter_status'] ) : false;
						$export->product_orderby = ( isset( $_POST['product_orderby'] ) ) ? $_POST['product_orderby'] : false;
						if( $export->product_orderby <> wpsc_ce_get_option( 'product_orderby' ) )
							wpsc_ce_update_option( 'product_orderby', $export->product_orderby );
						$export->product_order = ( isset( $_POST['product_order'] ) ) ? $_POST['product_order'] : false;
						if( $export->product_order <> wpsc_ce_get_option( 'product_order' ) )
							wpsc_ce_update_option( 'product_order', $export->product_order );
						break;

					case 'categories':
						$dataset[] = 'categories';
						$export->fields = ( isset( $_POST['category_fields'] ) ) ? $_POST['category_fields'] : false;
						$export->category_orderby = ( isset( $_POST['category_orderby'] ) ) ? $_POST['category_orderby'] : false;
						if( $export->category_orderby <> wpsc_ce_get_option( 'category_orderby' ) )
							wpsc_ce_update_option( 'category_orderby', $export->category_orderby );
						$export->category_order = ( isset( $_POST['category_order'] ) ) ? $_POST['category_order'] : false;
						if( $export->category_order <> wpsc_ce_get_option( 'category_order' ) )
							wpsc_ce_update_option( 'category_order', $export->category_order );
						break;

					case 'tags':
						$dataset[] = 'tags';
						$export->fields = ( isset( $_POST['tag_fields'] ) ) ? $_POST['tag_fields'] : false;
						$export->tag_orderby = ( isset( $_POST['tag_orderby'] ) ) ? $_POST['tag_orderby'] : false;
						if( $export->tag_orderby <> wpsc_ce_get_option( 'tag_orderby' ) )
							wpsc_ce_update_option( 'tag_orderby', $export->tag_orderby );
						$export->tag_order = ( isset( $_POST['tag_order'] ) ) ? $_POST['tag_order'] : false;
						if( $export->tag_order <> wpsc_ce_get_option( 'tag_order' ) )
							wpsc_ce_update_option( 'tag_order', $export->tag_order );
						break;

					case 'orders':
						$dataset[] = 'orders';
						$export->fields = ( isset( $_POST['order_fields'] ) ) ? $_POST['order_fields'] : false;
						$export->order_dates_filter = ( isset( $_POST['order_dates_filter'] ) ) ? $_POST['order_dates_filter'] : false;
						$export->order_dates_from = $_POST['order_dates_from'];
						$export->order_dates_to = $_POST['order_dates_to'];
						$export->order_status = ( isset( $_POST['order_filter_status'] ) ) ? wpsc_ce_format_product_filters( $_POST['order_filter_status'] ) : false;
						$export->order_customer = ( isset( $_POST['order_customer'] ) ) ? $_POST['order_customer'] : false;
						$export->order_user_roles = ( isset( $_POST['order_filter_user_role'] ) ) ? wpsc_ce_format_user_role_filters( $_POST['order_filter_user_role'] ) : false;
						$export->order_orderby = ( isset( $_POST['order_orderby'] ) ) ? $_POST['order_orderby'] : false;
						if( $export->order_orderby <> wpsc_ce_get_option( 'order_orderby' ) )
							wpsc_ce_update_option( 'order_orderby', $export->order_orderby );
						$export->order_order = ( isset( $_POST['order_order'] ) ) ? $_POST['order_order'] : false;
						if( $export->order_order <> wpsc_ce_get_option( 'order_order' ) )
							wpsc_ce_update_option( 'order_order', $export->order_order );
						break;

					case 'customers':
						$dataset[] = 'customers';
						$export->fields = $_POST['customer_fields'];
						break;

					case 'coupons':
						$dataset[] = 'coupons';
						$export->fields = $_POST['coupon_fields'];
						break;

				}
				if( $dataset ) {

					$timeout = 600;
					if( isset( $_POST['timeout'] ) ) {
						$timeout = (int)$_POST['timeout'];
						if( $timeout <> wpsc_ce_get_option( 'timeout' ) )
							wpsc_ce_update_option( 'timeout', $timeout );
					}

					if( !ini_get( 'safe_mode' ) )
						@set_time_limit( $timeout );

					@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );
					@ini_set( 'max_execution_time', (int)$timeout );

					$args = array(
						'limit_volume' => $export->limit_volume,
						'offset' => $export->offset,
						'encoding' => $export->encoding,
						'date_format' => $export->date_format,
						'product_categories' => $export->product_categories,
						'product_tags' => $export->product_tags,
						'product_status' => $export->product_status,
						'product_orderby' => $export->product_orderby,
						'product_order' => $export->product_order,
						'category_orderby' => $export->category_orderby,
						'category_order' => $export->category_order,
						'tag_orderby' => $export->tag_orderby,
						'tag_order' => $export->tag_order,
						'order_status' => $export->order_status,
						'order_dates_filter' => $export->order_dates_filter,
						'order_dates_from' => wpsc_ce_format_order_date( $export->order_dates_from ),
						'order_dates_to' => wpsc_ce_format_order_date( $export->order_dates_to ),
						'order_customer' => $export->order_customer,
						'order_user_roles' => $export->order_user_roles,
						'order_orderby' => $export->order_orderby,
						'order_order' => $export->order_order
					);
					wpsc_ce_save_fields( $dataset, $export->fields );
					$export->filename = wpsc_ce_generate_csv_filename( $export->type );
					if( WPSC_CE_DEBUG ) {

						wpsc_ce_export_dataset( $dataset, $args );
						$export->idle_memory_end = wpsc_ce_current_memory_usage();
						$export->end_time = time();

					} else {

						// Generate CSV contents
						$bits = wpsc_ce_export_dataset( $dataset, $args );
						unset( $export->fields );
						if( !$bits ) {
							wp_redirect( add_query_arg( 'empty', true ) );
							exit();
						}
						if( isset( $export->delete_temporary_csv ) && $export->delete_temporary_csv ) {

							// Print to browser
							wpsc_ce_generate_csv_header( $export->type );
							echo $bits;
							exit();

						} else {

							// Save to file and insert to WordPress Media
							if( $export->filename && $bits ) {
								$post_ID = wpsc_ce_save_csv_file_attachment( $export->filename );
								$upload = wp_upload_bits( $export->filename, null, $bits );
								if( $upload['error'] ) {
									wp_delete_attachment( $post_ID, true );
									wp_redirect( add_query_arg( array( 'failed' => true, 'message' => urlencode( $upload['error'] ) ) ) );
									return;
								}
								$attach_data = wp_generate_attachment_metadata( $post_ID, $upload['file'] );
								wp_update_attachment_metadata( $post_ID, $attach_data );
								if( $post_ID ) {
									wpsc_ce_save_csv_file_guid( $post_ID, $export->type, $upload['url'] );
									wpsc_ce_save_csv_file_details( $post_ID );
								}
								$export_type = $export->type;
								unset( $export );

								// The end memory usage and time is collected at the very last opportunity prior to the CSV header being rendered to the screen
								wpsc_ce_update_csv_file_detail( $post_ID, '_wpsc_idle_memory_end', wpsc_ce_current_memory_usage() );
								wpsc_ce_update_csv_file_detail( $post_ID, '_wpsc_end_time', time() );

								// Generate CSV header
								wpsc_ce_generate_csv_header( $export_type );
								unset( $export_type );

								// Print file contents to screen
								if( $upload['file'] ) {
									readfile( $upload['file'] );
								} else {
									wp_redirect( add_query_arg( 'failed', true ) );
								}
								unset( $upload );
							} else {
								wp_redirect( add_query_arg( 'failed', true ) );
							}
							exit();

						}
					}
				}
				break;

			default:
				// Detect other platform versions
				wpsc_ce_detect_non_wpsc_install();
				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_date' );
				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_status' );
				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_customer' );
				break;

		}

	}
	add_action( 'admin_init', 'wpsc_ce_admin_init' );

	// HTML templates and form processor for Store Exporter screen
	function wpsc_ce_html_page() {

		global $wpdb, $export;

		$title = apply_filters( 'wpsc_ce_template_header', '' );
		wpsc_ce_template_header( $title );
		wpsc_ce_support_donate();
		$action = wpsc_get_action();
		switch( $action ) {

			case 'export':
				$message = __( 'Chosen WP e-Commerce details have been exported from your store.', 'wpsc_ce' );
				wpsc_ce_admin_notice( $message );
				$output = '';
				if( WPSC_CE_DEBUG ) {
					if( false === ( $export_log = get_transient( WPSC_CE_PREFIX . '_debug_log' ) ) ) {
						$export_log = __( 'No export entries were found, please try again with different export filters.', 'wpsc_ce' );
					} else {
						delete_transient( WPSC_CE_PREFIX . '_debug_log' );
						$export_log = base64_decode( $export_log );
					}
					$output .= '<h3>' . __( 'Export Details' ) . '</h3>';
					$output .= '<textarea id="export_log">' . print_r( $export, true ) . '</textarea><hr />';
					$output .= '<h3>' . sprintf( __( 'Export Log: %s', 'wpsc_ce' ), $export->filename ) . '</h3>';
					$output .= '<textarea id="export_log">' . $export_log . '</textarea>';
				}
				echo $output;

				wpsc_ce_manage_form();
				break;

			default:
				wpsc_ce_manage_form();
				break;

		}
		wpsc_ce_template_footer();

	}

	// HTML template for Export screen
	function wpsc_ce_manage_form() {

		$tab = false;
		if( isset( $_GET['tab'] ) )
			$tab = $_GET['tab'];
		$url = add_query_arg( 'page', 'wpsc_ce' );
		wpsc_ce_fail_notices();
		switch( wpsc_get_major_version() ) {

			case '3.8':
				$wpsc_ce_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_ce' ), 'edit.php' );
				break;

			case '3.7':
				$wpsc_ce_url = add_query_arg( 'page', 'wpsc_ce', 'admin.php' );
				break;

		}
		include_once( WPSC_CE_PATH . 'templates/admin/wpsc-admin_ce-export.php' );

	}

	/* End of: WordPress Administration */

}
?>