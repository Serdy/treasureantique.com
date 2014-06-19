<?php
/**
 * Created by PhpStorm.
 * User: Ftx
 * Date: 13-11-4
 * Time: 上午12:14
 */

if(!class_exists('SC_ShortCode'))
{
    class SC_ShortCode {
        public function __construct($config)
        {
            extract($config);
            $this->defaultAtts = $defaultAtts;
            $this->widgetName = $widgetName;
            add_shortcode( $tag , array( $this ,'shortCodeHandle'));
        }

        public function shortCodeHandle($atts)
        {
            $instance = shortcode_atts( $this->defaultAtts, $atts );
            ob_start();
            the_widget( $this->widgetName , $instance );
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }
}