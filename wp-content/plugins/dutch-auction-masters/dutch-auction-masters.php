<?php
/*
 * Plugin Name: Dutch auction masters
 * Plugin URI: http://www.dutchauctionmasters.com/
 * Description: This plugin enables you the ability to build your professional online auctioning website by only a few clicks.
 * Author: Dutch auction masters team.
 * Author URI: http://www.dutchauctionmasters.com/
 * Version: 1.7.0.0
 * Copyright (c) 2013 Second Company B.V. http://www.secondcompany.nl/
*/

load_plugin_textdomain ( 'dam-auction-masters', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
require_once 'Load.php';

if (!defined('DAM_DEBUG'))
    define('DAM_DEBUG', false);

if (!class_exists('DutchAuctionMasters')) {
    class DutchAuctionMasters
    {
        #region constance
        const PLUGIN_VERSION = "1.7.0.0";
        const CRON_JOB_INTERVAL = 5;
        const TEXT_DOMAIN = "dam-auction-masters";
        const PLUGIN_PREFIX = "dam_";
        const PLUGIN_NAME = "DamAuctions";
        const PLUGIN_VERSION_KEY = "SC_VERSION";
        const PLUGIN_SETTING_URL = "admin.php?page=dam_settings";
        const CAPABILITIES = "dam_edit_auction";
        const CRON_JOB_NAME = "dam_cron_job";
        const CRON_JOB_HOOK_NAME = "dam_cron_hook";
        const SHORT_CODE_BUTTON_KEY = "wpse_65456_button";
        const DAM_AUCTION_LIST_PAGE = "dam_auction_list_page";
        const DAM_AUCTION_DETAIL_PAGE = "dam_auction_detail_page";
        const DAM_USER_ROLE = "dam_user_role";
        const DAM_AUCTION_AUTHENTICATED = "dam_auction_authenticated";
        const DAM_TABLE_AUCTION = "dam_auction";
        const DAM_TABLE_ORDER = "dam_order";
        const DAM_POST_TYPE = "dam_auction";
        const DAM_TABLE_BID = "dam_bid";
        const DAM_TABLE_CATEGORY = "dam_category";
        const DAM_TABLE_SHIPPING_ADDRESS = "dam_shipping_address";
        #endregion

        #region configKeys
        const PLUGIN_BASE_NAME ="PluginBaseName";
        const PLUGIN_FILE = "PluginFile";
        const MAIN_MENUS ="MainMenus";
        const SUB_MENUS ="SubMenus";
        const SHORT_CODES ="ShortCodes";
        const TINY_MCE_BUTTONS = "TinyMceButtons";
        const WIDGETS = "Widgets";
        const SCRIPTS = "Scripts";
        const STYLES = "Styles";
        const LOCALIZE_SCRIPT = "LocalizeScript";
        const SCRIPT_TYPE = "type";
        const CUSTOM_POSTS = "CustomType";
        #endregion

        private $pluginUri;

        public function __construct()
        {
            $this->pluginUri = plugin_dir_url(__FILE__);
            global $SC_Config;
            $timezone = get_option('gmt_offset');
            $SC_Config = array(
                self::PLUGIN_FILE => __FILE__,
                self::PLUGIN_BASE_NAME => plugin_basename(__FILE__),
                self::MAIN_MENUS => array(),
                self::SUB_MENUS => array(
                    array(
                        "parent_slug" => "edit.php?post_type=dam_auction",
                        "page_title" => __("Help", DutchAuctionMasters::TEXT_DOMAIN),
                        "menu_title" => __("Help", DutchAuctionMasters::TEXT_DOMAIN),
                        "capability" => "activate_plugins",
                        "menu_slug" => "dam_help",
                        "includeUri" => "admin/help.php",
                        "function" => null,
                        "icon_url" => null,
                        "position" => null) // help
                ),
                self::WIDGETS => array(
                    'AuctionWidget',
                    'AuctionListWidget',
                ),
                self::SHORT_CODES => array(
                    array(
                        "tag" => "auction-list",
                        "widgetName" => 'AuctionListWidget',
                        "defaultAtts" => array('type' => 'all')
                    ),
                    array(
                        "tag" => "single-auction",
                        "widgetName" => 'AuctionWidget',
                        "defaultAtts" => array('title' => '', 'post_id' => null, 'id' => null, 'shortcode' => 'detail')
                    )
                ),
                self::LOCALIZE_SCRIPT => array(
                    'handle' => 'jquery',
                    'object_name' => 'dam_ajax',
                    'l10n' => array(
                        'timezone' => $timezone,
                        "ajaxUrl" => admin_url("admin-ajax.php"),
                        "login_url" => wp_login_url(),
                        "pluginUrl" => plugin_dir_url(__FILE__)
                    )
                ), // Localize script
                self::STYLES => array(
                    array(
                        'handle' => 'jquery-ui.css',
                        'src' => 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ), //jquery-ui.css
                    array(
                        'handle' => 'jquery-timepicker.css',
                        'src' => $this->pluginUri . 'assets/css/jquery-ui-timepicker-addon.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ), //jquery-timepicker.css
                    array(
                        'handle' => 'reveal.css',
                        'src' => $this->pluginUri . 'assets/css/reveal.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ), //reveal.css
                    array(
                        'handle' => 'jquery.multiselect.css',
                        'src' => $this->pluginUri . 'assets/css/jquery.multiselect.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ), //jquery.multiselect.css
                    array(
                        'handle' => 'front.css',
                        'src' => $this->pluginUri . 'assets/css/front.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ), //front.css
                    array(
                      'handle' => 'jquery.jqzoom.css',
                        'src' => $this->pluginUri. 'assets/css/jquery.jqzoom.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ),
                    array(
                        'handle' => 'my-auctions.css',
                        'src' => $this->pluginUri. 'assets/css/my-auctions.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ),
                    array(
                        'handle' => 'admin.css',
                        'src' => $this->pluginUri . 'assets/css/admin.css',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                    ), // admin.css
                ),
                self::SCRIPTS => array(
                    array(
                        'handle' => 'knockout.js',
                        'src' => $this->pluginUri . 'assets/js/knockout-3.0.0.js',
                        'deps' => '',
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ), //knockout.js
                    array(
                        'handle' => 'jquery-validation.js',
                        'src' => $this->pluginUri . 'assets/js/jquery.validation.js',
                        'deps' => array('jquery'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ), //jquery-validation.js
                    array(
                        'handle' => 'jquery-ui-slider-access.js',
                        'src' => $this->pluginUri . 'assets/js/jquery-ui-slider-access.js',
                        'deps' => array('jquery', 'jquery-ui-core', 'jquery-ui-slider'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ), //jquery-ui-slider-access.js
                    array(
                        'handle' => 'jquery-ui-timepicker.js',
                        'src' => $this->pluginUri . 'assets/js/jquery-ui-timepicker.js',
                        'deps' => array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-autocomplete'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ), //jquery-ui-timepicker.js
                    array(
                        'handle' => 'jquery.multiselect.js',
                        'src' => $this->pluginUri . 'assets/js/jquery.multiselect.js',
                        'deps' => array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-sortable'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ), //jquery.multiselect.js
                    array(
                        'handle' => 'jquery.jqzoom-core.js',
                        'src' => $this->pluginUri . 'assets/js/jquery.jqzoom-core.js',
                        'deps' => array('jquery'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                    ),
                    array(
                        'handle' => 'admin.js',
                        'src' => $this->pluginUri . 'assets/js/admin.js',
                        'deps' => array('jquery'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                        self::SCRIPT_TYPE => 'admin',
                    ), //admin.js
                    array(
                        'handle' => 'jquery.reveal.js',
                        'src' => $this->pluginUri . 'assets/js/jquery.reveal.js',
                        'deps' => array('jquery'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                        self::SCRIPT_TYPE => 'front',
                    ), //jquery.reveal.js
                    array(
                        'handle' => 'front.js',
                        'src' => $this->pluginUri . 'assets/js/front.js',
                        'deps' => array('jquery'),
                        'ver' => self::PLUGIN_VERSION,
                        'in_footer' => true,
                        self::SCRIPT_TYPE => 'front',
                    ), //front.js
                ),
                self::TINY_MCE_BUTTONS => array('wpse72394_button', 'wpse72395_button', 'wpse72396_button'),
                self::CUSTOM_POSTS => array(
                    array(
                        "name" => "dam_auction",
                        "fields" => array(
                            'picture' => array(
                                'label' => __('Picture', self::TEXT_DOMAIN),
                                'type' => 'image',
                                'maxlength' => "10",
                                'title' => __(" Picture", self::TEXT_DOMAIN)
                            ),
                            'original_price' => array(
                                'label' => __('Original price', self::TEXT_DOMAIN),
                                'type' => 'money',
                                'maxlength' => "10",
                                'required' => true,
                                'title' => __(' Original price is required', self::TEXT_DOMAIN),
                            ),
                            'shipping_fee' => array(
                                'label' => __('Shipping fee', self::TEXT_DOMAIN),
                                'type' => 'money',
                                'maxlength' => "10",
                                'required' => true,
                                'title' => __(' Shipping fee is required', self::TEXT_DOMAIN),
                            ),
                            'start_price' => array(
                                'label' => __('Start price', self::TEXT_DOMAIN),
                                'type' => 'money',
                                'maxlength' => "10",
                                'required' => true,
                                'title' => __(' Start price is required', self::TEXT_DOMAIN),
                            ),
                            'step_price' => array(
                                'label' => __('Step price', self::TEXT_DOMAIN),
                                'type' => 'money',
                                'maxlength' => "10",
                                'required' => true,
                                'title' => __(' Step price is required', self::TEXT_DOMAIN),
                            ),
                            'begin' => array(
                                'label' => __('Begin', self::TEXT_DOMAIN),
                                'type' => 'datetime',
                                'maxlength' => "10",
                                'required' => true,
                                'title' => __(' Begin is required', self::TEXT_DOMAIN),
                            ),
                            'end' => array(
                                'label' => __('End', self::TEXT_DOMAIN),
                                'type' => 'datetime',
                                'maxlength' => "10",
                                'required' => true,
                                'title' => __(' End is required', self::TEXT_DOMAIN),
                            ),
                            'description' => array(
                                'label' => __('Description', self::TEXT_DOMAIN),
                                'type' => 'editor',
                                'maxlength' => "10",
                                'required' => false,
                                'title' => __(' Description', self::TEXT_DOMAIN),
                            ),
                        ),
                        "args" => array(
                            'show_ui' => true,
                            'menu_icon' => plugin_dir_url(__FILE__) . "assets/images/icon.png",
                            'show_in_menu' => true,
                            'capabilities' => array(
                                'edit_post' => 'update_core',
                                'read_post' => 'update_core',
                                'delete_post' => 'update_core',
                                'edit_posts' => 'update_core',
                                'edit_others_posts' => 'update_core',
                                'publish_posts' => 'update_core',
                                'read_private_posts' => 'update_core'
                            ),
                            'hierarchical' => true,
                            'public' => true,
                            'supports' => array('title', 'tags', 'thumbnail', 'author', 'comments', 'excerpt'),
                            'labels' => array(
                                'name' => __('Dam Auctions', self::TEXT_DOMAIN),
                                'all_items' => __('Auctions', self::TEXT_DOMAIN),
                                'singular_name' => __('Auction', self::TEXT_DOMAIN),
                                'add_new' => __('Add Auction', self::TEXT_DOMAIN),
                                'add_new_item' => __('Add Auction', self::TEXT_DOMAIN),
                                'edit_item' => __('Edit Auction', self::TEXT_DOMAIN),
                                'new_item' => __('New', self::TEXT_DOMAIN),
                                'view_item' => __('View', self::TEXT_DOMAIN),
                                'search_items' => __('Search auctions', self::TEXT_DOMAIN),
                                'not_found' => __('No auction found', self::TEXT_DOMAIN),
                                'not_found_in_trash' => __('No auction found in trash', self::TEXT_DOMAIN)
                            ),
                            'taxonomies' => array('category', 'post_tag')
                        )
                    )
                ),
            );

            add_action( 'init', array( $this, 'pluginInit'));
            add_action ( 'plugins_loaded',  array( $this, "checkDataBase") );
            add_action ( 'admin_menu', array ( $this, 'addPluginMenu'));
            add_action( 'widgets_init', array( $this, 'widgetsInit'));
            register_activation_hook ( __FILE__,  array( $this, 'doActivation'));
            register_deactivation_hook ( __FILE__, array( $this, 'doDeactivation'));
            add_action( 'plugin_action_links', array( $this, 'addSettingLink'), 10, 4);
            add_action( 'wp_ajax_ajaxHandle', array( $this,'ajaxHandle'));
            add_action( 'wp_ajax_nopriv_ajaxHandle',array( $this,'ajaxHandle'));
            add_action( "admin_enqueue_scripts", array( $this,"admin_enqueue_scripts"),10, 1 );
            add_action( "wp_enqueue_scripts", array( $this,"front_enqueue_scripts"));
            add_action( 'wp_footer', array( $this, 'front_footer'));
            $this->addCronJob();
        }

        public function pluginInit()
        {
            $this->addShortButtons();
            $this->addPluginCustomType();
            ob_start();
            add_action('dam_enqueue_scripts',array($this, 'enqueueScriptsAndStyles'),10, 1);
        }

        public function widgetsInit()
        {
            $pluginWidgets = $this->getConfig(self::WIDGETS);
            foreach($pluginWidgets as $widget)
            {
                register_widget( $widget);
            }
            $this->addPluginShortCodes();
        }

        public function addPluginMenu()
        {
            $pluginMainMenus = $this->getConfig(self::MAIN_MENUS);
            $pluginSubMenus =  $this->getConfig(self::SUB_MENUS);
            new SC_Menu($pluginMainMenus, $pluginSubMenus);
        }

        public function addPluginCustomType()
        {
            $configItems = $this->getConfig(self::CUSTOM_POSTS);
            new SC_CustomPost($configItems);
        }

        public function addSettingLink($links, $file)
        {
            global $SC_Config;
            $pluginBaseName = $SC_Config[self::PLUGIN_BASE_NAME];
            if ( $file == $pluginBaseName ) {
                $settings_link = '<a href="' .
                    admin_url(self::PLUGIN_SETTING_URL) . '">' .
                    __("Settings", self::TEXT_DOMAIN ) . '</a>';
                array_unshift( $links, $settings_link );
            }
            return $links;
        }

        public function registerTinymcePlugin($plugin_array)
        {
            $newButtons = $this->getConfig(self::TINY_MCE_BUTTONS);
            $plugin_array[$newButtons[0]] = plugin_dir_url ( __FILE__ )."assets/js/shortcode.js";
            return $plugin_array;
        }

        public function tinymceButtons($buttons)
        {
            $newButtons = $this->getConfig(self::TINY_MCE_BUTTONS);
            $buttons = array_merge($buttons,$newButtons);
            return $buttons;
        }

        public function admin_enqueue_scripts()
        {
            global $hook_suffix, $typenow;
            $scopes = $this->getScopes();
            $customPostTypes = $this->getCustomTypes();
            if(in_array($hook_suffix, $scopes) || in_array($typenow, $customPostTypes))
            {
                $this->enqueueScriptsAndStyles('admin');
                wp_dequeue_script( 'autosave' );
            }
        }

        public function front_enqueue_scripts()
        {
            $this->enqueueScriptsAndStyles('front');
        }

        protected function addPluginShortCodes()
        {
            $shortCodes = $this->getConfig(self::SHORT_CODES);
            foreach($shortCodes as $shortCode)
            {
                new SC_ShortCode($shortCode);
            }
        }

        protected function getCustomTypes()
        {
            global $SC_Config;
            if(array_key_exists(self::CUSTOM_POSTS, $SC_Config))
            {
                $configItems = $SC_Config[self::CUSTOM_POSTS];
                $customPostTypes = array();
                foreach($configItems as $item)
                {
                    $customPostTypes[] = $item['name'];
                }
                return $customPostTypes;
            }
            else{
                return array();
            }
        }

        protected function getScopes()
        {
            global $SC_Config;
            if(array_key_exists(SC_Menu::MENU_SCOPE_KEY, $SC_Config))
                return $SC_Config[SC_Menu::MENU_SCOPE_KEY];
            else
                return array();
        }

        protected function addCronJob()
        {
            add_action ( self::CRON_JOB_HOOK_NAME, array( $this, 'cronProcession') );
            add_filter ( 'cron_schedules', array($this, 'addSchedules'));
            if (! wp_next_scheduled ( self::CRON_JOB_HOOK_NAME) ) {
                wp_schedule_event ( time (), self::CRON_JOB_NAME, self::CRON_JOB_HOOK_NAME );
            }
        }

        public function addSchedules($schedules)
        {
            $schedules[self::CRON_JOB_NAME] = array (
                'interval' => self::CRON_JOB_INTERVAL,
                'display' => sprintf( __ ( 'Every %1$s Seconds', self::TEXT_DOMAIN ), self::CRON_JOB_INTERVAL ));
            return $schedules;
        }

        protected function addShortButtons()
        {
            if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
                return;
            add_filter("mce_external_plugins", array($this, "registerTinymcePlugin"));
            add_filter('mce_buttons', array($this, 'tinymceButtons'));
        }

        protected function getConfig($key)
        {
            global $SC_Config;
            if($SC_Config && array_key_exists($key, $SC_Config))
                return $SC_Config[$key];
            else
                return array();
        }

        public function enqueueScriptsAndStyles($type)
        {
            $scripts = $this->getConfig(self::SCRIPTS);
            $this->enqueueScripts($scripts , $type);
            $styles = $this->getConfig(self::STYLES);
            $this->enqueueStyles($styles ,$type);
            $localizeScript = $this->getConfig(self::LOCALIZE_SCRIPT);
            $this->localizeScript($localizeScript);
        }

        protected function enqueueStyles($styles, $styleType)
        {
            foreach ($styles as $style) {
                extract($style);
                if(isset($type) && $type!=$styleType)
                    continue;
                wp_register_style($handle, $src, $deps, $ver, 'all');
                wp_enqueue_style($handle);
            }
        }

        protected function enqueueScripts($scripts, $scriptType)
        {
            foreach ($scripts as $script) {
                extract($script);
                if(isset($type) && $type!=$scriptType)
                    continue;
                wp_register_script($handle, $src, $deps, $ver, $in_footer);
                wp_enqueue_script($handle);
                unset($type);
            }
        }

        protected function localizeScript($config)
        {
            extract($config);
            wp_localize_script($handle, $object_name, $l10n);
        }

        public function doDeactivation()
        {
            if (DAM_DEBUG)
                SC_functions::removeDATA();
        }

        public function doActivation()
        {
            SC_functions::initPlugin();
        }

        public function checkDataBase()
        {
            $dbVersion = get_option(self::PLUGIN_VERSION_KEY);
            if($dbVersion === false || version_compare(self::PLUGIN_VERSION, $dbVersion) > 0 )
            {
                SC_DataProvider::initDatabase();
                update_option(self::PLUGIN_VERSION_KEY, self::PLUGIN_VERSION);
                $this->upgrade();
            }
        }

        public function upgrade()
        {
            /** check if plugin lower than 1.6.0.0 */
            $lowerVersion = get_option('dam_auction_masters_db_version');
            if($lowerVersion)
            {
                SC_DataProvider::upgrade();
                delete_option("dam_auction_masters_db_version");
            }
        }

        public function front_footer()
        {
            if (get_option("dam_auction_powered_by")) {
                echo '<div class="powered-by"><a href="http://www.dutchauctionmasters.com/">Powered by DAM</a> <br/></div>';
            }
        }

        public function ajaxHandle()
        {
            new SC_AjaxHandle($_REQUEST);
        }

        public function cronProcession()
        {
            SC_functions::cronProcession();
        }

        public static function doUninstall()
        {
            SC_functions::removeDATA();
        }
    }

    new DutchAuctionMasters();
}