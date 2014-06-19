<?php

if (!class_exists('SC_Settings')) {
    class SC_Settings
    {
        const GROUP_NAME = "dam-auction-group";
        const TEXT_DOMAIN = "dam-auction-masters";

        public function __construct()
        {
            $this->menu = array(
                "parent_slug" => "edit.php?post_type=dam_auction",
                "page_title" => __("Settings", self::TEXT_DOMAIN),
                "menu_title" => __("Settings", self::TEXT_DOMAIN),
                "capability" => "activate_plugins",
                "menu_slug" => "dam_settings",
                "function" => array($this, "render"),
                "icon_url" => null,
                "position" => null);
            $this->items = array(
                'dam_auction_admin_name' => array(
                    'label' => __('Admin name', self::TEXT_DOMAIN),
                    'type' => 'input',
                    'maxlength' => "50",
                    'required' => true,
                    'title' => __(' Admin name is required', self::TEXT_DOMAIN),
                    'description' => __('This will be used as the sender for notification admin name.', self::TEXT_DOMAIN),
                ),
                'dam_auction_admin_email' => array(
                    'label' => __('Admin email', self::TEXT_DOMAIN),
                    'type' => 'input',
                    'maxlength' => "50",
                    'required' => true,
                    'title' => __(' Admin email is required', self::TEXT_DOMAIN),
                    'description' => __('This will be used as the sender for notification emails.', self::TEXT_DOMAIN),
                ),
                'dam_auction_list_page' => array(
                    'label' => __('Auction list page', self::TEXT_DOMAIN),
                    'type' => 'select',
                    'maxlength' => "50",
                    'required' => false,
                    'options' => array($this, 'getPageOptions'),
                    'title' => __(' Auction list page is required', self::TEXT_DOMAIN),
                    'description' => __('The page will display an auction widget.', self::TEXT_DOMAIN),
                ),
                'dam_user_role' => array(
                    'label' => __('Roles allows to bid', self::TEXT_DOMAIN),
                    'type' => 'select',
                    'maxlength' => "50",
                    'required' => true,
                    'multiple' => 'multiple',
                    'title' => __(' Roles required', self::TEXT_DOMAIN),
                    'description' => __('Choose roles to bid.', self::TEXT_DOMAIN),
                    'options' => array($this, 'getRoles'),
                ),
                'dam_currency' => array(
                    'label' => __('Currency', self::TEXT_DOMAIN),
                    'type' => "group",
                    'required' => true,
                    'items' => array(
                        'dam_currency_decimal_point' => array(
                            'label' => __('decimal point', self::TEXT_DOMAIN),
                            'type' => 'input',
                            'maxlength' => "1",
                            'default'=>',',
                            'style'=>'width:30px;',
                        ),
                        'dam_currency_thousands_step' => array(
                            'label' => __('Currency thousands step', self::TEXT_DOMAIN),
                            'type' => 'input',
                            'maxlength' => "1",
                            'default'=>'.',
                            'style'=>'width:30px;',
                        ),
                        'dam_currency_symbol' => array(
                            'label' => __('Currency symbol', self::TEXT_DOMAIN),
                            'type' => 'input',
                            'maxlength' => "5",
                            'default'=>'&euro;',
                            'style'=>'width:50px;',
                        ),
                    ),
                ),
                'dam_pay_url'=> array(
                    'label' => __('Pay url', self::TEXT_DOMAIN),
                    'type' => 'input',
                    'maxlength' => "355",
                    'required' => false,
                    'style'=>'width:300px',
                    'title' => __(' Pay url is required', self::TEXT_DOMAIN),
                    'description' => __('The url will change the default pay url.', self::TEXT_DOMAIN),
                ),
                'dam_auction_powered_by' => array(
                    'label' => __('Show power by dam', self::TEXT_DOMAIN),
                    'type' => 'radios',
                    'maxlength' => "50",
                    'required' => false,
                    'options' => array("0" => __("Disable", self::TEXT_DOMAIN), "1" => __("Enable", self::TEXT_DOMAIN)),
                    'title' => __(' Show power by dam', self::TEXT_DOMAIN),
                ),
            );

            add_action('admin_menu', array($this, 'initMenu'));
            add_action('admin_init', array($this, 'registerAndRender'));
        }

        public function registerAndRender()
        {
            $this->hookedItems();
            foreach ($this->items as $key => $args) {
                if ($args['type'] == "group" && isset($args['items'])) {
                    foreach ($args['items'] as $subKey => $subArgs) {
                        register_setting(self::GROUP_NAME, $subKey);
                    }
                } else
                    register_setting(self::GROUP_NAME, $key);
            }
        }

        public function initMenu()
        {
            $this->registerAndRender();
            extract($this->menu);
            add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        }

        public function hookedItems()
        {
            $this->items = apply_filters('dam_settings', $this->items);
        }

        public function render()
        {
            $this->hookedItems();
            ?>
            <div class="wrap">
                <?php do_action("dam-settings-header"); ?>
                <h2><?php _e("Dam auction settings", self::TEXT_DOMAIN) ?></h2>

                <form method="post" action="options.php">
                    <?php settings_fields(self::GROUP_NAME); ?>
                    <table class="form-table">
                        <?php
                        foreach ($this->items as $key => $field) {
                            $field['name'] = $key;
                            $field['value'] = get_option($key);

                            if ($field['type'] == "group" && isset($field['items']))
                            {
                                foreach ($field['items'] as $subKey => $subArgs) {
                                    $field['items'][$subKey]['value'] = get_option($subKey);
                                }
                            }

                            $this->renderItems($field);
                        }
                        ?>
                    </table>

                    <?php submit_button();?>
                </form>
            </div>
        <?php
        }

        public function getRoles()
        {
            global $wp_roles;
            $allRoles = array_keys($wp_roles->roles);
            $roles = array();
            foreach ($allRoles as $key) {
                $roles[$key] = $key;
            }
            return $roles;
        }

        private function renderItems($field)
        {
            SC_FieldControls::render($field);
        }

        public function getPageOptions()
        {
            $pages = get_pages(array('post_status' => 'publish,private'));
            $pageOptions = array();
            $pageOptions[0] = __("Select a page", self::TEXT_DOMAIN);
            if (!$pages)
                return $pageOptions;

            foreach ($pages as $page) {
                $pageOptions[$page->ID] = $page->post_title;
            }
            return $pageOptions;
        }
    }

    new SC_Settings();
}