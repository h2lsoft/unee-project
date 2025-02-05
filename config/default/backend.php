<?php

return [
		
		'dirname' => 'backend', # url for backend
		'logo' => "/core/theme/core-admin/assets/img/logo.png", # logo path

		'langs' => [
			['en', 'English'],
			['fr', 'Français'],
		],

		'header_show_website' => true,

		
		// MENU ********************************************************************************************************
		'menu' => [
					'nav' => [
								// add an icon in navbar example help or support
								// ['name' => 'help', 'tooltip' => 'Help', 'icon' => 'bi bi-life-preserver', 'action' => '#', 'attributes' => ['target' => '_blank']],
					],
			
					'user' => [
								// add an icon in navbar example help or support
								// ['name' => 'help', 'text' => 'Help', 'icon' => 'bi bi-life-preserver', 'action' => '#', 'attributes' => ['target' => '_blank']],
					],
		],
		
		
		// ASSETS ADDED ************************************************************************************************
		'assets' => [
			'css' => [],
			'js_head' => [],
			'js_body' => [
			],
		],

];