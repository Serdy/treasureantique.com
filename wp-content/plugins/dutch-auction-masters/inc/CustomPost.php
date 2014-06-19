<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */
if (!class_exists('SC_CustomPost')) {
    class SC_CustomPost
    {
        const TEXT_DOMAIN = "dam-auction-masters";
        const DAM_AUCTION = "dam_auction";
        public $customPostTypes;
        public $auctions;
        private $damFields;

        public function __construct($configItems)
        {
            $this->damFields = array(
                'picture' => __('Picture', self::TEXT_DOMAIN),
                'begin' => __('Begin', self::TEXT_DOMAIN),
                'end' => __('End', self::TEXT_DOMAIN),
                'winner' => __('Winner', self::TEXT_DOMAIN),
                'last_bids' => __('Last bids', self::TEXT_DOMAIN),
                'hits' => __('Hits', self::TEXT_DOMAIN),
                'status' => __('Status', self::TEXT_DOMAIN)
            );

            $this->getCustomTypes($configItems);
            foreach ($configItems as $config) {
                extract($config);
                if (isset($name) && isset($args)) {
                    $fields = isset($fields) ? $fields : null;
                    register_post_type($name, $args);
                    new SC_MetaBox($name, __("Auction Data", DutchAuctionMasters::TEXT_DOMAIN), $fields, "dam");
                }
            }

            add_filter("views_edit-dam_auction", array($this, "damAuctionTabs"), 10, 1);
            add_filter('manage_dam_auction_posts_columns', array($this, 'damColumnsHeaders'), 10, 2);
            add_action('manage_dam_auction_posts_custom_column', array($this, 'customAuctionColumns'), 10, 2);
            add_action('pre_get_posts', array($this, 'filterCustomPost'), 10, 1);
            add_action('save_post', array($this, 'saveCustomPostType'), 10, 1);
            add_filter('the_posts', array($this, 'getCustomPosts'));
            add_action('delete_post', array($this, 'deleteCustomPost'));
            add_action('wp_trash_post', array($this, 'trashCustomPost'));
            add_action('untrash_post', array($this, 'untrashCustomPost'));
            add_action('admin_notices', array($this, 'displayWarningMessage'));
        }


        public function displayWarningMessage()
        {
            if (isset($_GET['post'])) {
                $id = $_GET['post'];
                $post = get_post($id);
                if ($post && $post->post_type == self::DAM_AUCTION && $post->post_status == "publish") {
                    $message = __("The auction has started, continue editing that will lose bids and winner information", self::TEXT_DOMAIN);
                    echo "<div class='wrap'><div class='error'><p>$message</p></div></div>";
                }
            }
        }

        public function trashCustomPost($postId)
        {
            SC_DataProvider::updateAuction(array("status" => 20), array("post_id" => $postId));
        }

        public function untrashCustomPost($postId)
        {
            SC_DataProvider::updateAuction(array("status" => 10), array("post_id" => $postId));
        }

        public function deleteCustomPost($postId)
        {
            SC_DataProvider::deleteAuction(array("post_id" => $postId));
        }

        public function getCustomPosts($posts, $query = false)
        {
            if (!is_admin())
                return $posts;
            $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : "";

            if ('dam_auction' == $post_type && count($posts) > 0) {
                $postIds = array();
                foreach ($posts as $post) {
                    $postIds[] = $post->ID;
                }
                $this->auctions = SC_DataProvider::getAuctionByPostIds($postIds);
            }
            return $posts;
        }

        public function damColumnsHeaders($columns)
        {
            $result = $this->insertArrayIndex($columns, $this->damFields, 2);
            return $result;
        }

        public function customAuctionColumns($column, $post_id)
        {
            if (array_key_exists($column, $this->damFields)) {
                echo $this->getValue($post_id, $column);
            }
        }

        public function saveCustomPostType($post_id)
        {
            $post = get_post($post_id);
            if ($post->post_type == self::DAM_AUCTION) {
                $default = array(
                    'id' => 0,
                    'post_id' => $post_id,
                    'title' => $post->post_title,
                    'amount' => '',
                    'auction_type' => 0,
                    'picture' => '',
                    'begin' => null,
                    'end' => null,
                    'original_price' => null,
                    'reserve_price' => 0,
                    'shipping_fee' => 0,
                    'start_price' => null,
                    'step_price' => null,
                    'description' => '',
                    'status' => $this->mappingStatus($post->post_status),
                    'properties' => ''
                );
                $item = shortcode_atts($default, $_POST);
                if (isset($_POST['SC_description']))
                    $item["description"] = $_POST['SC_description'];

                $categories = wp_get_post_categories($post_id);
                if (is_array($categories))
                    $item['category'] = "," . implode(',', $categories) . ",";

                $tags = wp_get_post_tags($post_id, array('fields' => 'names'));

                if (is_array($tags))
                    $item['tags'] = "," . implode(',', $tags) . ",";

                if (!empty($item['properties'])) {
                    $item['properties'] = stripslashes($item['properties']);
                    $props = json_decode($item['properties']);
                    foreach ($props as $prop) {
                        SC_functions::add_property_key($prop->key);
                    }
                }

                if ($post->post_status == "publish") {
                    $item['notified'] = 0;
                    $item['winner'] = '';
                    $item['owner_id'] = $post->post_author;
                    $item['deal_state'] = '';
                    $item['last_bids'] = '';
                    $item['bid_price'] = 0;
                    SC_DataProvider::deleteBidsByPostId($post_id);
                }
                $this->saveAuction($post_id, $item);
                SC_DataProvider::updatePost(array("post_content" => '[single-auction post_id="' . $post_id . '"]'), array("ID" => $post_id));
            }
        }

        public function filterCustomPost($wpQuery)
        {

            if (!is_admin()) {
                $query = $wpQuery->query;
                if (isset($query["cat"]) || isset($query['category_name'])) {
                    $wpQuery->set('post_type', 'any');
                } else {
                    return;
                }
            }

            global $current_user, $pagenow;
            get_currentuserinfo();

            if (!is_a($current_user, 'WP_User'))
                return;

            if ('edit.php' != $pagenow)
                return;

            if ('dam_auction' != $wpQuery->query['post_type'])
                return;

            $status = isset($_REQUEST['state']) ? $_REQUEST['state'] : "";

            if ($status) {
                $postIds = SC_DataProvider::getPostIdsByStatus($status);
                if (count($postIds) > 0)
                    $wpQuery->set('post__in', $postIds);
                else
                    $wpQuery->set('post__in', array(-1));
            }

            // If the user is not administrator, filter the post listing
            if (!current_user_can('delete_plugins'))
                $wpQuery->set('author', $current_user->ID);

        }

        public function damAuctionTabs($views)
        {
            ?>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-<?php echo SC_functions::is_active_auction_page("") ?>"
                   href="?post_type=dam_auction"> <?php _e("All", "dam-auction-masters"); ?></a>
                <a class="nav-tab nav-tab-<?php echo SC_functions::is_active_auction_page("upcoming") ?>"
                   href="?post_type=dam_auction&state=upcoming"> <?php _e("Upcoming", DutchAuctionMasters::TEXT_DOMAIN); ?></a>
                <a class="nav-tab nav-tab-<?php echo SC_functions::is_active_auction_page("running") ?>"
                   href="?post_type=dam_auction&state=running"> <?php _e("Running", DutchAuctionMasters::TEXT_DOMAIN); ?></a>
                <a class="nav-tab nav-tab-<?php echo SC_functions::is_active_auction_page("closed") ?>"
                   href="?post_type=dam_auction&state=closed"> <?php _e("Closed", DutchAuctionMasters::TEXT_DOMAIN); ?></a>
                <a class="nav-tab nav-tab-<?php echo SC_functions::is_active_auction_page("paid") ?>"
                   href="?post_type=dam_auction&state=paid"> <?php _e("Paid", DutchAuctionMasters::TEXT_DOMAIN); ?></a>
            </h2>
            <?php
            return $views;
        }

        private function insertArrayIndex($array, $newArray, $index)
        {
            $start = array_slice($array, 0, $index);
            $start = array_merge($start, $newArray);
            $end = array_slice($array, $index);
            return array_merge($start, $end);
        }

        private function getCustomTypes($configItems)
        {
            $this->customPostTypes = array();
            foreach ($configItems as $item) {
                $this->customPostTypes[] = $item['name'];
            }
        }

        private function saveAuction($post_id, $item)
        {
            $fieldRequires = array(
                'original_price' => true,
                //  'reserve_price' => true,
                'shipping_fee' => true,
                'start_price' => true,
                'step_price' => true,
                'begin' => true,
                'end' => true,
            );

            foreach ($fieldRequires as $key => $value) {
                if (empty($item[$key])) {
                    return false;
                }
            }

            $auction = SC_DataProvider::get_auction_by_post_id($post_id);

            if ($auction) {
                $item['id'] = $auction->id;
                SC_DataProvider::updateAuction($item, array("id" => $auction->id));
            } else {
                SC_DataProvider::insertAuction($item);
            }
            return true;
        }

        private function getValue($postId, $columnName)
        {
            if (is_array($this->auctions)) {
                foreach ($this->auctions as $auction) {
                    if ($auction->post_id == $postId) {
                        if ($columnName == "picture") {
                            return SC_functions::showMainPicture($columnName, $auction);
                        } else if ($columnName == "status") {
                            return SC_functions::displayStatus((array)$auction);
                        } else if ($columnName == "last_bids") {
                            return SC_functions::displayLastBids((array)$auction);
                        } else if ($columnName == "winner") {
                            return $this->displayWinner((array)$auction);
                        } else {
                            return $auction->$columnName;
                        }
                    }
                }
            }
            return null;
        }

        private function displayWinner($auction)
        {
            $result = "";
            if ($auction ['deal_state'] == DealState::PAID && $auction ['winner']) {
                $address = SC_DataProvider::get_address_by_auction_id($auction['id']);
                if ($address) {
                    $result = "Name: <strong>" . $address->contact_name . "</strong><br/>";
                    $result .= "Phone: <strong>" . $address->phone . "</strong><br/>";
                    $result .= "Gender: <strong>" . $address->gender . "</strong><br/>";
                    $result .= "Postal code: <strong>" . $address->postal_code . "</strong><br/>";
                    $result .= "House number: <strong>" . $address->house_number . "</strong><br/>";
                    $result .= "Street: <strong>" . $address->house_number . "</strong><br/>";
                    $result .= "City: <strong>" . $address->house_number . "</strong><br/>";
                    $result .= "Province: <strong>" . $address->province . "</strong><br/>";
                    $result .= "Country: <strong>" . $address->country . "</strong><br/>";
                }
                return $result;
            } else if ($auction ['winner']) {
                $winner = json_decode($auction ['winner']);
                $result = __("<strong>Email</strong>: ", "dam-auction-masters");
                $result .= $winner->email . "<br/>";
                $result .= __("<strong>Name</strong>: ", "dam-auction-masters");

                if ($winner->id) {
                    $result .= "<a href='./user-edit.php?user_id= $winner->id'>" . $winner->name . "</a> <br/>";
                } else {
                    $result .= $winner->name . "<br/>";
                }
            }
            return $result;
        }

        private function mappingStatus($status)
        {
            $mapping = array(
                "auto-draft" => 0,
                "draft" => 0,
                "publish" => 10,
                "trash" => 20,
                "pending" => 30,
            );

            if (array_key_exists($status, $mapping)) {
                return $mapping[$status];
            }
            return 0;
        }
    }
}