<?php

// path
$function = new \Twig\TwigFunction('path', function(string $route_name):string{
	global $routes;
	$url = $routes->get($route_name)->getPath();
	return $url;
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// abs_path
$function = new \Twig\TwigFunction('abs_path', function(string $path):string{
	return get_absolute_path($path);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// url_get_query_string
$function = new \Twig\TwigFunction('url_get_query_string', function():string{

	$qs = urldecode(http_build_query($_GET));
	$qs = preg_replace("#search\[([0-9])\]#", 'search[]', $qs);

	return $qs;
}, ['is_safe' => ['html']]);
$this->twig->addFunction($function);

// absolute_path
$function = new \Twig\TwigFunction('absolute_path', function(string $path):string{
	return get_absolute_path($path);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// session
$function = new \Twig\TwigFunction('session', function(string $key, bool|string $default=false):string{
	return \Core\Session::get($key, $default);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// config
$function = new \Twig\TwigFunction('config', function(string $key, bool|string $default=false):mixed{
	return \Core\Config::get($key, $default);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// globals
$function = new \Twig\TwigFunction('globals', function(string $var, bool|string $default=false):mixed{
	return \Core\Globals::get($var, $default);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// ucfirst
$function = new \Twig\TwigFunction('ucfirst', function(string $str):mixed{
	return ucfirst($str);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// get
$function = new \Twig\TwigFunction('get', function(string $key, mixed $default='', array $list=[]):mixed{
	return get($key, $default, $list);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// post
$function = new \Twig\TwigFunction('post', function(string $key, mixed $default='', array $list=[]):mixed{
	return post($key, $default, $list);
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// json
$function = new \Twig\TwigFunction('json', function(mixed $value):string{
	
	$encoded = json_encode($value, true);
	return $encoded;
	
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);

// slugify
$function = new \Twig\TwigFunction('slugify', function(string $str):string{
	
	$str = slugify($str);
	return $str;
	
}, ['is_safe' => ['text']]);
$this->twig->addFunction($function);
