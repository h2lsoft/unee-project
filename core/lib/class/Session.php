<?php

namespace Core;

class Session
{
	
	/**
	 * start sesssion
	 * @param string $name
	 * @param array  $options
	 *
	 * @return void
	 */
	public static function start(string $name, array $options):void
	{
		session_name($name);
		session_start($options);
	}
	
	/**
	 * get session_id
	 * @return false|string
	 */
	public static function getID():false|string
	{
		return session_id();
	}
	
	/**
	 * get session value from key
	 * @param string     $key
	 * @param mixed|null $default
	 *
	 * @return mixed
	 */
	public static function get(string $key='', mixed $default=null):mixed
	{
		if(empty($key))return $_SESSION;
		return $_SESSION[$key] ?? $default;
	}
	
	/**
	 * set a session value
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public static function set(string $name, mixed $value):void
	{
		$_SESSION[$name] = $value;
	}
	
	/**
	 * Destroy session key
	 * @param string $key session to destroy, you can use joker to delete generic key, ex: SHOPPING_CART_*
	 */
	public static function destroy(string $key=""):void
	{
		// joker key found
		if(!empty($key) && $key[-1] == '*')
		{
			$index = substr($key, 0, -1);
			foreach($_SESSION as $s_key => $s_val)
			{
				if(preg_match("#^{$index}#", $s_key))
				{
					unset($_SESSION[$s_key]);
				}
			}
		}
		
		if(isset($key))
		{
			unset($_SESSION[$key]);
		}
		else
		{
			$_SESSION = [];
		}
	}
	
	
	

}