<?php

$function = new \Twig\TwigFunction('xcore_backend', function(string $type):string{
	
	$data = [];
	
	// menu
	if($type == 'menu')
	{
		$data['menu'] = \Model\User::getMenu();
		$resp = View('/core/theme/core-admin/_menu.twig', $data);
		return $resp->getContent();
	}
	
	// breadcrumbs
	if($type == 'breadcrumbs')
	{
		$data['menu'] = App()->plugin['breadcrumbs'];
		$resp = View('/core/theme/core-admin/_breadcrumbs.twig', $data);
		return $resp->getContent();
	}
	
	
}, ['is_safe' => ['html']]);
$this->twig->addFunction($function);