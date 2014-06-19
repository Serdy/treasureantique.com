<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */

if (!class_exists('SC_AjaxHandle')) {
    class SC_AjaxHandle
    {
        function __construct($data)
        {
            header('Content-type: text/json');
            header('Content-type: application/json');
            $action = isset($data ["act"]) ? $data ["act"] : "";
            $actionsMappings = array(
                "register" => "register",
                "place_bid" => "place_bid",
                "status" => "status",
                "login" => "login",
                "auctions" => "auctions",
                "delivery" => "delivery",
                "confirm_receiving" => "confirm_receiving",
                "plugin_activate" => "activatePlugin",
                "plugin_deactivate" => "deactivatePlugin",
                "installModule" => "installModule",
            );

            if (array_key_exists($action, $actionsMappings)) {
                $result = call_user_func(array($this, $actionsMappings[$action]), $data);
            } else {
                $result = apply_filters("dam_auction_ajax_call", '', $data);
            }
            if (is_string($result))
                echo $result;
            else
                echo json_encode($result);
            die;
        }

        function deactivatePlugin($data)
        {
            $plugins = array();
            $plugins[] = $data['id'];
            deactivate_plugins($plugins);
            return true;
        }

        function activatePlugin($data)
        {
            activate_plugin($data['id']);
            return true;
        }

        function register($data)
        {
            $result = SC_functions::unauthenticated_place_bid($data ['id'], $data ['bid_price']);
            return json_encode($result);
        }

        function place_bid($data)
        {
            $result = SC_functions::authenticated_place_bid($data ['id'], $data ['bid_price']);
            return json_encode($result);
        }

        function installModule($data)
        {
            $url = SC_ModuleManagement::SERVER_URL.$data['res'].'.zip';
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
            require_once(ABSPATH . 'wp-admin/admin-header.php');
            $upgrade = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('type', 'title', 'nonce') ) );
            ob_start();
            $upgrade->install( $url );
            $contents = ob_get_contents();
            ob_end_clean();
            require_once(ABSPATH . 'wp-admin/admin-footer.php');
            $settingUrl = add_query_arg(array('post_type' => 'dam_auction', 'page'=>'dam_settings'), admin_url('edit.php'));
            die;
        }

        static function status($data)
        {
            $result = SC_DataProvider::get_status($data ['id']);
            return json_encode($result);
        }

        static function login($data)
        {
            $credentials = array();
            $credentials ['user_login'] = $data ["username"];
            $credentials ['user_password'] = $data ["password"];
            $credentials ['remember'] = true;
            $result = new stdClass ();
            $user = wp_signon($credentials);
            $can_user_bid = SC_functions::can_user_bid($user);
            if (is_wp_error($user)) {
                $result->error = 1;
                $result->error_message = $user->get_error_message();
                return json_encode($result);
            } else if ($can_user_bid && $user->display_name) {
                $result->error = 0;
                return json_encode($result);
            } else {
                $result->error = 1;
                $result->error_message = __("<strong>ERROR</strong>: Invalid user role, current role not allows to bid", "dam-auction-masters");
                return json_encode($result);
            }
        }

        static function auctions($data)
        {
            $status = $data ['status'];
            $start = isset ($data ['start']) ? max(0, intval($data ['start'])) : 0;
            if (!isset($data ['category']) || empty($data ['category'])) {
                $category = null;
            } else {
                $category = $data ['category'];
            }
            if (!isset($data ['keyword']) || empty($data ['keyword'])) {
                $keyword = null;
            } else {
                $keyword = $data ['keyword'];
            }
            $auctions = SC_functions::get_auction_list($status, $category, $start, $keyword);
            return json_encode($auctions);
        }

        static function delivery($data)
        {
            $auction_id = $data ['id'];
            $result = SC_functions::deliverGoods($auction_id);
            return json_encode($result);
        }

        static function confirm_receiving($data)
        {
            $auction_id = $data ['id'];
            $result = SC_functions::confirmReceiving($auction_id);
            return json_encode($result);
        }
    }
}