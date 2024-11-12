<?php
/**
 * Add debugbar
 *
 * @global $dispatcher
 */

use Symfony\Component\HttpKernel\Event\ResponseEvent;

$dispatcher->addListener('kernel.response', function(ResponseEvent $event) {
	
	$app = App();
	$request = $app->request;
	
	$response = $event->getResponse();
	
	if(APP_CLI_MODE || !\Core\Config::get('debug') || !\Core\Config::get('debug_bar') || $request->getRequestFormat() != 'html' || get('ajaxer') == 1 || !$app->debugbar)return;
	$c = $response->getContent();
	
	// debugbar
	if(!empty($c) && $c[0] == '{')return;
	
	
	$debugbarRenderer = $app->debugbar->getJavascriptRenderer();
	
	$bar = "\n".$debugbarRenderer->renderHead();
	$bar .= "\n".$debugbarRenderer->render();
	
	// check if there is a body envlop
	if(!str_contains($c, '</body>'))
		$c .= $bar;
	else
		$c = str_replace('</body>', "\n\n{$bar}\n\n</body>", $c);
	
	$response->setContent($c);
});
