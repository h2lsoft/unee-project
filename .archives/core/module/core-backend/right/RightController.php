<?php

namespace Plugin\Core_Backend;

use Symfony\Component\HttpFoundation\JsonResponse;

class RightController extends \Core\Controller {
	public string $table = 'xcore_group_right';

	/**
	 * @route /@backend/@module/    {name:"backend-right"}
	 */
	public function list()
	{
		
		$sql = "SELECT
                        xcore_menu.name AS menu_name,
						xcore_plugin.*
				FROM
						xcore_menu,
						xcore_plugin
				WHERE
						xcore_menu.deleted = 'no' and
						xcore_plugin.deleted = 'no' and
						xcore_plugin.xcore_menu_id = xcore_menu.id and
						xcore_plugin.type != 'core'
				ORDER BY
						xcore_menu.position,
						xcore_menu.name,
						xcore_plugin.position,
						xcore_plugin.name";
		
		$recs = DB()->query($sql)->fetchAll();
		
		$menus = [];
		foreach($recs as $rec)
		{
			$rec['actions'] = explode("\n", trim($rec['actions']));
			
			$tmp = [];
			foreach($rec['actions'] as $action)
			{
				$action = trim($action);
				if(empty($action))continue;
				
				$tmp[] = $action;
			}
			
			$rec['actions'] = $tmp;
			$menus[$rec['menu_name']][] = $rec;
		}
		
		// current rights
		$group_id = get('xcore_group_id');
		$rights = [];
		if($group_id)
		{
			$sql = "SELECT * FROM xcore_group_right WHERE deleted = 'no' and xcore_group_id = :group_id  ORDER BY xcore_plugin_id, action";
			$recs = DB()->query($sql, [':group_id' => $group_id])->fetchAll();
			foreach($recs as $rec)
			{
				$rights[] = "{$rec['xcore_plugin_id']}_{$rec['action']}";
			}
		}
		
		
		
		$data = [];
		$data['groups'] = \Model\Group::all("id != 1", [], "*", "", "priority, name");
		$data['menus'] = $menus;
		$data['rights'] = $rights;
		
		return View('index', $data);
	}

	/**
	 * @route /@backend/@module/   {method:"POST", name:"backend-right-exec"}
	 */
	public function exec()
	{
		$group_id = $this->validator->inputGet('xcore_group_id');
		$actions = $this->validator->inputGet('action');
		
		// delete all rights
		$sql = "DELETE FROM xcore_group_right WHERE xcore_group_id = :group_id";
		DB()->query($sql, [':group_id' => $group_id]);
		
		// insert right
		foreach($actions as $action)
		{
			$tmp = explode('_', $action);
			if(count($tmp) != 2)continue;
			
			$f = [];
			$f['xcore_group_id'] = $group_id;
			$f['xcore_plugin_id'] = $tmp[0];
			$f['action'] = $tmp[1];
			
			DB($this->table)->insert($f, \Core\Session::get('auth.login'));
		}
		
		
		
		return new JsonResponse($this->validator->result());
	}
	
	
}