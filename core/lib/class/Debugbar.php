<?php

namespace Core;

class Debugbar
{
	
	/**
	 * add message to debugbar
	 * @param mixed $message
	 * @param string $type (info, warning, error)
	 *
	 * @return void
	 */
	public static function addMessage(mixed $message, string $type='info'):void
	{
		global $app;
		
		// if(!is_object($app->debugbar))return;
		$app->debugbar["messages"]->addMessage($message, $type);
		
	}
	
	/**
	 * add message info in debugbar
	 * @param string $message
	 *
	 * @return void
	 */
	public static function info(mixed $message):void{self::addMessage($message);}
	
	/**
	 * add message warning in debugbar
	 * @param mixed $message
	 *
	 * @return void
	 */
	public static function warning(mixed $message):void{self::addMessage($message, 'warning');}
	
	/**
	 * add message error in debugbar
	 * @param mixed $message
	 *
	 * @return void
	 */
	public static function error(mixed $message):void{self::addMessage($message, 'error');}
	
	
	/**
	 * start measure operation
	 * @param string $operation_name
	 * @param string $message
	 *
	 * @return void
	 */
	public static function startMeasure(string $operation_name, string $message):void
	{
		if(!is_object(App()->debugbar))return;
		App()->debugbar['time']->startMeasure($operation_name, $message);
	}
	
	/**
	 * stop measure operation
	 * @param string $operation_name
	 * @param string $message
	 *
	 * @return void
	 */
	public static function stopMeasure(string $operation_name):void
	{
		if(!is_object(App()->debugbar))return;
		App()->debugbar['time']->startMeasure($operation_name);
	}
	
	/**
	 * measure operation
	 * @param string $message
	 * @param callable $function
	 *
	 * @return void
	 */
	public static function measure(string $message, callable $callable):void
	{
		if(!is_object(App()->debugbar))return;
		App()->debugbar['time']->measure($message, $callable);
	}
	
	/**
	 * enable debug
	 * @return void
	 */
	/*public static function enable():void
	{
		// if(!is_object(App()->debugbar))return;
		// App()->debugbar->enable();
	}*/
	
	/**
	 * disable debug
	 * @return void
	 */
	public static function disable():void
	{
		// if(!is_object(App()->debugbar))return;
		// App()->debugbar->disable();
		App()->debugbar = false;
	}
	
	
}