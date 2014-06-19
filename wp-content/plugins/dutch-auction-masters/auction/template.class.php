<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

class template_loader
{
 
	function __construct($path = NULL)
	{
		if(!isset($path))
		{
			$path = dirname(__FILE__).'/../views/';
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