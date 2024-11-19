<?php

return [
		
		'website_name' => "", # if empty take app/name
		'theme' => "simple",

		'langs' => [
			['en', 'English'],
			// ['fr', 'Français'],
		],

		// PAGE MANAGER ################################################################################################
		'page' => [
					'url_pattern' => "/{locale}/{slug}-{id}.html", // {locale} will be replaced by language, {id} by id and {slug} by slugify page name

					'widget' => [
									'limit' => 20,
									'where' => "",
									'where_parameters' => [],
					],

					'type' => [
									'page', 'article', 'dynamic', 'url', 'url external'
					],


					'status' => [
									['label' => 'Draft', 'value' => 'draft', 'icon' => "bi bi-pencil"],
									['label' => 'Waiting validation', 'value' => 'waiting validation'],
									['label' => 'Scheduled', 'value' => 'scheduled'],
									['label' => 'Published', 'value' => 'published'],
								],

					'featured' => [
										'upload_max_size' => "1MB",
										'width' => 1200,
										'height' => 628,
										'allowed_extension' => ['jpg', 'jpeg','png','webp'],
										'allowed_mimes' => [],
										'thumbnail_default_url' =>  "/public/".APP_PACKAGE."/upload/page/0.png",
										'thumbnail_width' => 320, // will be resized x2
										'thumbnail_height' => 180, // will be resized x2
					],

					'plugins' => [

									[
										'name' => 'block',
										'pattern' => '/(<x-block[^>]*data-name="([^"]*)"[^>]*>)(<\/x-block>)/',
										'controller' =>  \Plugin\Core_Frontend\BlockController::class,
										'method' => 'render',
										'reload_pattern' => true,
									],

									[
										'name' => 'slider',
										'pattern' => '/(<x-slider[^>]*data-name="([^"]*)"[^>]*>)(<\/x-slider>)/',
										'controller' =>  \Plugin\Core_Frontend\SliderController::class,
										'method' => 'render',
										'reload_pattern' => false,
									],

									[
										'name' => 'gallery',
										'pattern' => '/(<x-gallery[^>]*data-name="([^"]*)"[^>]*>)(<\/x-gallery>)/',
										'controller' =>  \Plugin\Core_Frontend\GalleryController::class,
										'method' => 'render',
										'reload_pattern' => false,
									],

									[
										'name' => 'thumbpage',
										'pattern' => '/(<x-thumbpage[^>]*data-parent-page="([^"]*)"[^>]*>)(<\/x-thumbpage>)/',
										'controller' =>  \Plugin\Core_Frontend\PageController::class,
										'method' => 'thumbnailsRender',
										'reload_pattern' => false,
									],
					]
		],

		// GALLERY ################################################################################################
		'gallery' => [

			'thumbnail' => [
				'default' => '0.png',
				'width' => 300, // will be resized x 2
				'height' => 200, // will be resized x 2
			],

		],

		// BLOG ################################################################################################
		'blog' => [

			'enabled' => true,
			'resume_nb_chars' => 300,
			'listing' => [
							'url' => '/news/',
							'search_page_url' => '/news/page/{page}/',
							'author_search_url' => '/news/author/{author}/',
							'author_search_page_url' => '/news/author/{author}/page/{page}/',

							'class' => '',
							'resume_class' => '',
							'image_class' => 'animation--img-scale',
			],

			'image' => [
							'max_size' => '600 ko',
			],

			'thumbnail' => [
								'default' => '0.png',
								'width' => 300, // will be resized x2
								'height' => 200, // will be resized x2
			],
			'date_format' => 'd F Y',
			'author' => 'login', // login or concat(firstname,'  ', lastname)
			'url' => "/article/{slug}_{id}.html", # put {locale}, {language}, {lang}, {id}, {slug}
			'page_container_id' => 0, // id for page article
			'show_tags' => true,

			'status' => [
				['label' => 'Draft', 'value' => 'draft', 'icon' => "bi bi-pencil", 'class' => "text-white bg-warning d-block", 'style' => "border-radius:5px"],
				['label' => 'Waiting validation', 'value' => 'waiting validation', 'icon' => "bi bi-exclamation-triangle-fill", 'class' => "text-white bg-success bg-opacity-50 d-block", 'style' => "border-radius:5px;"],
				['label' => 'Scheduled', 'value' => 'scheduled', 'icon' => "bi bi-clock", 'class' => "text-white bg-dark d-block", 'style' => "border-radius:5px"],
				['label' => 'Published', 'value' => 'published', 'icon' => "bi bi-eye", 'class' => "text-white bg-success d-block", 'style' => "border-radius:5px"],
			],

			'comments' => [
								'allow.author_update' => true,
								'allow.author_update_seconds' => 3*60, // minutes to update for author
								'tag_allowed' => ['<strong>', '<b>', '<em>', '<br>', '<img>', '<hr>', '<mark>'],
								'moderate' => true, // you have approve
			],

		],

		// ANALYTICS ###################################################################################################
		'analytics' => [
			'enabled' => true,
			'ips_excluded' => ['127.0.0.1'], // exclude ip from stats

			'domains_allowed_collect' => [

			],

			'user_agent_regex_excluded' => [
												'/bot|spider|crawl|scanner|axios/i'
											],

			// enter domain without www, every referer domain in list with no be cleared (only referer value)
			'referer_domains_cleared' => [

			]
		],


		// MAINTENANCE #################################################################################################
		'maintenance' => false,
		'maintenance.sentence' => "", # if empty display default, if http or slash => redirect
		'maintenance.ips_allowed' => [],
		'maintenance.ips_allowed_include_debug' => false,
		'maintenance.url_exclude' => [],

		// ASSETS ######################################################################################################
		'minify' => false,

		'assets' => [
			'version' => '1.0.0', // useful for minify
			'css' => [],
			'js_head' => [],
			'js_body' => [],
		],
];