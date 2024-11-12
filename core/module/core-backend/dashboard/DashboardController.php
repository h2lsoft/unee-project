<?php

namespace Plugin\Core_Backend;

use Model\User;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends \Core\Controller {

	/**
	 * @route /@backend/ {name: "backend-dashboard"}
	 */
	public function index(): \Core\Response|string|Response
	{
		$data = [];
		
		$sql_added = "";
		
		# superadmin exception
		if(\Core\Session::get('auth.xcore_group_id') != 1)
		{
			$sql_added = <<<STR
 AND
				        (
				            (xcore_plugin_id IN(SELECT id FROM xcore_plugin WHERE deleted='no' AND type IN('core'))) or
				            
				            (
				                (xcore_plugin_id IN(SELECT id FROM xcore_plugin WHERE deleted='no' AND active ='yes' AND type IN('normal', 'url'))) AND
				                xcore_plugin_id IN(SELECT xcore_plugin_id FROM xcore_group_right WHERE deleted='no' AND xcore_group_id=:xcore_group_id)
				            )
				        )
STR;

		}
		
		
		$sql = "SELECT
                        *
				FROM
				        xcore_widget
				WHERE
				        deleted ='no' AND
				        active = 'yes'
				        # group id in comment :xcore_group_id
						{$sql_added}
				ORDER BY
				        position";
		
		$widgets = DB()->query($sql, [':xcore_group_id' => \Core\Session::get('auth.xcore_group_id')])->fetchAll();
		
		$widgets_parsed = [];
		foreach($widgets as $widget)
		{
			$path = '\\Plugin\\'.ltrim(trim($widget['method']), '\\');
			$path = str_erase('()', $path);
			
			$attrs = "";
			if($widget['autorefresh_seconds'])
				$attrs = " hx-get=\"?ajaxer=1&ajaxer-action=get-widget&widget-id={$widget['id']}\" hx-trigger=\"every {$widget['autorefresh_seconds']}s\" ";
			
			$widget_render = call_user_func($path);
			
			// hook ajax request
			if(get('ajaxer') == 1 && get('ajaxer-action') == 'get-widget' && get('widget-id') == $widget['id'])
			{
				return new Response($widget_render);
			}
			
			
			$render = "<div id=\"dashboard-widget-{$widget['id']}\" class=\"widget-wrapper\" {$attrs}>{$widget_render}</div>\n";
			
			
			$widgets_parsed[] = $render;
		}
		
		
		$data['widgets'] = $widgets_parsed;


		
		
		return View('index', $data);
	}
	
	public function widgetBookmarkRender(): string
	{
		$content = View('widget-user-bookmarks', [], false);
		return $content;
	}

	/**
	 * @route /@backend/last-connected/
	 */
	public function widgetUserLastConnectedRender(): string
	{
		$fields = "*";
		$fields .= ", (SELECT name FROM xcore_group WHERE id = xcore_group_id) AS group_name";
		
		
		$last_connected = User::all("last_connection_date != 0", [], $fields, "", "last_connection_date DESC", 10);
		
		
		$parsed = [];
		foreach($last_connected as $rec)
		{
			if(empty($rec['avatar']))
			{
				$rec['avatar'] = get_absolute_path(\Core\Config::get('dir/avatar')."/0.png");
			}
			else
			{
				$ext = file_get_extension($rec['avatar']);
				$rec['avatar'] = str_replace("/{$rec['id']}.{$ext}", "/{$rec['id']}_thumb.{$ext}", $rec['avatar']);
			}
			
			$parsed[] = $rec;
			
		}
		
		$data = [];
		$data['last_connected'] = $parsed;
		
		
		$content = View('widget-last-connected', $data, false);
		return $content;
	}
	
	
	
	
}