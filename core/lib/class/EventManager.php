<?php

namespace Core;

class EventManager
{
	public static $events = [];
	public static $STOP_PROPAGATION = false;
	
	const PRIORITY_VERY_LOW = -1024;
	const PRIORITY_LOW = -512;
	const PRIORITY_MEDIUM = 0;
	const PRIORITY_HIGH = 512;
	const PRIORITY_VERY_HIGH = 1024;

	/**
	 * declare tretment
	 *
	 * @param string $event_name
	 * @param callable $func
	 * @param int $priority
	 * @return void
	 */
	public static function on(string $event_name, callable $func, int $priority=EventManager::PRIORITY_MEDIUM):void
	{
		static::$events[$event_name][] = ['callable' => $func, 'priority' => $priority];
	}

	/**
	 * emit event
	 *
	 * @param string $event_name
	 * @param array $params
	 * @return void
	 */
	public static function emit(string $event_name, array $params=[]):void
	{
		// not found
		if(!isset(static::$events[$event_name]))
			return;
		
		// sort event by priority
		$events = static::$events[$event_name];
		usort($events, function ($events1, $events2) {
			return $events2['priority'] <=> $events1['priority'];
		});
		
		foreach($events as $event)
		{

			call_user_func($event['callable'], $params);
			
			// call
			
			
			if(static::$STOP_PROPAGATION)break;
		}
		
		
	}


	/**
	 * stop propagation
	 *
	 * @return void
	 */
	public static function stopPropagation():void{
		static::$STOP_PROPAGATION = true;
	}

}