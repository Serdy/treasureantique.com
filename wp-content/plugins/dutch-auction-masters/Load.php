<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */


require_once 'auction/constants.class.php';
require_once 'inc/Functions.php';
require_once 'inc/ShortCode.php';
require_once 'inc/FileInclude.php';
require_once 'inc/CustomPost.php';
require_once 'inc/FieldControls.php';
require_once 'inc/DefaultViews.php';
require_once 'inc/DefaultTemplate.php';
require_once 'inc/MetaBox.php';
require_once 'inc/Menu.php';
require_once 'inc/AjaxHandle.php';
require_once 'inc/DataProvider.php';
require_once 'inc/Settings.php';
require_once 'auction/widget.auction.list.php';
require_once 'auction/widget.auction.single.php';
require_once 'auction/template.class.php';
require_once 'auction/cron.job.class.php';

if (! class_exists ( 'WP_List_Table' )) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

$items = scandir( dirname(__FILE__)."/modules");
foreach($items as $item )
    if(strpos($item,'.php')>0)
        require_once 'modules/'.$item;