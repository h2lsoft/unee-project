<?php

namespace Model;

class Cron extends \Core\Entity
{
	public static string $table = 'xcore_crontask';
	public static array $current;
	public static string $current_status;
	public static string $last_message;
	public static array $stack;
	
	
	public static function shutdownHandler()
	{
		if(!isset(\Model\Cron::$current['id']))return;
		
		if(is_null(error_get_last()))return;
		$error_last = error_get_last();
		
		// word ignore detection
		$ignored = \Core\Config::get('cron/shutdown_mail_ignore_error_list');
		foreach($ignored as $word)
		{
			if(stripos($error_last['message'], $word) !== FALSE)
				return;
		}
		
		// format error type
		if($error_last['type'] == 1)$error_last['type'] = 'ERROR';
		elseif($error_last['type'] == 2)$error_last['type'] = 'WARNING';
		elseif($error_last['type'] == 4)$error_last['type'] = 'PARSE';
		elseif($error_last['type'] == 8)$error_last['type'] = 'NOTICE';
		elseif($error_last['type'] == 2048)$error_last['type'] = 'ESTRICT';
		elseif($error_last['type'] == 8192)$error_last['type'] = 'DEPRECATED';
		
		
		$cron_name = \Model\Cron::$current['name']." #".\Model\Cron::$current['id'];
		$error_last['cron'] = $error_last;
		
		\Model\Cron::update(['status' => 'error', 'last_error_message' => $error_last['message'], 'locked' => 'no'], \Model\Cron::$current['id']);
		
		\Core\Log::write('execution', 'php', $error_last, \Model\Cron::$current['id'], 'error', $cron_name, 'system', 'cron');
		
		// email error php
		if(\Core\Config::get('cron/shutdown.mail_alert') && (!count(\Core\Config::get('cron/shutdown_mail_error_types')) || in_array($error_last['type'], \Core\Config::get('cron/shutdown_mail_error_types'))))
		{
			$mail_from = \Core\Config::get('cron/report_email_from');
			$mail_to = \Core\Config::get('cron/error_admin_email');
			
			$body = "<b>{$cron_name}<b><br>";
			$body .= "<b>{$error_last['type']} in line {$error_last['line']} in file `{$error_last['file']}`:</b><br><br>";
			$body .= "<pre>{$error_last['message']}<pre>";
			
			\Core\Mailer::send($mail_from, $mail_to, "[CRON]{$cron_name} error detected", $body);
		}
		
	}
	
	
	public static function stack(string $msg)
	{
		static::$stack[] = $msg;
	}
	
	/**
	 * get stack in html for email
	 * @return string
	 */
	public static function stackGetAll():string
	{
		return join("<br>\n", static::$stack);
	}
	
	public static function write(string $message, string $type='info', bool $stacked=true)
	{
		self::$last_message = $message;
		
		$type = strtoupper($type);
		if($type == 'LOG') echo "\033[37m";
		if($type == 'ERROR') echo "\033[31m";
		if($type == 'WARNING') echo "\033[33m";
		if($type == 'SUCCESS') echo "\033[32m";
		if($type == 'INFO') echo "\033[0m";
		
		echo $message."\n";
		
		if($stacked)
		{
			$color = 'black';
			if($type == 'LOG') $color = '#ccc';
			if($type == 'ERROR') $color = 'red';
			if($type == 'WARNING') $color = '#ffcc00';
			if($type == 'SUCCESS') $color = 'green';
			
			$stamp = now();
			static::$stack[] = "<span style=\"color:{$color};\">[{$stamp}] ".trim($message)."</span>";
		}
		
	}
	
	
	public static function getParameterValue(string $param_name):bool|string
	{
		if(!isset(static::$current['script_parameters_parsed']))
		{
			$params = explode("\n", static::$current['script_parameters']);
			
			$all = [];
			foreach($params as $param)
			{
				$params = trim($param);
				if(empty($param))continue;
				
				list($p_name, $p_value) = explode('=', $param, 2);
				$all[trim($p_name)] = trim($p_value);
			}
			
			static::$current['script_parameters_parsed'] = $all;
		}
		
		
		$value = (!isset(static::$current['script_parameters_parsed'][$param_name])) ? false : static::$current['script_parameters_parsed'][$param_name];
		 
		 
		 return $value;
	}
	
}