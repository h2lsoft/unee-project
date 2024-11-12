<?php

namespace Plugin\Core_Backend;


use Core\Session;

class MenuController extends \Core\Controller {
	
	public string $table = 'xcore_menu';
	public string $object_label = 'menu';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {
		
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 0);
		$datagrid->qOrderBy('position asc');
		$datagrid->qSelectEmbedCount('xcore_plugin', 'nb_plugins');
		
		
		// columns
		$datagrid->addColumnPosition();
		$datagrid->addColumn('id');
		$datagrid->addColumnHtml('icon', ' ', false, 'min center');
		$datagrid->addColumn('name', '', false, '');
		$datagrid->addColumnButton('nb_plugins', 'plugins', "/@backend/plugin/?search[]=xcore_menu_id||[ID]", false, "bi bi-plugin");
		
		// hookData
		$datagrid->hookData(function($row){

			$row['icon'] = "<i class=\"{$row['icon']}\"></i>";

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
		
		$form->addText('name', '', true, ['class' => 'upper']);
		$form->addText('icon', '', true, []);

		
		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$added = [];
				if($form->is_adding)
				{
					$position = $form->getMaxPosition();
					$added['position'] = $position;
				}
				
				$form->save([], $added);
			}
			
			return $form->json();
		}
		
		
		return $form->render();
		
	}
	
	
	
	
	
}