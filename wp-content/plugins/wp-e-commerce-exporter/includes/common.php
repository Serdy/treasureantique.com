<?php
/**
*
* Filename: common.php
* Description: common.php loads commonly accessed functions across the Visser Labs suite.
*
* - wpsc_is_admin_icon_valid
* - wpsc_get_action
* - wpsc_get_major_version
* - wpsc_is_wpsc_activated
* - wpsc_is_woo_activated
* - wpsc_is_jigo_activated
*
*/

if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'wpsc_is_admin_icon_valid' ) ) {
		function wpsc_is_admin_icon_valid( $icon = 'tools' ) {

			switch( $icon ) {

				case 'index':
				case 'edit':
				case 'post':
				case 'link':
				case 'comments':
				case 'page':
				case 'users':
				case 'upload':
				case 'tools':
				case 'plugins':
				case 'themes':
				case 'profile':
				case 'admin':
					return $icon;
					break;

			}

		}
	}

	include_once( 'common-dashboard_widgets.php' );

	/* End of: WordPress Administration */

}

if( !function_exists( 'wpsc_get_action' ) ) {
	function wpsc_get_action( $switch = false ) {

		if( $switch ) {

			if( isset( $_GET['action'] ) )
				$action = $_GET['action'];
			else if( !isset( $action ) && isset( $_POST['action'] ) )
				$action = $_POST['action'];
			else
				$action = false;

		} else {

			if( isset( $_POST['action'] ) )
				$action = $_POST['action'];
			else if( !isset( $action ) && isset( $_GET['action'] ) )
				$action = $_GET['action'];
			else
				$action = false;

		}
		return $action;

	}
}

if( !function_exists( 'wpsc_get_major_version' ) ) {
	function wpsc_get_major_version() {

		$output = '';
		if( defined( 'WPSC_VERSION' ) )
			$version = WPSC_VERSION;
		else
			$version = get_option( 'wpsc_version' );
		if( $version )
			$output = substr( $version, 0, 3 );
		return $output;

	}
}

if( !function_exists( 'wpsc_is_wpsc_activated' ) ) {
	function wpsc_is_wpsc_activated() {

		if( class_exists( 'WP_eCommerce' ) )
			return true;

	}
}

if( !function_exists( 'wpsc_is_woo_activated' ) ) {
	function wpsc_is_woo_activated() {

		if( class_exists( 'Woocommerce' ) )
			return true;

	}
}

if( !function_exists( 'wpsc_is_jigo_activated' ) ) {
	function wpsc_is_jigo_activated() {

		if( function_exists( 'jigoshop_init' ) )
			return true;

	}
}
?>