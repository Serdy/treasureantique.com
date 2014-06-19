<?php

/**
 * Helper for managing SEO issues.
 *
 * @author     Timely Network Inc
 * @since      2.0
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.Helper
 */
class Ai1ec_Seo_Helper
{

	/**
	 * @var Ai1ec_Seo_Helper Singletonian instance of self
	 */
	static protected $_instance = NULL;

	/**
	 * Returns singletonian instance of this class.
	 *
	 * @return Ai1ec_Seo_Helper Singletonian instance of self
	 */
	static public function get_instance() {
		if ( ! ( self::$_instance instanceof Ai1ec_Seo_Helper ) ) {
			self::$_instance = new Ai1ec_Seo_Helper();
		}
		return self::$_instance;
	}

	/**
	 * Returns true if "All-in-One SEO Pack" plugin is installed, false otherwise.
	 *
	 * @return bool Installation status
	 */
	public function is_aiosep_installed() {
		global $aiosp;
		$class = 'All_in_One_SEO_Pack';
		return isset( $aiosp ) &&
		       class_exists( $class ) &&
		       ( $aiosp instanceof $class );
	}

	/**
	 * Returns true if "Greg's High Performance SEO" plugin is installed, false
	 * otherwise.
	 *
	 * @return bool Installation status
	 */
	public function is_ghpseo_installed() {
		return class_exists( 'gregsHighPerformanceSEO' );
	}

	/**
	 * Returns true if "WordPress SEO by Yoast" plugin is installed, false
	 * otherwise.
	 *
	 * @return bool Installation status
	 */
	public function is_wpseo_installed() {
		return defined( 'WPSEO_VERSION' );
	}

	/**
	 * Returns true if any SEO plugin is installed that obviates the need for this
	 * helper class, false otherwise.
	 *
	 * @return bool Installation status - true if any is installed
	 */
	public function is_seo_plugin_installed() {
		return $this->is_aiosep_installed() ||
		       $this->is_ghpseo_installed() ||
		       $this->is_wpseo_installed();
	}

	/**
	 * Returns true if WordPress has an internal method registered that outputs
	 * the canonical link tag in the HTML head section.
	 *
	 * @return bool True if WordPress has internal approach on canonical links
	 */
	public function is_wp_with_canonical() {
		return function_exists( 'rel_canonical' ) &&
		       false !== has_action( 'wp_head', 'rel_canonical' );
	}

	/**
	 * Returns canonical link for current calendar page if user is currently on
	 * the calendar page, or if not, an empty string.
	 *
	 * @return string Canonical URL for current calendar page or an empty string
	 */
	public function get_canonical_link() {
		global $ai1ec_app_controller;
		$calendar_page = $ai1ec_app_controller->is_calendar_page();
		if ( false === $calendar_page ) {
			return '';
		}
		return Ai1ec_Html_Utility::canonical_link(
			get_page_link( $calendar_page )
		);
	}

	/**
	 * Renders the calendar's canonical link, if required.
	 *
	 * Method is intended to be called from `wp_head` action to output canonical
	 * link, if any, in the page's head section. First checks prerequisites to
	 * ensure that no other plugin has intentions to output canonical link.
	 *
	 * @return void Method does not return
	 */
	public function render_canonical_link() {
		if (
			! $this->is_wp_with_canonical() &&
			! $this->is_seo_plugin_installed()
		) {
			echo $this->get_canonical_link();
		} // Else is not required, as SEO plugins use the same approach.
	}

	/**
	 * Constructs an SEO helper.
	 *
	 * Checks prerequisites (that all plugins are loaded) and registers an
	 * action to be fired on "wp_head" and output canonical link, if required.
	 *
	 * @return void Constructor does not return
	 */
	protected function __construct() {
		if ( did_action( 'plugins_loaded' ) < 1 ) {
			throw new Ai1ec_Flow_Exception(
				'SEO helper may only be initialized after plugins are loaded.'
			);
		}
		add_action(
			'wp_head',
			array( $this, 'render_canonical_link' )
		);
	}

}
