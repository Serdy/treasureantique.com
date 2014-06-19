<?php
/**
 * Created by PhpStorm.
 * User: Ftx
 * Date: 13-11-4
 * Time: 上午12:14
 */

if(!class_exists('SC_FileInclude'))
{
    class SC_FileInclude {

        private $uri;

        public function __construct($uri)
        {
            if(!empty($uri))
                $this->uri = plugin_dir_path(__FILE__).'../'.$uri;
        }

        public function includeFile()
        {
            if($this->uri && file_exists($this->uri))
                include($this->uri);
        }
    }
}