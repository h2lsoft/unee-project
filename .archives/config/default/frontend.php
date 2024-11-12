<?php

return [
		
		'website_name' => "", # if empty take app/name
		'theme' => "simplee",

		'langs' => [
			['en', 'English'],
			// ['fr', 'Français'],
		],

		// PAGE MANAGER ################################################################################################
		'page' => [

					'status' => [
									['label' => 'Draft', 'value' => 'draft'],
									['label' => 'Waiting validation', 'value' => 'waiting validation'],
									['label' => 'Scheduled', 'value' => 'scheduled'],
									['label' => 'Published', 'value' => 'published'],
								],



		],
		
		// MAINTENANCE #################################################################################################
		'maintenance' => false,
		'maintenance.sentence' => "", # if empty display default, if http or slash => redirect
		'maintenance.ips_allowed' => [],
		'maintenance.ips_allowed_include_debug' => false,
		'maintenance.url_exclude' => [],
		
		
		// ASSETS ##########################################################################################################
		'assets' => [
			'version' => '1.0.0', // useful for minify
			'minify' => true,
			'css' => [],
			'js_head' => [],
			'js_body' => [],
		],
];