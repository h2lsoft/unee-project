<?php

namespace Core;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Router {
	
	static array $routes = [];
	static string $route_prefix = "";
	
	/**
	 * Set prefix for route
	 * @param string $prefix
	 *
	 * @return void
	 */
	public static function prefix(string $prefix)
	{
		self::$route_prefix = rtrim($prefix, '/');
	}
	
	/**
	 * Clear route prefix
	 * @return void
	 */
	public static function prefixClear()
	{
		self::$route_prefix = "";
	}
	
	/**
	 * any verbs for path
	 *
	 * @param string $path
	 * @param string $callback
	 * @param string $name
	 *
	 * @return void
	 */
	public static function any(string $path, string|callable $callback, string $name=''):void
	{
		if(!empty(self::$route_prefix))$path = self::$route_prefix.$path;
		self::$routes[] = ['path' => $path, 'callback' => $callback, 'verbs' => ['ANY'], 'name' => $name];
	}
	
	/**
	 * match verbs for path
	 *
	 * @param array  $verbs GET, POST, PUT, PATCH, DELETE, OPTIONS
	 * @param string $path
	 * @param string $callback
	 * @param string $name
	 *
	 * @return void
	 */
	public static function match(array $verbs, string $path, string|callable $callback, string $name=''):void
	{
		if(!empty(self::$route_prefix))$path = self::$route_prefix.$path;


		foreach($verbs as $verb)
		{
			if(empty($name))
			{
				$name2 = slugify($path)."__{$verb}";
			}
			else
			{
				$name2 = $name;
			}

			self::$routes[] = [
									'path' => $path,
									'callback' => $callback,
									'verbs' => [$verb],
									'name' => $name2
								];

			if(empty($name))$name2 = '';
		}

	}
	
	
	/**
	 * get verb for path
	 *
	 * @param string          $path
	 * @param string|callable $callback
	 * @param string $name
	 *
	 * @return void
	 */
	public static function get(string $path, string|callable $callback, string $name=''):void
	{
		if(!empty(self::$route_prefix))$path = self::$route_prefix.$path;
		if(empty($name))$name = slugify($path).'__GET';
		
		self::$routes[] = [
							'path' => $path,
							'callback' => $callback,
							'verbs' => ['GET'],
							'name' => $name
						];
	}
	
	/**
	 * post verb for path
	 *
	 * @param string          $path
	 * @param string|callable $callback
	 * @param string $name
	 *
	 * @return void
	 */
	public static function post(string $path, string|callable $callback, string $name=''):void
	{
		if(!empty(self::$route_prefix))$path = self::$route_prefix.$path;
		if(empty($name))$name = slugify($path).'__POST';
		self::$routes[] = [
			'path' => $path,
			'callback' => $callback,
			'verbs' => ['POST'],
			'name' => $name,
			
		];
	}
	
	/**
	 * put verb for path
	 *
	 * @param string          $path
	 * @param string|callable $callback
	 * @param string $name
	 *
	 * @return void
	 */
	public static function put(string $path, string|callable $callback, string $name=''):void
	{
		if(!empty(self::$route_prefix))$path = self::$route_prefix.$path;
		if(empty($name))$name = slugify($path).'__PUT';
		self::$routes[] = [
			'path' => $path,
			'callback' => $callback,
			'verbs' => ['PUT'],
			'name' => $name,
		];
	}
	
	/**
	 * delete verb for path
	 *
	 * @param string          $path
	 * @param string|callable $callback
	 * @param string $name
	 *
	 * @return void
	 */
	public static function delete(string $path, string|callable $callback, string $name=''):void
	{
		if(!empty(self::$route_prefix))$path = self::$route_prefix.$path;
		if(empty($name))$name = slugify($path).'__DELETE';
		self::$routes[] = [
			'path' => $path,
			'callback' => $callback,
			'verbs' => ['DELETE'],
			'name' => $name,
		];
	}

	/**
	 * compile all routes
	 *
	 * @return void
	 */
	public static function compile():RouteCollection
	{
		global $routes;
		
		$backend_path = \Core\Config::get('backend/dirname');
		
		foreach(self::$routes as $r)
		{
			if(
				$r['verbs'] == ['GET'] ||
				$r['verbs'] == ['POST'] ||
				$r['verbs'] == ['PATCH'] ||
				$r['verbs'] == ['PUT'] ||
				$r['verbs'] == ['DELETE'] ||
				$r['verbs'] == ['ANY']
			)
			{

				$r['path'] = str_replace('/@backend/', "/{$backend_path}/", $r['path']);

				if($r['verbs'] == ['ANY'])$r['verbs'] = [];
				
				$requirements = [];
				$requirements['id'] = '\d+';
				$requirements['page'] = '\d+';

				$routes->add(
								$r['name'],
								new Route($r['path'], ['_controller' => $r['callback']], $requirements, [], null, [], $r['verbs'])
							);
			}
		}
		
		return $routes;
		
	}
	
	
	
	
	
}

