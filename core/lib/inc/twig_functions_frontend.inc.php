<?php

// xZone ***************************************************************************************************************
$function = new \Twig\TwigFunction('xZone', function(string $name, string $language=''):string{

	$zone_id = explode(' #', $name);
	$zone_id = (int)end($zone_id);

	$ul = \Model\Zone::getHtml($zone_id, $language);

	return $ul;
}, ['is_safe' => ['html']]);
$this->twig->addFunction($function);


// xUrl ****************************************************************************************************************
$function = new \Twig\TwigFunction('xUrl', function(int $page_id):string{

	$page = \Model\Page::findById($page_id);
	return $page['url'];

}, ['is_safe' => ['html']]);
$this->twig->addFunction($function);

// xBreadcrumb *********************************************************************************************************
$function = new \Twig\TwigFunction('xBreadcrumb', function(int $page_id, string $home_url="/"):string{

	$breadcrumb = [];

	$page = \Model\Page::findById($page_id, 'id, language, xcore_page_id, name, url');
	$breadcrumb[] = $page;
	$page_language = $page['language'];

	while($page['xcore_page_id'] != false)
	{
		$page = \Model\Page::findById($page['xcore_page_id'], 'id, language, xcore_page_id, name, url, is_homepage');

		if($page['is_homepage'] == 'no')
		{
			$breadcrumb[] = $page;
			$page_id = $page['xcore_page_id'];
		}
		else
		{
			break;
		}

	}

	$home_label = "<i18n>Home</i18n>";

	$breadcrumb[] = ['id' => false, 'url' => $home_url, 'xcore_page_id' => 0, 'name' => $home_label];
	$breadcrumb = array_reverse($breadcrumb);


	$str = '<ul class="unee-breadcrumb">'.CR;
	foreach($breadcrumb as $b)
	{
		$str .= "<li><a href=\"{$b['url']}\">{$b['name']}</a></li>\n";
	}
	$str .= '</ul>'.CR;


	return $str;

}, ['is_safe' => ['html']]);
$this->twig->addFunction($function);

// xPlugin *************************************************************************************************************
$function = new \Twig\TwigFunction('xPlugin', function($controller_method, array $parameters=[]):string{

	$controller_method_parsed = "\\Plugin\\Frontend\\".explode('::', ucfirst($controller_method))[0];

	$class = "{$controller_method_parsed}Controller";
	$method = explode('::', $controller_method)[1];

	// check core_
	if (!class_exists($class)) {
		$class = str_replace('\\Plugin\\Frontend\\', '\\Plugin\\Core_Frontend\\', $class);
	}

	if (!class_exists($class)) {
		throw new \Exception("Frontend plugin `{$class}` not found");
	}

	$controller_instance = new $class();
	if (!method_exists($controller_instance, $method)) {
		throw new \Exception("Method `{$method}` not found in class `{$class}`");
	}

	return call_user_func_array([$controller_instance, $method], $parameters);


}, ['is_safe' => ['html']]);
$this->twig->addFunction($function);