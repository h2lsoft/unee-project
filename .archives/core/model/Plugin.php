<?php

namespace Model;

class Plugin extends \Core\Entity
{
	public static string $table = 'xcore_plugin';
	
	/**
	 * extract plugin name from uri
	 * @param string $uri
	 *
	 * @return string (@error if not found)
	 */
	public static function extractName(string $uri=''):string
	{
		if(empty($uri))$uri = $_SERVER['REQUEST_URI'];
		
		$plugin_name = @explode("/", str_erase("/".\Core\Config::get('backend/dirname')."/", $uri))[0];
		
		if(empty($plugin_name) && $uri != "/".\Core\Config::get('backend/dirname')."/")
			$plugin_name = '@error';
		
		
		return $plugin_name;
	}




	
}