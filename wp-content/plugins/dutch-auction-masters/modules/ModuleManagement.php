<?php

if (!class_exists('SC_ModuleManagement')) {
    class SC_ModuleManagement
    {
        const TEXT_DOMAIN = "dam-auction-masters";
        const SERVER_URL = "http://dutchauctionmasters.com";

        function __construct()
        {
            add_action('dam-settings-header', array($this, 'render'));
        }

        public function render()
        {
            $table = new SC_Modules();
            $table->prepare_items();

            if (isset($_REQUEST['dl']) || isset($_REQUEST['upgrade']))
                $this->installModule();
            $headText = __('Dam modules', self::TEXT_DOMAIN);
            echo "<h2>$headText</h2>";
            echo "<div class='dam-modules' style='max-width:920px;'>";
            $table->display();
            echo "</div>";
            $this->renderScript();
        }

        public function renderScript()
        {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('.button.activate, .button.deactivate').click(function () {
                        var pluginId = $(this).data('id');
                        var action = $(this).hasClass('activate') ? "plugin_activate" : "plugin_deactivate";
                        $.ajax({
                            type: "POST",
                            url: dam_ajax.ajaxUrl,
                            data: {
                                'action': 'ajaxHandle',
                                'act': action,
                                'id': pluginId
                            },
                            success: function (res) {
                                if (res) {
                                    window.location.reload();
                                }
                            },
                            error: function () {

                            }
                        });
                    });
                    $('.button.buy').click(function () {
                        var url = $(this).data('url');
                        var id = $(this).data('id');
                        var name = $(this).data('name');
                        var returnUrl = $(this).data('return');
                        var form = $('<form>', {
                            'action': url,
                            'method': 'POST'
                        });
                        form.append($('<input>', {
                            'name': 'id',
                            'value': id
                        }));
                        form.append($('<input>', {
                            'name': 'module',
                            'value': name
                        }));
                        form.append($('<input>', {
                            'name': 'ref',
                            'value': encodeURI(returnUrl)
                        }));
                        form.appendTo(document.body).submit();
                    });

                    $('.button.upgrade').click(function () {
                        var upgradeUrl = $(this).data('return');
                        window.location.href = upgradeUrl;
                    });
                });
            </script>
        <?php
        }

        public function setUnSafeUrl($r, $url)
        {
            $r['reject_unsafe_urls'] = false;
            return $r;
        }

        public function installModule()
        {
            $isUpgrade = isset($_REQUEST['upgrade']);
            if ($isUpgrade) {
                $url = $_REQUEST['upgrade'];
            } else {
                $url = $_REQUEST['dl'];
            }
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        //    require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php');
            require_once(ABSPATH . 'wp-admin/admin-header.php');
            $upgrade = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('type', 'title', 'nonce')));
            add_filter('http_request_args', array($this, 'setUnSafeUrl'), 10, 2);

            if ($isUpgrade) {
                $plugin = $_REQUEST["plugin"];
                $args=  array(
                    'package' => $url,
                    'destination' => WP_PLUGIN_DIR,
                    'clear_destination' => true,
                    'clear_working' => true,
                    'hook_extra' => array(
                        'plugin' => $plugin,
                        'type' => 'plugin',
                        'action' => 'update',
                    ),
                );

                $upgrade->init();
                $upgrade->upgrade_strings();
                $result = $upgrade->run($args);
            } else
                $result = $upgrade->install($url);

            if ($result) {
                $ref = add_query_arg(array('post_type' => 'dam_auction', 'page' => 'dam_settings'), admin_url('edit.php'));
                ?>
                <script type="text/javascript">
                    document.location.href = "<?php echo $ref?>";
                </script>
            <?php
            }
        }
    }

    new SC_ModuleManagement();
}

if (!class_exists('SC_Modules')) {

    class SC_Modules extends WP_List_Table
    {
        const TEXT_DOMAIN = "dam-auction-masters";
        public function __construct()
        {
            parent::__construct(array(
                'singular' => 'module',
                'plural' => 'modules'
            ));
        }

        public function column_default($item, $column_name)
        {
            /** @var  $item SC_Module */
            $text = $item->$column_name;
            if ($item->Status === SC_PluginStatus::ACTIVATED || $item->Status === SC_PluginStatus::INACTIVE || $item->Status === SC_PluginStatus::UPDATE_AVAILABLE)
                $text = "<strong style='color:#2EA2CC'>$text</strong>";
            $result = $text;
            if(!empty($item->LinkUrl) && $column_name=="Name" )
                $result ="<a href='".$item->LinkUrl."' target='_blank'>$text</a>";

            return $result;
        }

        public function column_status($item)
        {
              /** @var  $item SC_Module */
            $id = $item->Id;
            $moduleId = reset(explode('/', $item->Id));
            $buyUrl = SC_ModuleManagement::SERVER_URL . '/modules.php?action=buy';
            $returnUrl = add_query_arg(array('post_type' => 'dam_auction', 'page' => 'dam_settings', 'module' => $moduleId, 'act' => 'installModule'), admin_url('edit.php'));

            $buttonArray = array(
                SC_PluginStatus::ACTIVATED => __("Deactivate", self::TEXT_DOMAIN),
                SC_PluginStatus::INACTIVE => __("Activate", self::TEXT_DOMAIN),
                SC_PluginStatus::AVAILABLE => __("Buy now", self::TEXT_DOMAIN),
                SC_PluginStatus::UNAVAILABLE => __("Coming soon", self::TEXT_DOMAIN)
            );

            $button = $buttonArray[$item->Status];
            $args = array(
                SC_PluginStatus::ACTIVATED => "<button data-id='$id' class='button deactivate'>$button</button>",
                SC_PluginStatus::INACTIVE => "<button data-id='$id' class='button activate button-primary'>$button</button>",
                SC_PluginStatus::AVAILABLE => "<button data-id='$id' data-name='$moduleId' data-url='$buyUrl' data-return='$returnUrl' class='button buy button-primary'>$button</button>",
                SC_PluginStatus::UNAVAILABLE => "<span>$button</span>",
            );

            /** upgrade button */
            $buttonText = __("Upgrade", self::TEXT_DOMAIN);
            $plugin = urlencode($id);
            $host = urlencode($_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST']);
            $upgradeUrl = urlencode(SC_ModuleManagement::SERVER_URL . "/modules.php?action=upgrade&plugin=$plugin&host=$host" );
            $returnUrl = add_query_arg(array('post_type' => 'dam_auction', 'page' => 'dam_settings', 'plugin' => $plugin, 'upgrade' => $upgradeUrl), admin_url('edit.php'));
            $upgrade = $item->AvailableUpgrade ? "<br /><button style='margin-top: 5px' data-return='$returnUrl' class='button upgrade button-primary'>$buttonText</button>" : "";
            return $args[$item->Status] . $upgrade;
        }

        public function get_columns()
        {
            $columns = array(
                "Name" => __("Name", self::TEXT_DOMAIN),
                "Version" => __("Version", self::TEXT_DOMAIN),
                "Price" => __("Price", self::TEXT_DOMAIN),
                "Description" => __("Description", self::TEXT_DOMAIN),
                "Dependency" => __("Dependency on", self::TEXT_DOMAIN),
                "Status" => __("Operation", self::TEXT_DOMAIN),
            );
            return $columns;
        }

        public function prepare_items()
        {
            $plugins = get_plugins();
            $modulesUrl = SC_ModuleManagement::SERVER_URL . "/modules.php?action=modules";
            $content = file_get_contents($modulesUrl);
            $json = json_decode($content, true);
            $modules = shortcode_atts($json, $plugins);
            $per_page = 100;
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array(
                $columns,
                $hidden,
                $sortable
            );
            $total_items = count($modules);
            $items = array();
            foreach ($modules as $key => $value) {
                $raw = $json[$key];
                $value['Id'] = $key;
                $value['Price'] = $raw['Price'] . ' ' . $raw['Currency'];
                $module = SC_Module::parse($value);

                if (array_key_exists($key, $plugins))
                    $module->Status = is_plugin_active($key);
                else if ($value['Available'] == SC_PluginStatus::AVAILABLE)
                    $module->Status = SC_PluginStatus::AVAILABLE;
                else
                    $module->Status = SC_PluginStatus::UNAVAILABLE;

                if (version_compare($raw['Version'], $value['Version']) > 0)
                    $module->AvailableUpgrade = true;

                if(isset($raw['LinkUrl']))
                {
                    $module->LinkUrl = $raw['LinkUrl'];
                }

                if(empty($module->Dependency))
                    $module->Dependency ="Dam Pro";
                $items[] = $module;
            }

            $this->items = $items;
            $this->set_pagination_args(array(
                'total_items' => $total_items, // total items defined above
                'per_page' => $per_page, // per page constant defined at top of method
                'total_pages' => ceil($total_items / $per_page) // calculate pages count
            ));
        }
    }
}

if (!class_exists('SC_PluginStatus')) {
    class SC_PluginStatus
    {
        const ACTIVATED = true;
        const INACTIVE = false;
        const UNAVAILABLE = "unavailable";
        const AVAILABLE = "available";
        const UPDATE_AVAILABLE = "update_available";
    }
}

if (!class_exists('SC_Module')) {
    class SC_Module
    {
        public $Id;
        public $Name;
        public $Version;
        public $Description;
        public $Price;
        public $DownloadUrl;
        public $Status;
        public $AvailableUpgrade;
        public $Dependency;
        public $LinkUrl;

        public static function parse($args, $default = '')
        {
            $values = wp_parse_args($args, $default);
            $instance = new self();
            $vars = get_object_vars($instance);
            foreach ($vars as $key => $value) {
                if (array_key_exists($key, $values))
                    $instance->$key = $values[$key];
            }
            return $instance;
        }
    }
}