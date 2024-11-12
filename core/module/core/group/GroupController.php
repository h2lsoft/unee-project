<?php

namespace Plugin\Core;


use Core\Session;

class GroupController extends \Core\Controller {

	public string $table = 'xcore_group';
	public string $object_label = 'group';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {
		
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->setDeleteMessageWarning("All users will be also deleted");
		
		
		
		$datagrid->qSelectEmbedCount('xcore_user', 'nb_users');
		$datagrid->qWhere("priority >= (select priority from xcore_group where id = :user_group_id)", [':user_group_id' => \Core\Session::get('auth.xcore_group_id')]);
		
		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('access_backend', 'Backend');
		$datagrid->searchAddBoolean('access_frontend', 'Frontend');
		$datagrid->searchAddNumber('priority');
		$datagrid->searchAddBoolean('active');
		
		
		// columns
		$datagrid->addColumn('id', '', true, 'min center');
		$datagrid->addColumnHtml('name', '', false, 'min');
		$datagrid->addColumn('description', '');
		$datagrid->addColumnButton('nb_users', 'Users', "/@backend/user/?search[]=xcore_group_id||[ID]", true, "bi bi-person-fill");
		$datagrid->addColumn('priority', '', true, 'min center');
		$datagrid->addColumnBoolean('access_backend', 'Backend', false);
		$datagrid->addColumnBoolean('access_frontend', 'Frontend', false);
		$datagrid->addColumnBoolean('active');
		
		$datagrid->setOrderByInit('priority', 'asc');
		
		
		
		// hookData
		$datagrid->hookData(function($row){
			
			if(\Core\Session::get('auth.xcore_group_id') == $row['id'] || $row['id'] == 1)
				$row['btn_delete_class'] = 'invisible';
			
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
		
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addText('description', '', false, []);
		$form->addSwitch('access_backend', 'Backend access');
		$form->addSwitch('access_frontend', 'Frontend access');
		$form->addNumber('priority', '', true);
		$form->addSwitch('active', '', false)->setValue('yes');
		
		
		// validation
		if($form->isSubmitted())
		{
			// custom rules
			$group_priority = \Model\Group::findOne(Session::get("auth.xcore_group_id"), [], 'priority')['priority'];
			$form->input('priority')->min($group_priority);
			
			if($form->inputGet('active') != 'yes' && Session::get("auth.xcore_group_id") == $form->id)
				$form->addError('active', "You can't deactivate your group");
			
			if($form->inputGet('access_backend') != 'yes' && $form->inputGet('access_frontend') != 'yes')
			{
				$form->addError('access_backend', "You must select one access at least");
			}
			
			
			// valid
			if($form->isValid())
			{
				$form->save();
			}
			
			return $form->json();
		}
		
		
		return $form->render();
		
	}
	
	
	public function onDeleteBefore():void
	{
		if(\Core\Session::get('auth.xcore_group_id') == $this->id)
		{
			$this->validator->addError("You can't delete your group");
		}
		
		if($this->id == 1)
		{
			$this->validator->addError("You can't delete superadmin group");
		}
		
	}
	
	


}