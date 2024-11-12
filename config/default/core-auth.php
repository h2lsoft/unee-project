<?php

return [

	'key_name' => 'UID',	 # key to store session auth data
	'logon_session_columns_added' => "",
	'logon_sleep_seconds' => 2, # seconds to prevent attack
	'password_reset_token_expiration_hours' => 12,

	'security' => [

		'email' => [
			'max_length' => 255,
		],

		'login' => [
			'min_length' => 6,
			'max_length' => 30,
			'regex' => "/^[a-z\d\-_]+$/i",
			'regex_error_message' => "`login`: please fill only alphanumeric and [OTHER_CHARS]",
			'regex_error_message_added' => ['OTHER_CHARS' => " '-', '_'"],
		],

		'password' => [
			'algo' => PASSWORD_DEFAULT, # choose php password encrypt view php manual => http://php.net/manual/en/function.password-hash.php
			'algo_options' => ['cost' => 12],
			'min_length' => 6,
			'max_length' => 20,
			'regex' => "/^[a-z\d\-_#%@!(){}]+$/i",
			'regex_error_message' => "`password`: please fill only an alphanumeric string, accepted [OTHER_CHARS]",
			'regex_error_message_added' => ['OTHER_CHARS' => "'-', '_', '#','%', '@', '!', '(', ')', '{', '}'"],
		]

	],

	'backend' => [
		'key' => 'email', # choose between email or login
	],

	'frontend' => [
		'key' => 'email', # choose between Email or Login
	],

	'mail' => [
		'password_reset' => "/core/module/auth/view/@password-reset.twig"
	]

];