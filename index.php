<?php
/**
 * @global $config
 */

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

include 'vendor/autoload.php';
include "core/lib/function/core.php";
include "core/lib/function/string.php";
include "core/lib/function/array.php";
include "core/lib/function/http.php";
include "core/bootstrap.php";

// for POST, PUT, PATCH, DELETE
Request::enableHttpMethodParameterOverride();

// debug ***************************************************************************************************************
if(!$config['debug'] && $config['debug_for_ip_auto'] && in_array(getVisitorIp(), $config['debug_for_ip']))
	$config['debug'] = true;

if($config['debug'] && isset($_GET['debug']) && $_GET['debug'] == 0)
	$config['debug'] = false;

if(!$config['debug'] && isset($_GET['debug']) && $_GET['debug'] == 1 && in_array(getVisitorIp(), $config['debug_for_ip']))
	$config['debug'] = true;



if(!APP_CLI_MODE)
{
	if($config['debug'])
	{
		Debug::enable();
	}
	else
	{
		\Symfony\Component\ErrorHandler\ErrorHandler::register();
		\Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer::setTemplate(APP_PATH."/core/theme/core/@error.html.php");
	}
}

// command line possibility url
if(APP_CLI_MODE)
{
	if(count($argv) == 2)
	{
		$method = 'GET';
		list($_, $path) = $argv;
	}
	else
		list($_, $method, $path) = $argv;
	
	$request = Request::create($path, $method);
}
else
{
	$request = Request::createFromGlobals();
	\Core\Session::start(\Core\Config::get('session/name'), \Core\Config::get('session/options'));
}

// override format listener
if($request->getRequestFormat() != $request->request->get('_format', 'html'))
	$request->setRequestFormat($request->request->get('_format', 'html'));

// forced JSON
if(isset($_GET['_format']) && in_array($_GET['_format'], ['json', 'text', 'html']) && $_GET['_format'] != $request->request->get('_format', 'html'))
{
	$request->setRequestFormat($_GET['_format']);
}


// routes **************************************************************************************************************
$routes = new RouteCollection();



// customer routes include
$custom_routes = \Core\Config::get('routes', []);
foreach($custom_routes as $route_file)
	include $route_file;

// get route from controller
$all_controllers = \Core\Controller::getAll();

foreach($all_controllers as $c_controller)
{
	$info = \Core\Controller::getInfo($c_controller);
	$paths = $info['paths'];
	$package = $info['package'];
	$module = $info['module'];
	$controller = $info['controller'];
	$class_path = $info['class_path'];

	$class = new ReflectionClass($class_path);
	$methods = $class->getMethods();
	foreach($methods as $method)
	{
		$doclet = $method->getDocComment();
		if($doclet)
		{
			preg_match_all("/@route(.*)$/m", $doclet, $matches);
			if(count($matches[1]))
			{
				foreach($matches[1] as $route)
				{
					$route = trim(str_replace("\t", "    ", $route));

					$tmp = explode(' {', $route, 2);
					$verbs = ['GET'];
					$route_name = "";
					$uri = trim($tmp[0]);
					$uri = str_replace('/@module/', "/{$paths[1]}/", $uri);

					$method_name = $method->getName();

					// parameters
					if(count($tmp) == 2)
					{
						$params = rtrim($tmp[1], "}");
						$params = explode(", ", $params);

						foreach($params as $param)
						{
							$line = explode(':', $param);
							if($line[0] == 'name')
							{
								$route_name = trim(str_erase(['"', "'"], $line[1]));
								$route_name = trim($route_name);
							}
							elseif($line[0] == 'method')
							{
								$verbs = explode("|", trim(str_erase(['"', "'"], $line[1])));
							}
							elseif($line[0] == 'controller')
							{
								$method_name = trim(str_erase(['"', "'"], $line[1]));
							}
							else
							{
								throw new Exception("`{$line[0]}` not correct for route `{$uri}` in `{$c_controller}`");
							}
						}
					}


					\Core\Router::match($verbs, $uri, $class->getName()."::{$method_name}", $route_name);

				}
			}
		}
	}
}

include 'core/route/backend.php';
include 'core/route/frontend.php';

$all_routes = \Core\Router::compile();


$matcher = new UrlMatcher($all_routes, new RequestContext());

// dispatcher **********************************************************************************************************
$dispatcher = new EventDispatcher();
include "core/event/Firewall.php";
include "core/event/Debugbar.php";
include "core/event/I18n.php";
include "core/event/Controller.php";

$custom_events = \Core\Config::get('events', []);
foreach($custom_events as $event_file)
	include $event_file;

$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

// create controller and argument resolvers
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();


// instantiate the kernel
$app = new \Core\Kernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver, $request);
$response = $app->handle($request);
$response->send();



$app->terminate($request, $response);


