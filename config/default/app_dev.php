<?php
/**
 * You can override default app config with APP_ENV
 */
return [
	
	'debug' => true,
	'debug_bar' => true,

	'php' => [
		'display_errors' => true,
		'error_reporting' => E_ALL,
	],
	
	'session' => [
		'options' => [
			'cookie_secure' => false,
		],
	],
	
	'twig' => [
		'options' => [
			'cache' => false,
			'debug' => true,
		]
	]
	

];