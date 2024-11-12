<?php

namespace Core;

class Log
{
	
	
	public static function write(string $action, string $message="", array $values=[], int $record_id=0, string $level='info', string $plugin='', string $author="", string $application='backend'):void
	{
		if(App()->is_backend)
		{
			if(empty($plugin) && isset(App()->plugin['route_prefix_name'])) $plugin = App()->plugin['route_prefix_name'];
			if(empty($action) && isset(App()->plugin['current_action'])) $action = App()->plugin['current_action'];
		}
		
		if(empty($author))$author = \Core\Session::get('auth.login');
		
		$f = [];
		$f['application'] = $application;
		$f['date'] = now();
		$f['level'] = $level;
		$f['plugin'] = $plugin;
		$f['action'] = $action;
		$f['message'] = $message;
		$f['values'] = json_encode($values, JSON_PRETTY_PRINT);
		$f['record_id'] = $record_id;
		$f['ip'] = getVisitorIp();
		$f['author'] = $author;
		
		
		DB('xcore_log')->insert($f, \Core\Session::get('auth.login'));
	}
	
	
	
}