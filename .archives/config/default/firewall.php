<?php

return [

	'auth-backend' => [
		'pattern' => '#^/@backend/#i',
		'guard_page' => '/@backend/login/',
		'error_message' => "Error: you must be logged",
		'rule' => '\Model\User::isLogon',
		'rule_params' => [],
		'exceptions' => [
			'/@backend/login/',
			'/@backend/password/',
			'/@backend/password-reset/',
		]
	],

	'auth-frontend' => [
		'pattern' => '#^/@lang/u/#i',
		'guard_page' => '/@lang/u/login/',
		'error_message' => "Error: you must be logged",
		'rule' => '\Model\User::isLogon',
		'rule_params' => [],
		'exceptions' => [
			'/@lang/u/login/',
			'/@lang/u/password/',
			'/@lang/u/password-reset/',
		]
	],


];
