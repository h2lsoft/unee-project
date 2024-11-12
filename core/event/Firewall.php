<?php
/**
 * Apply firewall rules
 *
 * @global $dispatcher
 */

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

$dispatcher->addListener('kernel.request', function(RequestEvent $event) {

	if(APP_CLI_MODE)return;

	$request = $event->getRequest();


	$backend_dirname = \Core\Config::get('backend/dirname');
	$firewalls = \Core\Config::get('firewalls');
	$cur_uri = strtok($request->getRequestUri(), '?');

	foreach($firewalls as $name => $rule)
	{
		$rule['pattern'] = str_replace('/@backend/', "/{$backend_dirname}/", $rule['pattern']);
		$rule['guard_page'] = str_replace('/@backend/', "/{$backend_dirname}/", $rule['guard_page']);

		for($i=0; $i < count($rule['exceptions']); $i++)
			$rule['exceptions'][$i] = str_replace('/@backend/', "/{$backend_dirname}/", $rule['exceptions'][$i]);


		if(preg_match($rule['pattern'], $cur_uri) && !in_array($cur_uri, $rule['exceptions']))
		{

			// evaluation of method
			if(!call_user_func_array($rule['rule'], $rule['rule_params']))
			{
				if($request->getRequestFormat() == 'json' || empty($rule['guard_page']))
				{
					// throw new Symfony\Component\HttpKernel\Exception\HttpException(403, $rule['error_message']);
					$response = new Response($rule['error_message'], 403, [], true);
					die($response->send());
				}
				else
				{
					http_redirect($rule['guard_page']);
				}

				break;
			}
		}
	}

});
