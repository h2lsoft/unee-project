<?php

/**
 * redirect with php header
 *
 * @param string $url
 * @param int    $code
 *
 * @return void
 */
function http_redirect(string $url, int $code=302):void
{
	header("Status: 302 Moved Temporarily", false, 302);
	header("Location: {$url}");
	exit();
}

/**
 * get visitor IP
 * @return string
 */
function getVisitorIp():string
{
	if(APP_CLI_MODE)return "127.0.0.1";
	
	
	// Get real visitor IP behind CloudFlare network
	if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
	{
		$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
	}
	
	$client  = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote  = @$_SERVER['REMOTE_ADDR'];
	
	
	if(filter_var($client, FILTER_VALIDATE_IP))
	{
		$ip = $client;
	}
	elseif(filter_var($forward, FILTER_VALIDATE_IP))
	{
		$ip = $forward;
	}
	else
	{
		$ip = $remote;
	}
	
	return $ip;
}

/**
 * return query parameter
 *
 * @param string $key
 * @param mixed  $default
 * @param array  $list force value in list
 *
 * @return mixed
 */
function get(string $key, mixed $default='', array $list=[]):mixed
{
	$v = (isset($_GET[$key])) ? $_GET[$key] : $default;
	
	if(count($list) && !in_array($v, $list))
		$v = $list[0];
	
	return $v;
}

/**
 * return post parameter
 *
 * @param string $key
 * @param mixed  $default
 * @param array  $list force value in list
 *
 * @return mixed
 */
function post(string $key, mixed $default='', array $list=[]):mixed
{
	$v = (isset($_POST[$key])) ? $_POST[$key] : $default;
	if(count($list) && !in_array($v, $list))
		$v = $list[0];
	return $v;
}





/**
 * replace in url get parameters
 *
 * @param array  $params
 * @param string $url
 *
 * @return string
 */
function http_query_replace(array $params, string $url):string
{
	$new_url = parse_url($url);
	if(!isset($new_url['query']))$new_url['query'] = "";
	parse_str($new_url['query'], $q);
	
	foreach($params as $k => $v)
	{
		if(is_null($v))
			unset($q[$k]);
		else
			$q[$k] = $v;
	}
	
	$new_url = $new_url['path'] . '?' . http_build_query($q);
	
	$new_url = str_replace('%5B0%5D', '[]', $new_url);
	$new_url = str_replace('%7C', '|', $new_url);
	
	return $new_url;
}

/**
 * get current request GET, POST, PUT, DELETE
 * @param string $type
 *
 * @return bool
 */
function requestIs(string $type)
{
	$type = strtoupper($type);
	
	return (App()->request->getMethod() == $type);
}