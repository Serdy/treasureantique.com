<?php

/**
 * Controller responsible for organizing log entries upload to Time.ly WS
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.12.07
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Logging.Controller
 */
class Ai1ec_Logging_Controller
{

	/**
	 * @constant string Cron version identifier
	 */
	const CRON_VERSION = 'a1';

	/**
	 * @var Ai1ec_Logging_Controller Singletonian instance of self
	 */
	static protected $_instance = NULL;

	/**
	 * @var Ai1ec_Log_Appender_Wpdb Instance of appender writing entries
	 */
	protected $_appender = NULL;

	/**
	 * get_instance method
	 *
	 * Singleton entry point to get single instance across system
	 *
	 * @return Ai1ec_Logging_Controller Singletonian instance
	 */
	static public function get_instance() {
		if ( ! ( self::$_instance instanceof Ai1ec_Logging_Controller ) ) {
			self::$_instance = new Ai1ec_Logging_Controller();
		}
		return self::$_instance;
	}

	/**
	 * cron_run method
	 *
	 * Action, which is executed, when cron is triggered
	 *
	 * @return void Method does not return
	 */
	public function cron_run() {
		$log_entries = $this->get_entries();
		if ( empty( $log_entries ) ) {
			return false;
		}
		$batch = $this->prepare_upload( $log_entries );
		if ( ! $this->submit( $batch ) ) {
			return false;
		}
		return $this->clean_entries( $log_entries );
	}

	/**
	 * clean_entries method
	 *
	 * Clean entries, that were submitted to Time.ly API
	 *
	 * @param array $entries List of entries returned by appender
	 *
	 * @return bool Success
	 */
	public function clean_entries( array $entries ) {
		return $this->_appender->prune( $entries );
	}

	/**
	 * get_plugins method
	 *
	 * Get a list of plugins installed (with detailed information) and the list
	 * of activated plugins.
	 *
	 * @return array Map with two elements: 'installed' and 'activated'
	 */
	public function get_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugins = array(
			'installed' => get_plugins(),
			'activated' => Ai1ec_Meta::get_option( 'active_plugins', array() ),
		);
		return $plugins;
	}

	/**
	 * get_themes method
	 *
	 * Get a list of themes installed (with detailed information) and the name
	 * of currently active theme.
	 *
	 * @return array Map with two elements: 'installed' and 'activated'
	 */
	public function get_themes() {
		$themes = array(
			'installed' => array(),
			'activated' => Ai1ec_Meta::get_option(
				'stylesheet',
				'~ERROR_DETECTING_ACTIVE_WP_THEME~'
			),
		);
		$theme_list = wp_get_themes();
		foreach ( $theme_list as $theme ) {
			$themes['installed'][$theme->get_stylesheet()] = $this
				->_theme_to_array( $theme );
		}
		unset( $theme_list );
		return $themes;
	}

	/**
	 * get_ai1ec_themes method
	 *
	 * Get a list of AI1EC themes installed (with detailed information) and the
	 * name of currently active theme, as well as detailed sheets information.
	 *
	 * @return array Map with three elements: 'installed', 'activated'
	 *               and 'outdated', later indicating outdatedness of themes
	 */
	public function get_ai1ec_themes() {
		global $ai1ec_themes_controller;
		$themes = array(
			'installed' => array(),
			'activated' => array(
				'template'      => Ai1ec_Meta::get_option( 'ai1ec_template' ),
				'stylesheet'    => Ai1ec_Meta::get_option( 'ai1ec_stylesheet' ),
				'current_theme' => Ai1ec_Meta::get_option(
					'ai1ec_current_theme'
				),
			),
			'outdated'  => (int)$ai1ec_themes_controller->are_themes_outdated(),
		);
		$ai1ec_theme_list = new Ai1ec_Themes_List_Table(
			array( 'inhibit' => true )
		);
		$installed        = $ai1ec_theme_list->get_themes();
		foreach ( $installed as $theme ) {
			$themes['installed'][$theme->get_stylesheet()] = $this
				->_theme_to_array( $theme );
		}
		unset( $ai1ec_theme_list, $installed );
		return $themes;
	}

	/**
	 * get_entries method
	 *
	 * Get list of log entries ready for upload.
	 *
	 * @return array List of log entries ready for upload to Time.ly
	 */
	public function get_entries() {
		return $this->_appender->get_commited();
	}

	/**
	 * get_mysql_info method
	 *
	 * Retrieve MySQL information map
	 *
	 * @return array Map of MySQL information options
	 */
	public function get_mysql_info() {
		global $wpdb;
		$status = array(
			'client_version' => mysql_get_client_info(),
			'server_version' => $wpdb->db_version(),
		);
		return $status;
	}

	/**
	 * get_php_info method
	 *
	 * Retrieve PHP information map
	 *
	 * @return array Map of PHP version and configuration options
	 */
	public function get_php_info() {
		$status = array(
			'version'     => phpversion(),
			'extensions'  => '~AI1EC_ERROR_DISABLED~',
			'ini_options' => array(),
			'int_size'    => PHP_INT_SIZE,
		);
		$ini_options = array(
			'memory_limit',
			'apc.shm_size',
			'apc.optimization',
			'apc.slam_defense',
			'apc.file_update_protection',
			'apc.include_once_override',
			'apc.ttl',
			'apc.user_ttl',
			'apc.gc_ttl',
			'apc.stat',
			'apc.rfc1867',
			'disable_functions',
			'max_execution_time',
			'max_input_time',
			'post_max_size',
			'upload_max_filesize',
			'request_order',
			'variables_order',
		);
		foreach ( $ini_options as $option ) {
			$value = ini_get( $option );
			$status['ini_options'][$option] = ( false === $value )
				? '~AI1EC_UNDEFINED~'
				: $value;
		}
		if ( is_callable( 'get_loaded_extensions' ) ) {
			$extensions = get_loaded_extensions();
			$status['extensions'] = array();
			foreach ( $extensions as $extension ) {
				$status['extensions'][$extension] = phpversion( $extension );
			}
		}
		return $status;
	}

	/**
	 * prepare_upload method
	 *
	 * Prepare log entries for upload - inject extra data, that is required for
	 * full analysis of possible consequences.
	 *
	 * @param array $entries List of log entries to upload
	 *
	 * @return array Log entries wrapped in a list of other information
	 */
	public function prepare_upload( array $entries ) {
		$batch = array(
			'site_url'         => site_url(),
			'network_site_url' => network_site_url(),
			'server_addr'      => $_SERVER['SERVER_ADDR'],
			'server_protocol'  => $_SERVER['SERVER_PROTOCOL'],
			'admin_email'      => get_bloginfo( 'admin_email' ),
			'sapi_name'        => php_sapi_name(),
			'default_timezone' => date_default_timezone_get(),
			'php'              => $this->get_php_info(),
			'mysql'            => $this->get_mysql_info(),
			'operating_system' => php_uname(),
			'ai1ec_version'    => AI1EC_VERSION,
			'ai1ec_debug'      => (int)(bool)AI1EC_DEBUG,
			'wp_debug'         => (int)(bool)WP_DEBUG,
			'wp_version'       => get_bloginfo( 'version' ),
			'wp_charset'       => get_bloginfo( 'charset' ),
			'wp_plugins'       => $this->get_plugins(),
			'wp_themes'        => $this->get_themes(),
			'ai1ec_themes'     => $this->get_ai1ec_themes(),
			'logs'             => $entries,
		);
		return $batch;
	}

	/**
	 * submit method
	 *
	 * Submit packed options to Time.ly
	 *
	 * @param array $post_body Post body options to submit
	 *
	 * @return bool Success
	 */
	public function submit( array $post_body ) {
		$post_body = json_encode( $post_body );
		$request   = array(
			'timeout'  => '1.005',
			'blocking' => false,
			'method'   => 'POST',
			'body'     => $post_body,
			'headers'  => array(
				'Content-Length' => strlen( $post_body ),
			),
		);
		$wp_http   = new WP_Http();
		$result    = $wp_http->request( AI1EC_LOG_UPLOAD, $request );
		unset( $post_body, $wp_http );
		if ( is_wp_error( $result ) ) {
			trigger_error( 'Failed to submit error report', E_USER_WARNING );
			return false;
		}
		return true;
	}

	/**
	 * install_schedule method
	 *
	 * Update/install, if necessary CRON.
	 * Return name of CRON action (hook) executed.
	 *
	 * @return string Name of CRON action
	 */
	public function install_schedule() {
		$cron_key = 'ai1ec_logging_cron';
		$optn_key = $cron_key . '_version';
		if (
			Ai1ec_Meta::get_option( $optn_key ) != self::CRON_VERSION
		) {
			wp_clear_scheduled_hook( $cron_key );
			wp_schedule_event(
				Ai1ec_Time_Utility::current_time(),
				'daily',
				$cron_key
			);
			update_option( $optn_key, self::CRON_VERSION );
		}
		return $cron_key;
	}

	/**
	 * Constructor
	 *
	 * Bind actions and install cron, if necessary
	 *
	 * @return void Constructor does not return
	 */
	protected function __construct() {
		$this->_appender = new Ai1ec_Log_Appender_Wpdb();
		$this->_appender->activateOptions();
		add_action( $this->install_schedule(), array( $this, 'cron_run' ) );
	}

	/**
	 * _theme_to_array method
	 *
	 * Convert `WP_Theme` object to map of useful properties.
	 *
	 * @param WP_Theme $theme Theme to convert to array
	 *
	 * @return array Map of theme information keys
	 */
	protected function _theme_to_array( WP_Theme $theme ) {
		static $keys = array(
			'Name',
			'ThemeURI',
			'Description',
			'Author',
			'AuthorURI',
			'Version',
			'Template',
			'Status',
			'TextDomain',
			'DomainPath',
		);
		$remapped = array();
		foreach ( $keys as $key ) { 
			$remapped[$key] = $theme->get( $key );
		}
		return $remapped;
	}

}
