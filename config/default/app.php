<?php

return [

		// generics
		'name' => "Unee local",
		'url' => 'http://unee.local', // you can use $_SERVER['HTTP_HOST']
		'admin_emails' => [],
		'noreply_email' => "Website|noreply@unee.local", # email or label|email if you want to have a labelled email
		'timezone' => "", # empty = default timezone
	
		// updater **********************************************************************************************
		'live_updater' => [
			'url' => "https://unee.app/@version",
			'canal' => "stable", // stable, beta
		],
		
		// DEBUG ***********************************************************************************************
		'debug' => true,
		'debug_bar' => true,
		'debug_for_ip_auto' => true, # auto-turn debug
		'debug_for_ip' => [
								'127.0.0.1'
		],
		
		// PHP **************************************************************************************************
		'php' => [
			
			// override php options (empty = default)
			'display_errors' => true,
			'error_reporting' => '',
			'memory_limit' => '',
			'max_execution_time' => '',
			'max_file_uploads' => '5',
			'upload_max_filesize' => '20M',
			'post_max_size' => '100M',
		],
	
		// FORMAT ************************************************************************************************
		'format' => [
			'date' => 'd/m/Y', # see php date documentation, can be override by user in profile
			'datetime' => 'd/m/Y H:i',
			'time' => 'H:i',
			'number_separator' => '.',
			'number_thousand_separator' => ' ',
			'number_decimal' => 2,
		],
	
		// SESSION ***************************************************************************************
		'session' => [
			'name' => 'PHPSESSID', # name of session default PHPSESSID
			'options' => [
				'use_strict_mode' => true,
				'gc_maxlifetime' => (int)(60*60*24*90), // 90 days
				'cookie_path' => '/',
				'cookie_domain' => '',
				'cookie_secure' => false,
				'cookie_lifetime' => (int)(60*60*24*90), // 90 days
				'cookie_httponly' => true,
				'cookie_samesite' => 'Lax',
				'cache_limiter' => 'nocache',
				'cache_expire' => 0,
			],
		],
	
		// DB - use env APP_DB_PACKAGE_NAME to change package ****************************************************
		'db' => [
			
			'package' => [
				
				'default' => [
					'driver'    => 'mysql',
					'host'      => 'localhost',
					'port'      => 3306,
					'username'  => 'root',
					'password'  => '',
					'database'  => 'unee',
					'pdo_options' => [], # PDO options see php pdo documentation
					
					// 'init_queries' => ["SET SESSION sql_mode='ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"],
					'init_queries' => []
				],
			],
			
			'schema' => [
				'default_charset' => 'utf8mb4', // for example  `utf8mb4`
				'default_collate' => 'utf8mb4_general_ci', // for example  `utf8mb4_general_ci`
				'default_engine' => 'MYISAM', // default engine
				'default_auto_increment' => 1, // default auto-increment
			],
		],
	
		// MAIL ********************************************************************************************************
		'mailer' => [
						'package' => [
							
							'default' => [
								'smtp' => false, // false for localhost
								'host' => 'localhost',
								'port' => 25,
								'auth' => false,
								'auth.username' => '',
								'auth.password' => '',
								'security' => '', // \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS (see https://github.com/PHPMailer/PHPMailer)
								'template' => [
												'path' => '/theme/@mail/base.twig',
												'vars' => [
															'logo' => '/public/default/assets/img/logo.png',
															'background' => '#F4F4F4',
												]
								]
							]
						],
		],
	
		// CLI *********************************************************************************************************
		'cli' => [
			
			'database' => [
							// you can override database info for cli (empty = current package)
							'host'      => '',
							'port'      => '',
							'username'  => '',
							'password'  => '',
			],
			
			'commands' => [], # enter custom command path here
		],
	
		// CRON *********************************************************************************************************
		'cron' => [
			
			'report_email_from' => "cron@unee.local",
			'error_admin_email' => '',
			
			'shutdown.mail_alert' => true,
			'shutdown_mail_ignore_error_list' => [],
			'shutdown_mail_error_types' => [], // empty for all or use option: ERROR, WARNING, PARSE, NOTICE, ESTRICT, DEPRECATED
		],
	
		// Config ******************************************************************************************************
		'config_files' => [
						 # add more configuration files	to override
		],
	
		// Twig ************************************************************************************************************
		'twig' => [
			'options' => [
				'cache' => false, // ex: APP_PATH.'/public/'.APP_PACKAGE_NAME.'/.cache/twig/' see twig documentation for option (https://twig.symfony.com/doc/3.x/api.html)
				'strict_variables' => true,
				'debug' => true,
			],
			
			// add your custom extension by include file, example: /core/inc/twig_functions.inc.php
			'functions_file' => []
			
		],

	
		// ROUTES ******************************************************************************************************
		'routes' => [
						# add your custom routes file path here, ex: APP_PATH.'/route/my_routes.php';
						APP_PATH.'/route/frontend.inc.php'
		],
	
		// DIR *********************************************************************************************************
		'dir' => [

			'tmp' => APP_PATH.'/.tmp/'.APP_PACKAGE,
			'avatar' => APP_PATH."/public/".APP_PACKAGE."/upload/avatar",
			'page' => APP_PATH."/public/".APP_PACKAGE."/upload/page",
			'article' => APP_PATH."/public/".APP_PACKAGE."/upload/article",
			'slider_card' => APP_PATH."/public/".APP_PACKAGE."/upload/slider-card",
			'gallery_card' => APP_PATH."/public/".APP_PACKAGE."/upload/gallery-card",
			'page_template' => APP_PATH."/public/".APP_PACKAGE."/upload/page-template",
		],

		// SANITIZER ***************************************************************************************************
		'sanitizer' => [
							'allowed_html_tags' => [],
							'allowed_symbols' => ['@', "'"],
							'convert' => [
											'"' => '`',
											'{{ ' => "[[ ",
											' }}' => " ]]",
											'<x' => "<sanitizer-x",
							],
		],

		// MODULE ******************************************************************************************************
		'backend' => require __DIR__.'/backend.php',
		'frontend' => require __DIR__.'/frontend.php',
		'auth' => require __DIR__.'/core-auth.php',
		'firewalls' => require __DIR__.'/firewall.php',
		'events' => require __DIR__.'/events.php',
		'file_manager' => require __DIR__.'/file-manager.php',


		

];