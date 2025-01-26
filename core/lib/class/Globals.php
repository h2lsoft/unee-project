<?php

namespace Core;

class Globals
{
	static public array $cached = [];

	/**
	 * get global value from variable name
	 *
	 * @param string $name
	 * @param mixed $default value if not found
	 * @param bool $register_value (default=true) register default value
	 *
	 * @return string|bool
	 */
	public static function get(string $name, mixed $default=false, bool $register_value=true):bool|string
	{
		global $app;

		// register
		if($register_value && !isset($app->globals[$name]))
		{
			$app->globals[$name] = $default;
			\Core\Globals::set($name, $default);
		}

		$value = (!isset($app->globals[$name])) ? $default : $app->globals[$name];
		return $value;
	}

	/**
	 * create or update a globals
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return void
	 */
	public static function set(string $name, string $value, string $package=''):void
	{
		if(empty($package))$package = APP_PACKAGE;

		\Core\Entity::$table = 'xcore_globals';


		$f = [];
		$f['package'] = $package;
		$f['name'] = $name;
		$f['value'] = $value;

		$where = [];
		$where[] = "package = :package and name = :name";
		$where[] = [':package' => $package, ':name' => $name];


		\Core\Entity::replace($f, $where);

	}

	/**
	 * @param string $name
	 * @param string $package if empty current package
	 *
	 * @return void
	 */
	public static function destroy(string $name, string $package=''):void
	{
		global $app;

		if(empty($package))$package = APP_PACKAGE;

		\Core\Entity::$table = 'xcore_globals';

		$where = [];
		$where[] = "package = :package and name = :name";
		$where[] = [':package' => $package, ':name' => $name];

		\Core\Entity::delete($where);

		if(isset($app->globals[$name]))
			unset($app->globals[$name]);
	}



}