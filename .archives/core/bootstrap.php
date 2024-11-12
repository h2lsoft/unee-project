<?php

define('APP_PATH', dirname(__DIR__));
define('APP_PACKAGE', !getenv('APP_PACKAGE') ? 'default' : getenv('APP_PACKAGE'));
define('APP_DB_PACKAGE', !getenv('APP_DB_PACKAGE') ? 'default' : getenv('APP_DB_PACKAGE'));
define('APP_MAIL_PACKAGE', !getenv('APP_MAIL_PACKAGE') ? 'default' : getenv('APP_MAIL_PACKAGE'));
define('APP_CLI_MODE', (strtolower(php_sapi_name()) == 'cli'));
define('APP_ENV', !getenv('APP_ENV') ? 'production' : getenv('APP_ENV'));



// utils
const CR = "\n";
const TAB = "\t";
const CHARLIST_LATIN = 'âàäéèêëôöüùÿçïî';
const CHARLIST_DASHES = '-_';
const CHARLIST_SPACE = ' ';
const CHARLIST_LATIN_DASHES = CHARLIST_LATIN.CHARLIST_DASHES;
const CHARLIST_LATIN_DASHES_SPACE = CHARLIST_LATIN.CHARLIST_DASHES.CHARLIST_SPACE;
const CHARLIST_LATIN_DASHES_SPACE_QUOTE = CHARLIST_LATIN.CHARLIST_DASHES.CHARLIST_SPACE."'";

// load config *********************************************************************************************************

// default config
$config = require APP_PATH."/config/default/app.php";

// load default other than production env
if(APP_ENV != 'production' && file_exists(APP_PATH."/config/default/app_".APP_ENV.".php"))
{
	$tmp = require APP_PATH."/config/default/app_".APP_ENV.".php";
	$config = array_merge($config, $tmp);
}

$config_files = [];
$config_files = array_merge($config_files, $config['config_files']);

// load package
if(APP_PACKAGE != 'default')
{
	if(file_exists(APP_PATH."/config/".APP_PACKAGE."/app.php"))
		$config_files[] = APP_PATH."/config/".APP_PACKAGE."/app.php";

	if(APP_ENV != 'production' && file_exists(APP_PATH."/config/".APP_PACKAGE."/app_".APP_ENV.".php"))
		$config_files[] = APP_PATH."/config/".APP_PACKAGE."/app_".APP_ENV.".php";
}

// load custom config file
$config_files = array_merge($config_files, $config['config_files']);
$config_files = array_unique($config_files);

foreach($config_files as $config_file)
{
	$tmp = require $config_file;
	$config = array_merge($config, $tmp);
}

// load php ini
foreach($config['php'] as $php_directive => $php_value)
{
	if($php_value != '')
	{
		ini_set($php_directive, $php_value);
	}
}

// core autoloader *****************************************************************************************************
spl_autoload_register(function($class){
	
	$path = explode('\\', $class);
	
	// Core
	if($path[0] == 'Core')
	{
		$class2 = str_replace('\\', '/', $class);
		$class2 = str_erase('Core/', $class2);
		$class_path = APP_PATH."/core/lib/class/{$class2}.php";
		
		if(file_exists($class_path))
		{
			include_once($class_path);
			return true;
		}
	}
	
	// Plugin
	if($path[0] == 'Plugin')
	{
		$package = strtolower($path[1]);
		$package = str_replace('_', '-', $package);
		$controller_dir = strtolower(str_erase('Controller', $path[2]));
		$controller_dir = str_replace('_', '-', $controller_dir);
		$controller = $path[2];
		$class_path = APP_PATH."/module/{$package}/{$controller_dir}/{$controller}.php";
		$core_class_path = APP_PATH."/core/module/{$package}/{$controller_dir}/{$controller}.php";
		
		if(file_exists($class_path))
		{
			include_once($class_path);
			return true;
		}

		if(file_exists($core_class_path))
		{
			include_once($core_class_path);
			return true;
		}
	}
	
	// Model
	if($path[0] == 'Model')
	{
		$package = strtolower($path[1]);
		$class_path = APP_PATH . "/model/{$path[1]}.php";
		$core_class_path = APP_PATH . "/core/model/{$path[1]}.php";
		
		if(file_exists($class_path))
		{
			include_once($class_path);
			return true;
		}

		if(file_exists($core_class_path))
		{
			include_once($core_class_path);
			return true;
		}
	}
	
	// Component
	if($path[0] == 'Component')
	{
		$package = strtolower($path[1]);
		$package = str_replace('_', '-', $package);
		$file = $path[1];
		
		$core_class_path = APP_PATH."/core/component/{$package}/{$file}.php";
		if(file_exists($core_class_path))
		{
			include_once($core_class_path);
			return true;
		}
	}

	// Command
	if($path[0] == 'Command')
	{
		$package = strtolower($path[1]);
		$package = str_replace('_', '-', $package);
		$class_path = APP_PATH . "/command/{$package}/{$path[2]}.php";
		$core_class_path = APP_PATH . "/core/command/{$package}/{$path[2]}.php";


		if(file_exists($class_path))
		{
			include_once($class_path);
			return true;
		}

		if(file_exists($core_class_path))
		{
			include_once($core_class_path);
			return true;
		}
	}


});






