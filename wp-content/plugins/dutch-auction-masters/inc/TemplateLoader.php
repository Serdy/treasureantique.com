<?php
/**
 * Created by Second Company BV.
 * User: Viking
 */
if(!class_exists('SC_TemplateLoader'))
{
    class SC_TemplateLoader {

        function __construct($path = NULL)
        {
            if(!isset($path))
            {
                $path = dirname(__FILE__).'/views/';
            }
            $this->path = $path;
        }

        function set($name, $value)
        {
            $this->vars[$name] = $value;
        }

        function process($file)
        {
            @extract($this->vars);
            ob_start();
            include($this->path . $file);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
    }
}