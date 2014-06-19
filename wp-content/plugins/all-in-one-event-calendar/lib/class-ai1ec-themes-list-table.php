<?php
//
//  class-ai1ec-themes-list-table.php
//  all-in-one-event-calendar
//
//  Created by The Seed Studio on 2012-04-05.
//

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Ai1ec_Themes_List_Table extends WP_List_Table {

	/**
	 * search class variable
	 *
	 * Holds search terms
	 *
	 * @var array
	 **/
	var $search = array();

	/**
	 * features class variable
	 *
	 * @var array
	 **/
	var $features = array();

	/**
	 * Constructor
	 *
	 * Overriding constructor to allow inhibiting parents startup sequence.
	 * If in some wild case you need to inhibit startup sequence of parent
	 * class - pass `array( 'inhibit' => true )` as argument to this one.
	 *
	 * @param array $args Options to pass to parent constructor
	 *
	 * @return void Constructor does not return
	 */
	public function __construct( $args = array() ) {
		if ( ! isset( $args['inhibit'] ) ) {
			parent::__construct( $args );
		}
	}

	/**
	 * get_themes method
	 *
	 * Wrapper to WP `wp_get_themes` or `get_themes` (whichever is available).
	 * Method resets global variables and hooks, required for indexing, whilst
	 * performing theme search. After themes are found - it caches these using
	 * local static variable and restores global functions.
	 *
	 * @param bool $force Set to true to re-list themes
	 *
	 * @return array Map of theme names and their paths
	 */
	public function get_themes( $force = false, $options = array() ) {
		static $theme_map = array();
		$key = json_encode( $options );
		if ( $force || ! isset( $theme_map[$key] ) ) {
			global $wp_theme_directories, $wp_broken_themes;
			$restore_vals = array(
				'wp_theme_directories' => array( AI1EC_THEMES_ROOT ),
				'wp_broken_themes'     => array(),
			);

			// mark restore point
			foreach ( $restore_vals as $key => $cval ) {
				$restore_vals[$key] = $$key;
				$$key               = $cval;
			}

			// disable and clean cache
			add_filter(
				'wp_cache_themes_persistently',
				'__return_false',
				1
			);
			search_theme_directories( true );
			$theme_list = NULL;
			if ( function_exists( 'wp_get_themes' ) ) {
				$theme_list = wp_get_themes( $options );
			} else {
				if ( isset( $options['errors'] ) && $options['errors'] ) {
					$theme_list = get_broken_themes();
				} else {
					$theme_list = get_themes();
				}
			}
			foreach ( $theme_list as $theme ) {
				$theme_map[$key][$theme->get( 'Name' )] = $theme;
				$theme->get_theme_root_uri(); // pre-cache
			}
			unset( $theme_list );

			// remove cache disablers and restore values
			remove_filter(
				'wp_cache_themes_persistently',
				'__return_false',
				1
			);
			foreach ( $restore_vals as $key => $cval ) {
				$$key = $cval;
			}
			search_theme_directories( true );
		}
		return $theme_map[$key];
	}

	/**
	 * get_broken_themes method
	 *
	 * Convenient wrapper to `wp_get_themes( [errors:=true] )`
	 * or `get_broken_themes`, to avoid WP version deprecation conflicts.
	 *
	 * @return array Map of broken themes
	 */
	public function get_broken_themes() {
		return $this->get_themes( false, array( 'errors' => true ) );
	}

	/**
	 * prepare_items function
	 *
	 * Prepares themes for display, applies search filters if available
	 *
	 * @return void
	 **/
	public function prepare_items() {
		global $ct;

		// setting wp_themes to null in case
		// other plugins have changed its value
		unset( $GLOBALS['wp_themes'] );

		// get available themes
		$ct     = $this->current_theme_info();

		// get allowed themes (checks to see if a themes has all necessary files - style.css and index.php)
		$themes = $this->get_themes();

		// handles theme searching by keyword
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search = strtolower( stripslashes( $_REQUEST['s'] ) );
			$this->search = array_merge( $this->search, array_filter( array_map( 'trim', explode( ',', $search ) ) ) );
			$this->search = array_unique( $this->search );
		}

		// handles theme search by features (tags, one column, widget etc)
		if ( !empty( $_REQUEST['features'] ) ) {
			$this->features = $_REQUEST['features'];
			$this->features = array_map( 'trim', $this->features );
			$this->features = array_map( 'sanitize_title_with_dashes', $this->features );
			$this->features = array_unique( $this->features );
		}

		// applies search and features terms from above to available themes
		// and remove themes that do not match the applied filters/keywords
		if ( $this->search || $this->features ) {
			foreach ( $themes as $key => $theme ) {
				if ( !$this->search_theme( $theme ) )
					unset( $themes[ $key ] );
			}
		}

		if( isset( $ct->name ) && isset( $themes[$ct->name] ) ) {
			unset( $themes[$ct->name] );
		}

		// sort themes using strnatcasecmp function
		uksort( $themes, 'strnatcasecmp' );

		// themes per page
		$per_page = 24;

		// get current page
		$page = $this->get_pagenum();
		$start = ( $page - 1 ) * $per_page;

		$this->items = array_slice( $themes, $start, $per_page );

		// set total themes and themes per page
		$this->set_pagination_args( array(
			'total_items' => count( $themes ),
			'per_page'    => $per_page,
		) );
	}

	/**
	 * display function
	 *
	 * Returns html display of themes table
	 *
	 * @return string
	 **/
	public function display() {
		$this->tablenav( 'top' );

		echo '<div id="availablethemes">' . $this->display_rows_or_placeholder() . '</div>';

		$this->tablenav( 'bottom' );
	}

	/**
	 * tablenav function
	 *
	 * @return void
	 **/
	public function tablenav( $which = 'top' ) {
		if ( $this->get_pagination_arg( 'total_pages' ) <= 1 )
			return;
		?>
		<div class="tablenav themes <?php echo $which; ?>">
			<?php $this->pagination( $which ); ?>
		   <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading list-ajax-loading" alt="" />
		  <br class="clear" />
		</div>
		<?php
	}

	/**
	 * ajax_user_can function
	 *
	 * @return bool
	 **/
	public function ajax_user_can() {
		// Do not check edit_theme_options here. AJAX calls for available themes require switch_themes.
		return current_user_can('switch_themes');
	}

	/**
	 * no_items function
	 *
	 * @return void
	 **/
	public function no_items() {
		if ( $this->search || $this->features ) {
			_e( 'No themes found.', AI1EC_PLUGIN_NAME );
			return;
		}

		if ( is_multisite() ) {
			if ( current_user_can( 'install_themes' ) && current_user_can( 'manage_network_themes' ) ) {
				printf( __( 'You only have one theme enabled for this site right now. Visit the Network Admin to <a href="%1$s">enable</a> or <a href="%2$s">install</a> more themes.', AI1EC_PLUGIN_NAME ),
                network_admin_url( 'site-themes.php?id=' . $GLOBALS['blog_id'] ),
                network_admin_url( 'theme-install.php' ) );

				return;
			} elseif ( current_user_can( 'manage_network_themes' ) ) {
				printf( __( 'You only have one theme enabled for this site right now. Visit the Network Admin to <a href="%1$s">enable</a> more themes.', AI1EC_PLUGIN_NAME ),
                network_admin_url( 'site-themes.php?id=' . $GLOBALS['blog_id'] ) );

				return;
			}
			// else, fallthrough. install_themes doesn't help if you can't enable it.
		} else {
			if ( current_user_can( 'install_themes' ) ) {
				printf( __( 'You only have one theme installed right now. You can choose from many free themes in the Timely Theme Directory at any time: ' .
                    'just click on the <a href="%s">Install Themes</a> tab above.', AI1EC_PLUGIN_NAME ),
                admin_url( AI1EC_THEME_SELECTION_BASE_URL ) );

				return;
			}
		}
		// Fallthrough.
		printf( __( 'Only the active theme is available to you. Contact the <em>%s</em> administrator to add more themes.', AI1EC_PLUGIN_NAME ),
            get_site_option( 'site_name' ) );
	}

	/**
	 * get_columns function
	 *
	 * @return array
	 **/
	public function get_columns() {
		return array();
	}

	/**
	 * display_rows function
	 *
	 * @return void
	 **/
	function display_rows() {
		$themes = $this->items;
		$theme_names = array_keys( $themes );
		natcasesort( $theme_names );

		foreach ( $theme_names as $theme_name ) {
			$class = array( 'available-theme' );
			?>
			<div class="<?php echo join( ' ', $class ); ?>">
			<?php
			if ( !empty( $theme_name ) ) :
				$template       = $themes[$theme_name]['Template'];
				$stylesheet     = $themes[$theme_name]['Stylesheet'];
				$title          = $themes[$theme_name]['Title'];
				$version        = $themes[$theme_name]['Version'];
				$description    = $themes[$theme_name]['Description'];
				$author         = $themes[$theme_name]['Author'];
				$screenshot     = $themes[$theme_name]['Screenshot'];
				$stylesheet_dir = $themes[$theme_name]['Stylesheet Dir'];
				$template_dir   = $themes[$theme_name]['Template Dir'];
				$parent_theme   = $themes[$theme_name]['Parent Theme'];
				$theme_root     = $themes[$theme_name]['Theme Root'];
				$theme_root_uri = esc_url( $themes[$theme_name]['Theme Root URI'] );
				$preview_link   = esc_url(
					Ai1ec_Meta::get_option( 'home' ) . '/'
				);

				if ( is_ssl() )
					$preview_link = str_replace( 'http://', 'https://', $preview_link );

				$preview_link = htmlspecialchars(
					add_query_arg(
						array(
							'preview'          => 1,
							'ai1ec_template'   => $template,
							'ai1ec_stylesheet' => $stylesheet,
							'preview_iframe'   => true,
							'TB_iframe'        => 'true'
						),
						$preview_link
					)
				);

				$preview_text   = esc_attr( sprintf( __( 'Preview of &#8220;%s&#8221;', AI1EC_PLUGIN_NAME ), $title ) );
				$tags           = $themes[$theme_name]['Tags'];
				$thickbox_class = 'thickbox thickbox-preview';
				$activate_link  = wp_nonce_url(
					admin_url( AI1EC_THEME_SELECTION_BASE_URL ) .
					"&amp;action=activate&amp;ai1ec_template=" .
					urlencode( $template ) .
					"&amp;ai1ec_stylesheet=" .
					urlencode( $stylesheet ),
					'switch-ai1ec_theme_' . $template
				);
				$activate_text  = esc_attr( sprintf( __( 'Activate &#8220;%s&#8221;', AI1EC_PLUGIN_NAME ), $title ) );
				$actions        = array();
				$actions[]      = '<a href="' . $activate_link .  '" class="activatelink" title="' . $activate_text . '">' .
				                  __( 'Activate', AI1EC_PLUGIN_NAME ) . '</a>';
				$actions[]      = '<a href="' . $preview_link . '" class="thickbox thickbox-preview" title="' .
				                  esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', AI1EC_PLUGIN_NAME ), $theme_name ) ) . '">' .
				                  __( 'Preview', AI1EC_PLUGIN_NAME ) . '</a>';

				if( ! is_multisite() && current_user_can( 'delete_themes' ) ) {
					$delete_link = wp_nonce_url(
						admin_url( AI1EC_THEME_SELECTION_BASE_URL ) .
						"&amp;action=delete&amp;ai1ec_template=$stylesheet", 'delete-ai1ec_theme_' . $stylesheet
					);
					$actions[] = '<a class="submitdelete deletion" href="' .
					             $delete_link .
					             '" onclick="' . "return confirm( '" .
					             esc_js( sprintf(
						             __( "You are about to delete this theme '%s'\n  'Cancel' to stop, 'OK' to delete.", AI1EC_PLUGIN_NAME ),
						             $theme_name
					             ) ) .
					             "' );" . '">' . __( 'Delete', AI1EC_PLUGIN_NAME ) . '</a>';
				}

				$actions = apply_filters( 'theme_action_links', $actions, $themes[$theme_name] );

				$actions = implode ( ' | ', $actions );
			?>
				<a href="<?php echo $preview_link; ?>" class="<?php echo $thickbox_class; ?> screenshot">
				<?php if ( $screenshot ) : ?>
					<img src="<?php echo $theme_root_uri . '/' . $stylesheet . '/' . $screenshot; ?>" alt="" />
				<?php endif; ?>
				</a>
				<h3>
			<?php
				/* translators: 1: theme title, 2: theme version, 3: theme author */
				printf( __( '%1$s %2$s by %3$s', AI1EC_PLUGIN_NAME ), $title, $version, $author ) ; ?></h3>
				<p class="description"><?php echo $description; ?></p>
				<span class='action-links'><?php echo $actions ?></span>
				<?php if ( current_user_can( 'edit_themes' ) && $parent_theme ) {
					/* translators: 1: theme title, 2:  template dir, 3: stylesheet_dir, 4: theme title, 5: parent_theme */ ?>
					<p>
						<?php
						printf(
							__( 'The template files are located in <code>%2$s</code>. The stylesheet files are located in <code>%3$s</code>. ' .
							    '<strong>%4$s</strong> uses templates from <strong>%5$s</strong>. Changes made to the templates will affect ' .
							    'both themes.', AI1EC_PLUGIN_NAME
							),
							$title,
							str_replace( WP_CONTENT_DIR, '', $template_dir ),
							str_replace( WP_CONTENT_DIR, '', $stylesheet_dir ),
							$title,
							$parent_theme );
						?>
					</p>
			<?php } else { ?>
				<p>
					<?php
					printf(
						__( 'All of this theme&#8217;s files are located in <code>%2$s</code>.', AI1EC_PLUGIN_NAME ),
						$title,
						str_replace( WP_CONTENT_DIR, '', $template_dir ),
						str_replace( WP_CONTENT_DIR, '', $stylesheet_dir )
					);
					?>
				</p>
			<?php } ?>
			<?php if ( $tags ) : ?>
				<p>
					<?php _e( 'Tags:', AI1EC_PLUGIN_NAME ); ?> <?php echo join( ', ', $tags ); ?>
				</p>
			<?php endif; ?>
			<?php theme_update_available( $themes[$theme_name] ); ?>
		<?php endif; // end if not empty theme_name ?>
			</div>
		<?php
		} // end foreach $theme_names
	}

	/**
	 * search_theme function
	 *
	 * @return void
	 **/
	function search_theme( $theme ) {
		$matched = 0;

		// Match all phrases
		if ( count( $this->search ) > 0 ) {
			foreach ( $this->search as $word ) {
				$matched = 0;

				// In a tag?
				if ( in_array( $word, array_map( 'sanitize_title_with_dashes', $theme['Tags'] ) ) )
					$matched = 1;

				// In one of the fields?
				foreach ( array( 'Name', 'Title', 'Description', 'Author', 'Template', 'Stylesheet' ) AS $field ) {
					if ( stripos( $theme[$field], $word ) !== false )
						$matched++;
				}

				if ( $matched == 0 )
					return false;
			}
		}

		// Now search the features
		if ( count( $this->features ) > 0 ) {
			foreach ( $this->features as $word ) {
				// In a tag?
				if ( !in_array( $word, array_map( 'sanitize_title_with_dashes', $theme['Tags'] ) ) )
					return false;
			}
		}

		// Only get here if each word exists in the tags or one of the fields
		return true;
	}

	/**
	 * {@internal Missing Short Description}}
	 *
	 * @since 2.0.0
	 *
	 * @return unknown
	 */
	function current_theme_info() {
		$themes = $this->get_themes();
		$current_theme = self::get_current_ai1ec_theme();

		if ( ! $themes ) {
			$ct = new stdClass;
			$ct->name = $current_theme;
			return $ct;
		}

		if ( ! isset( $themes[$current_theme] ) ) {
			delete_option( 'ai1ec_current_theme' );
			$current_theme = self::get_current_ai1ec_theme();
		}

		$ct = new stdClass;
		$ct->name = $current_theme;
		$ct->title = $themes[$current_theme]['Title'];
		$ct->version = $themes[$current_theme]['Version'];
		$ct->parent_theme = $themes[$current_theme]['Parent Theme'];
		$ct->template_dir = $themes[$current_theme]['Template Dir'];
		$ct->stylesheet_dir = $themes[$current_theme]['Stylesheet Dir'];
		$ct->template = $themes[$current_theme]['Template'];
		$ct->stylesheet = $themes[$current_theme]['Stylesheet'];
		$ct->screenshot = $themes[$current_theme]['Screenshot'];
		$ct->description = $themes[$current_theme]['Description'];
		$ct->author = $themes[$current_theme]['Author'];
		$ct->tags = $themes[$current_theme]['Tags'];
		$ct->theme_root = $themes[$current_theme]['Theme Root'];
		$ct->theme_root_uri = esc_url( $themes[$current_theme]['Theme Root URI'] );
		return $ct;
	}
	/**
	 * Retrieve current theme display name.
	 *
	 * If the 'current_theme' option has already been set, then it will be returned
	 * instead. If it is not set, then each theme will be iterated over until both
	 * the current stylesheet and current template name.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	static function get_current_ai1ec_theme() {
		if ( $theme = Ai1ec_Meta::get_option( 'ai1ec_current_theme' ) ) {
			return $theme;
		}

		$self_instance = new Ai1ec_Themes_List_Table();
		$themes = $self_instance->get_themes();
		$current_theme = 'Vortex';

		if ( $themes ) {
			$theme_names = array_keys( $themes );
			$current_template   = Ai1ec_Meta::get_option( 'ai1ec_template' );
			$current_stylesheet = Ai1ec_Meta::get_option( 'ai1ec_stylesheet' );

			foreach ( (array) $theme_names as $theme_name ) {
				if ( $themes[$theme_name]['Stylesheet'] == $current_stylesheet &&
						$themes[$theme_name]['Template'] == $current_template ) {
					$current_theme = $themes[$theme_name]['Name'];
					break;
				}
			}
		}

		update_option( 'ai1ec_current_theme', $current_theme );
		return $current_theme;
	}
	/**
	 * Retrieve list of WordPress theme features (aka theme tags)
	 *
	 * @since 3.1.0
	 *
	 * @return array  Array of features keyed by category with translations keyed by slug.
	 */
	static function get_theme_feature_list() {
		// Hard-coded list is used if api not accessible.
		$features = array(
				__('Colors') => array(
					'black'   => __( 'Black' ),
					'blue'    => __( 'Blue' ),
					'brown'   => __( 'Brown' ),
					'gray'    => __( 'Gray' ),
					'green'   => __( 'Green' ),
					'orange'  => __( 'Orange' ),
					'pink'    => __( 'Pink' ),
					'purple'  => __( 'Purple' ),
					'red'     => __( 'Red' ),
					'silver'  => __( 'Silver' ),
					'tan'     => __( 'Tan' ),
					'white'   => __( 'White' ),
					'yellow'  => __( 'Yellow' ),
					'dark'    => __( 'Dark' ),
					'light'   => __( 'Light ')
				),

			__('Width') => array(
				'fixed-width'    => __( 'Fixed Width' ),
				'flexible-width' => __( 'Flexible Width' )
			),

			__( 'Features' ) => array(
				'featured-images'       => __( 'Featured Images' ),
				'front-page-post-form'  => __( 'Front Page Posting' ),
				'full-width-template'   => __( 'Full Width Template' ),
				'rtl-language-support'  => __( 'RTL Language Support' ),
				'threaded-comments'     => __( 'Threaded Comments' ),
				'translation-ready'     => __( 'Translation Ready' )
			),

			__( 'Subject' )  => array(
				'holiday'       => __( 'Holiday' ),
				'photoblogging' => __( 'Photoblogging' ),
				'seasonal'      => __( 'Seasonal' )
			)
		);

		return $features;
	}

}
// END class
