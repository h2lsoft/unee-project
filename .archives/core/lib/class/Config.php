<?php

namespace Core;

class Config
{
	static public array $cached = [];
	
	/**
	 * get config value from path
	 *
	 * @param $path
	 * @param $default value if not found
	 * @param $cache retrieve value from cache
	 *
	 * @return false|mixed
	 */
	public static function get(string $path='', mixed $default=false, bool $cache=true):mixed
	{
		global $config;
		
		if(empty($path))return $config;
		if(isset(self::$cached[$path]) && $cache)return self::$cached[$path];
		
		$cur = $config;
		$tmp = explode('/', trim($path, '/'));
		foreach($tmp as $key)
		{
			if(!isset($cur[$key]))
			{
				$value = $default;
				break;
			}
			
			$cur = &$cur[$key];
			$value = $cur;
		}
		
		self::$cached[$path] = $value;
		return $value;
	}
	
	
	
}