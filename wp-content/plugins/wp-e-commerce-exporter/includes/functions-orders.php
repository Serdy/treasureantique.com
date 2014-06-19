<?php
// Returns a list of Order export columns
function wpsc_ce_get_order_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'purchase_id',
		'label' => __( 'Purchase ID', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'purchase_total',
		'label' => __( 'Purchase Total', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'payment_gateway',
		'label' => __( 'Payment Gateway', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'shipping_method',
		'label' => __( 'Shipping Method', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'payment_status',
		'label' => __( 'Payment Status', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'payment_status_int',
		'label' => __( 'Payment Status (number)', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'purchase_date',
		'label' => __( 'Purchase Date', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'purchase_time',
		'label' => __( 'Purchase Time', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'tracking_id',
		'label' => __( 'Tracking ID', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'transaction_id',
		'label' => __( 'Transaction ID', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'session_id',
		'label' => __( 'Session ID', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'order_personalisation',
		'label' => __( 'Order Personalisation', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'username',
		'label' => __( 'Username', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'notes',
		'label' => __( 'Notes', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'referral',
		'label' => __( 'Referral Source', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'order_items_product_id',
		'label' => __( 'Order Items: Product ID', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'order_items_sku',
		'label' => __( 'Order Items: SKU', 'wpsc_ce' ),
		'default' => 0
	);
	$fields[] = array(
		'name' => 'order_items_product_name',
		'label' => __( 'Order Items: Product Name', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'order_items_product_quantity',
		'label' => __( 'Order Items: Product Quantity', 'wpsc_ce' ),
		'default' => 1
	);
	$fields[] = array(
		'name' => 'order_items_product_personalisation',
		'label' => __( 'Order Items: Product Personalisation', 'wpsc_ce' ),
		'default' => 0
	);
	$fields = array_merge_recursive( $fields, wpsc_ce_get_checkout_fields() );
/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wpsc_ce' ),
		'default' => 1
	);
*/

	// Allow Plugin/Theme authors to add support for additional Order columns
	$fields = apply_filters( 'wpsc_ce_order_fields', $fields );

	$remember = wpsc_ce_get_option( 'orders_fields' );
	if( $remember ) {
		$remember = maybe_unserialize( $remember );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i] ) {
				if( !array_key_exists( $fields[$i]['name'], $remember ) )
					$fields[$i]['default'] = 0;
			}
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
function wpsc_ce_get_order_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_order_fields();
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
// HTML template for disabled Filter Orders by Date widget on Store Exporter screen
function wpsc_ce_orders_filter_by_date() {

	$current_month = date( 'F' );
	$last_month = date( 'F', mktime( 0, 0, 0, date( 'n' )-1, 1, date( 'Y' ) ) );
	$order_dates_from = '-';
	$order_dates_to = '-';

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-date" /> <?php _e( 'Filter Orders by Order Date', 'wpsc_ce' ); ?></label></p>
<div id="export-orders-filters-date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_dates_filter" value="current_month" disabled="disabled" /> <?php _e( 'Current month', 'wpsc_ce' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_month" disabled="disabled" /> <?php _e( 'Last month', 'wpsc_ce' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="manual" disabled="disabled" /> <?php _e( 'Manual', 'wpsc_ce' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="order_dates_from" name="order_dates_from" value="<?php echo $order_dates_from; ?>" class="text" disabled="disabled" /> to <input type="text" size="10" maxlength="10" id="order_dates_to" name="order_dates_to" value="<?php echo $order_dates_to; ?>" class="text" disabled="disabled" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first order to today.', 'wpsc_ce' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-date -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by Customer widget on Store Exporter screen
function wpsc_ce_orders_filter_by_customer() {

	ob_start(); ?>
<p><label for="order_customer"><?php _e( 'Filter Orders by Customer', 'wpsc_ce' ); ?></label></p>
<div id="export-orders-filters-date" class="separator">
	<select id="order_customer" name="order_customer" disabled="disabled">
		<option value=""><?php _e( 'Show all customers', 'wpsc_ce' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Filter Orders by Customer (unique e-mail address) to be included in the export. Default is to include all Orders.', 'wpsc_ce' ); ?></p>
</div>
<!-- #export-orders-filters-date -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by Order Status widget on Store Exporter screen
function wpsc_ce_orders_filter_by_status() {

	global $wpsc_purchlog_statuses;

	$order_statuses = $wpsc_purchlog_statuses;
	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-status" /> <?php _e( 'Filter Orders by Order Status', 'wpsc_ce' ); ?></label></p>
<div id="export-orders-filters-status" class="separator">
	<ul>
<?php foreach( $order_statuses as $order_status ) { ?>
		<li><label><input type="checkbox" name="order_filter_status[<?php echo $order_status['order']; ?>]" value="<?php echo $order_status['order']; ?>" /> <?php echo $order_status['label']; ?></label></li>
<?php } ?>
	</ul>
	<p class="description"><?php _e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'wpsc_ce' ); ?></p>
</div>
<!-- #export-orders-filters-status -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by User Role widget on Store Exporter screen
function wpsc_ce_orders_filter_by_user_role() {

	$user_roles = wpsc_ce_get_user_roles();
	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-user_role" /> <?php _e( 'Filter Orders by User Role', 'wpsc_ce' ); ?></label></p>
<div id="export-orders-filters-user_role" class="separator">
	<ul>
<?php foreach( $user_roles as $key => $user_role ) { ?>
		<li><label><input type="checkbox" name="order_filter_user_role[<?php echo $key; ?>]" value="<?php echo $key; ?>" disabled="disabled" /> <?php echo ucfirst( $user_role['name'] ); ?></label></li>
<?php } ?>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Orders by. Default is to include all User Role options.', 'wpsc_ce' ); ?></p>
</div>
<!-- #export-orders-filters-status -->
<?php
	ob_end_flush();

}

// HTML template for disabled Order Sorting widget on Store Exporter screen
function wpsc_ce_orders_order_sorting() {

	ob_start(); ?>
<p><label><?php _e( 'Order Sorting', 'wpsc_ce' ); ?></label></p>
<div>
	<select name="order_orderby" disabled="disabled">
		<option value="ID"><?php _e( 'Order ID', 'wpsc_ce' ); ?></option>
		<option value="date"><?php _e( 'Date Created', 'wpsc_ce' ); ?></option>
		<option value="payment_method"><?php _e( 'Payment Method', 'wpsc_ce' ); ?></option>
		<option value="shipping_method"><?php _e( 'Shipping Method', 'wpsc_ce' ); ?></option>
		<option value="rand"><?php _e( 'Random', 'wpsc_ce' ); ?></option>
	</select>
	<select name="order_order" disabled="disabled">
		<option value="ASC"><?php _e( 'Ascending', 'wpsc_ce' ); ?></option>
		<option value="DESC"><?php _e( 'Descending', 'wpsc_ce' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Orders within the exported file. By default this is set to export Orders by Order ID in Desending order.', 'wpsc_ce' ); ?></p>
</div>
<?php
	ob_end_flush();
	
}
?>