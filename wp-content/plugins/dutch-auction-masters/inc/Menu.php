<?php
/**
 * Created by PhpStorm.
 * User: Ftx
 * Date: 13-11-4
 * Time: 上午12:14
 */

if(!class_exists('SC_Menu'))
{
    class SC_Menu {
        const MENU_SCOPE_KEY = "PluginScopes";

        public function __construct($mainMenusConfig, $pluginSubMenusConfig)
        {
            foreach($mainMenusConfig as $mainMenuConfig)
            {
                extract($mainMenuConfig);
                $fileInclude = new SC_FileInclude($includeUri);
                if(!isset($function))
                {
                    $mainMenu = add_menu_page ( $page_title, $menu_title, $capability, $menu_slug,
                        array($fileInclude, 'includeFile') , $icon_url, $position);
                }else
                {
                    $mainMenu = add_menu_page ( $page_title, $menu_title, $capability, $menu_slug, $function , $icon_url, $position);
                }
                $this->addScope($mainMenu);
                unset($function);
                unset($fileInclude);
            }

            foreach($pluginSubMenusConfig as $subMenu)
            {
                extract($subMenu);
                $fileInclude = new SC_FileInclude($includeUri);
                if(!isset($function))
                {
                    $subPage =  add_submenu_page($parent_slug, $page_title , $menu_title, $capability, $menu_slug,
                        array($fileInclude, 'includeFile') , $icon_url, $position);
                }else{
                    $subPage =  add_submenu_page($parent_slug, $page_title , $menu_title, $capability, $menu_slug,
                        $function , $icon_url, $position);
                }
                $this->addScope($subPage);
                unset($function);
                unset($fileInclude);
            }
        }
        protected function addScope($menuPage)
        {
            global $SC_Config;
            if(array_key_exists(self::MENU_SCOPE_KEY, $SC_Config))
            {
                array_push( $SC_Config[self::MENU_SCOPE_KEY] , $menuPage);
            }
            else
            {
                $SC_Config[self::MENU_SCOPE_KEY]  = array();
                array_push( $SC_Config[SC_Menu::MENU_SCOPE_KEY] , $menuPage);
            }
        }

    }
}