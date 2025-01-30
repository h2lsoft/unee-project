<?php
/**
 * Core i18n replace execution
 *
 * @global $dispatcher
 */

use Symfony\Component\HttpKernel\Event\ResponseEvent;

$dispatcher->addListener('kernel.response', function(ResponseEvent $event) {
	
	
	if(App()->request->getRequestFormat() != 'html')return;
	
	$response = $event->getResponse();
	$c = $response->getContent();

	if(($locale = App()->locale) != 'en' && file_exists("core/i18n/{$locale}.php"))
	{
		$match = preg_match_all('#<i18n>(.*)</i18n>#U', $c, $tab);
		if($match)
		{
			$_I18N = include "core/i18n/{$locale}.php";
			for($i=0; $i < count($tab[0]); $i++)
			{
				$item = str_erase(['<i18n>', '</i18n>'], $tab[1][$i]);
				$item_markup = $tab[0][$i];
				$item_markup = str_replace('<i18n><i18n>', '<i18n>', $item_markup);
				
				$c = str_replace($item_markup, (isset($_I18N[$item])) ? $_I18N[$item] : $item, $c);
			}
		}
		
	}
	
	
	$c = str_erase(['<i18n>', '</i18n>'], $c);
	$response->setContent($c);
});

