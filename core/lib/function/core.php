<?php


/**
 * debug utils
 * @param      $var
 * @param bool $exit
 *
 * @return void
 */
function x(mixed $var, bool $exit=true):void
{
	!Kint::dump($var);
	if($exit)die();
}


/**
 * return global app
 * @param string $key
 *
 * @return void
 */
function App(string $key=''):mixed
{
	global $app;
	
	$v = (empty($key)) ? $app : $app[$key];
	return $v;
}

/**
 * return global db manager
 * @param string $table (optionnal init table)
 *
 * @return \Core\DB
 */
function DB(string $table="")
{
	global $app, $DB;

	if(!empty($table))
		$app->db->table($table);
	return $app->db;



}

/**
 * return plugin view
 *
 * @param string $template
 * @param array  $data
 * @param int    $status (if 0 direct render)
 * @param array  $headers
 *
 * @return \Symfony\Component\HttpFoundation\Response or string (if status == 0)
 */
function View(string $template='', array $data=[], int $status=200, array $headers=[]):\Core\Response|string
{
	global $app;
	
	$caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0]['file'];
	$path = dirname($caller);
	$plugin_folder_name = basename($path);
	
	if(empty($template))$template = '@plugin-content';
	if(!strpos($template, '.')) $template .= '.twig';
	
	
	// backend
	if($template[0] == '@' && file_exists(APP_PATH.'/core/theme/core-admin/'.$template))
	{
		$template = '/core/theme/core-admin/'.$template;
	}
	

	if($template[0] == '/')
	{
		$tpl_path = $template;
	}
	else
	{
		$tpl_path = "{$path}/view/{$template}";
	}
	
	// add variables
	$data['APP_TITLE'] = \Core\Config::get('name');
	$data['core_version'] = \Model\Live_Updater::$version;
	$data['app_name'] = \Core\Config::get('name');
	$data['locale'] = $app->locale;
	$data['config_css'] = \Core\Config::get('backend/assets/css');
	$data['config_js_head'] = \Core\Config::get('backend/assets/js_head');
	$data['config_js_body'] = \Core\Config::get('backend/assets/js_body');
	$tpl_path = str_erase(APP_PATH, $tpl_path);
	
	if($app->is_backend)
	{
		$data['plugin'] = $app->plugin;
	}




	$content = $app->twig->render($tpl_path, $data);
	
	// add @globals
	$cur_dir = get_absolute_path($path);
	
	
	$content = str_replace('@backend', \Core\Config::get('backend/dirname'), $content);
	$content = str_replace('@locale', App()->locale, $content);
	
	$content = str_replace('@assets_img', $cur_dir."/assets/img", $content);
	$content = str_replace('@assets_css', $cur_dir."/assets/css", $content);
	$content = str_replace('@assets_js', $cur_dir."/assets/js", $content);
	$content = str_replace('@assets', $cur_dir."/assets", $content);
	
	if(!$status)
		return $content;


	return new \Core\Response($content, $status, $headers);
}


/**
 * return protected value from content
 * @param $value
 * @return
 */
function XSSProtection($value)
{
	if(is_array($value))return $value;

	$params = \Core\Config::get('sanitizer');

	// sanitizer
	$allowed_tags = $params['allowed_html_tags'];
	$value = strip_tags($value, $allowed_tags);


	// convert protection
	$convert = $params['convert'];
	foreach($convert as $pattern => $replace)
	{
		$value = str_replace($pattern, $replace, $value);
	}

	return $value;
}


