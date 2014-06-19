<?php

/**
 * @author Timely Network Inc
 */

class Ai1ec_View_Factory {

	/**
	 * @var boolean
	 */
	private static $pretty_permalinks_enabled = false;

	/**
	 * @var string
	 */
	private static $page;

	/**
	 * @param boolean $pretty_permalinks_enabled
	 */
	public static function set_pretty_permalinks_enabled(
		$pretty_permalinks_enabled
		) {
		Ai1ec_View_Factory::$pretty_permalinks_enabled = $pretty_permalinks_enabled;
	}

	/**
	 * @param string $page
	 */
	public static function set_page( $page ) {
		Ai1ec_View_Factory::$page = $page;
	}

	/**
	 * @param array $args
	 * @param string $type
	 * @return Ai1ec_Href_Helper
	 */
	static public function create_href_helper_instance( array $args, $type = 'normal' ) {
		$href = new Ai1ec_Href_Helper( $args, self::$page );
		$href->set_pretty_permalinks_enabled( self::$pretty_permalinks_enabled );
		switch ( $type ) {
			case 'category':
				$href->set_is_category( true );
				break;
			case 'tag':
				$href->set_is_tag( true );
				break;
			default:
				break;
		}

		return $href;
	}

	/**
	 * Create the html element used as the UI control for the datepicker button.
	 * The href must keep only active filters.
	 *
	 * @param array           $args         Populated args for the view
	 * @param int|string|null $initial_date The datepicker's initially set date
	 * @return Ai1ec_Generic_Html_Tag
	 */
	static public function create_datepicker_link(
		array $args, $initial_date = null
	) {
		global $ai1ec_settings,
		       $ai1ec_view_helper;

		$link = Ai1ec_Helper_Factory::create_generic_html_tag( 'a' );

		$date_format_pattern = Ai1ec_Time_Utility::get_date_pattern_by_key(
			$ai1ec_settings->input_date_format
		);

		if ( $initial_date == null ) {
			// If exact_date argument was provided, use its value to initialize
			// datepicker.
			if ( isset( $args['exact_date'] ) &&
			     $args['exact_date'] !== false &&
			     $args['exact_date'] !== null ) {
				$initial_date = $args['exact_date'];
			}
			// Else default to today's date.
			else {
				$initial_date = Ai1ec_Time_Utility::gmt_to_local(
					Ai1ec_Time_Utility::current_time()
				);
			}
		}
		// Convert initial date to formatted date if required.
		if ( Ai1ec_Validation_Utility::is_valid_time_stamp( $initial_date ) ) {
			$initial_date = Ai1ec_Time_Utility::format_date(
				Ai1ec_Time_Utility::gmt_to_local( $initial_date ),
				$ai1ec_settings->input_date_format
			);
		}

		$link->add_class( 'ai1ec-minical-trigger btn' );
		$link->set_attribute( 'data-date', $initial_date );
		$link->set_attribute( 'data-date-format', $date_format_pattern );
		$link->set_attribute( 'data-date-weekstart',
			$ai1ec_settings->week_start_day );
		$link->set_attribute_expr( $args['data_type'] );

		$text = '<img src="' .
			esc_attr( $ai1ec_view_helper->get_theme_img_url( 'date-icon.png' ) ) .
			'" class="ai1ec-icon-datepicker" />';
		$link->set_text( $text );

		$href_args = array(
			'action' => $args['action'],
			'cat_ids' => $args['cat_ids'],
			'tag_ids' => $args['tag_ids'],
			'exact_date' => "__DATE__",
		);
		$data_href = self::create_href_helper_instance( $href_args );
		$link->set_attribute( 'data-href', $data_href->generate_href() );

		$link->set_attribute( 'href', '#' );
		return $link;
	}


	/**
	 * Creates a category selector using the Select2 widget.
	 *
	 * @return Ai1ec_Select
	 */
	static public function create_select2_categories() {
		global $ai1ec_events_helper;

		$select2 = Ai1ec_Helper_Factory::create_select_instance(
			'ai1ec_feed_category', 'ai1ec_feed_category[]' );
		foreach (
			get_terms( 'events_categories', array( 'hide_empty' => false ) ) as $term
		) {
			$args = array();
			$color = $ai1ec_events_helper->get_category_color( $term->term_id );
			if ( $color ) {
				$args["data-color"] = $color;
			}
			$select2->add_option( $term->name, $term->term_id, $args );
		}
		$select2->set_attribute( "multiple", "multiple" );
		$select2->set_attribute( "data-placeholder",
			__( 'Categories (optional)', AI1EC_PLUGIN_NAME )
		);
		$select2->add_class( 'ai1ec-categories-selector span12' );
		return $select2;
	}

	/**
	 * Creates a tag selector using the Select2 widget.
	 *
	 * @return Ai1ec_Input
	 */
	static public function create_select2_tags() {
		// Get tags.
		$tags = get_terms(
			'events_tags',
			array(
				'orderby' => 'name',
				'hide_empty' => 0,
			)
		);

		// Build tags array to pass as JSON.
		$tags_json = array();
		foreach ( $tags as $term ) {
			$tags_json[] = $term->name;
		}
		$tags_json = json_encode( $tags_json );
		$tags_json = _wp_specialchars( $tags_json, 'single', 'UTF-8' );
		$input = Ai1ec_Helper_Factory::create_input_instance();
		$input->set_id( 'ai1ec_feed_tags' );
		$input->set_name( 'ai1ec_feed_tags' );
		$input->set_attribute( "data-placeholder",
			__( 'Tags (optional)', AI1EC_PLUGIN_NAME )
		);
		$input->set_attribute( "data-ai1ec-tags", $tags_json );
		$input->add_class( 'ai1ec-tags-selector span12' );
		return $input;
	}
}
