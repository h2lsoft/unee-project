<?php

namespace Plugin\Core_Backend;

use Symfony\Component\HttpFoundation\JsonResponse;

class User_SearchController extends \Core\Controller {
	
	public string $table = 'xcore_user_search';
	public string $object_label = 'search';


	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->qSelectEmbed('xcore_plugin', "name", 'plugin');
		$datagrid->qSelectEmbed('xcore_plugin', "icon", 'icon');

		$datagrid->qSelect("(select name from xcore_group where id = default_xcore_group_id) as default_group");
		$datagrid->qSelect("(select name from xcore_group where id = share_xcore_group_id) as share_group");
		$datagrid->qSelect("(select login from xcore_user where id = xcore_user_id) as user");

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('xcore_plugin_id', 'plugin');
		$datagrid->searchAddSelectSql('xcore_user_id', 'user', 'xcore_user', 'login', " and xcore_group_id in(select id from xcore_group where deleted = 'no' and access_backend='yes')");
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('default');
		$datagrid->searchAddBoolean('share');


		// columns
		$datagrid->addColumn('id');
		$datagrid->addColumnHtml('plugin', '', true, 'min');
		$datagrid->addColumn('name', '', false, 'min');
		$datagrid->addColumnHtml('user', '', false, 'min');
		$datagrid->addColumnBoolean('default', '', false, 'min center', false);
		$datagrid->addColumnHtml('default_group', ' ', false, 'min');
		$datagrid->addColumnBoolean('share', '', false, 'min center', false);
		$datagrid->addColumnHtml('share_group', ' ', false, 'min');
		$datagrid->addColumnHtml('url', '', false, '');

		// hookData
		$datagrid->hookData(function($row){

			$row['plugin'] = "<i class='{$row['icon']}'></i> {$row['plugin']}";
			$row['url'] = "<a href='{$row['url']}' target='_blank'>{$row['url']}</a>";

			if(empty($row['default_group']))$row['default_group'] = '<i18n>all groups</i18n>';
			if($row['default'] == 'no')$row['default_group'] = '-';

			if(empty($row['share_group']))$row['share_group'] = '<i18n>all groups</i18n>';
			if($row['share'] == 'no')$row['share_group'] = '-';

			return $row;
		});


		$data = [];
		$data['content'] = $datagrid->render();
		return View('@plugin-content', $data);
	}


	/**
	 * @route /@backend/@module/add/ {method:"GET|POST", controller:"add"}
	 * @route /@backend/@module/edit/{id}/ {method:"GET|PUT", controller:"edit"}
	 */
	public function getForm(int $id=0)
	{
		$form = new \Component\Form();
		$form->linkController($this, $id);


		$form->addSelectSql('xcore_user_id', 'user', true, '', [], 'id', 'login', 'xcore_user', " and xcore_group_id in(select id from xcore_group where deleted = 'no' and access_backend='yes')");

		$form->addSelectSql('xcore_plugin_id', 'plugin', true, '', [], 'id', "concat((select name from xcore_menu where id = xcore_menu_id),'> ',name)");
		$form->addText('name', '', true, ['class' => 'upper']);
		$form->addUrl('url', '', true);
		$form->addSwitch('default', 'default', true);
		$form->addSelectSql('default_xcore_group_id', 'group', false, 'All groups', [], 'id', 'name', 'xcore_group', " and access_backend='yes'");
		$form->addSwitch('share', 'share', true);
		$form->addSelectSql('share_xcore_group_id', 'group', false, 'All groups', [], 'id', 'name', 'xcore_group', " and access_backend='yes'");



		// validation
		if($form->isSubmitted())
		{

			// valid
			if($form->isValid())
			{
				$added = [];
				$form->save([], $added);
			}

			return $form->json();
		}

		return $form->render();
	}





	/**
	 * @route /@backend/user-search/{plugin_id}/
	 */
	public function menuList(int $plugin_id):JsonResponse
	{
		$group_id = \Core\Session::get('auth.xcore_group_id');

		$where = "xcore_plugin_id = :plugin_id and ";
		$where .= " (xcore_user_id = :user_id or (share = 'yes' and share_xcore_group_id in(0,$group_id))) ";

		$binds = [];
		$binds[':plugin_id'] = $plugin_id;
		$binds[':user_id'] = \Model\User::getUID();

		$searches = \Model\User_Search::all($where, $binds, '*', '', 'name');
		
		$result = $this->validator->result();
		$result['searches'] = $searches;
		
		
		return new JsonResponse($result);
	}

	/**
	 * @route /@backend/user-search/create/  {method: "POST"}
	 */
	public function menuCreate():JsonResponse
	{
		$this->validator->input('xcore_plugin_id')->required()->integer(false);
		$this->validator->input('url')->required();
		$this->validator->input('name')->required();
		
		if($this->validator->success())
		{
			$f = [];
			$f['xcore_user_id'] = \Model\User::getUID();
			$f['xcore_plugin_id'] = $this->validator->inputGet('xcore_plugin_id');
			$f['name'] = ucfirst($this->validator->inputGet('name'));
			$f['url'] = $this->validator->inputGet('url');
			
			
			\Model\User_Search::insert($f);
		}
		
		$result = $this->validator->result();
		return new JsonResponse($result);
	}

	/**
	 * @route /@backend/user-search/delete/{id}/    {method: "DELETE"}
	 */
	public function menuDelete(int $id):JsonResponse
	{
		\Model\User_Search::delete(["id = :id and xcore_user_id = :user_id", [':id' => $id, ':user_id' => \Model\User::getUID()]]);
		$result = $this->validator->result();
		return new JsonResponse($result);
	}
	
}