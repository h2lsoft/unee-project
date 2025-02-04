<?php

namespace Plugin\Core;

class GlobalsController extends \Core\Controller {

	public string $table = 'xcore_globals';
	public string $object_label = 'variable';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 0);
		$datagrid->qOrderBy('package, name');

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('package');
		$datagrid->searchAddText('name');

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumn('package', '', true, 'min');
		$datagrid->addColumn('name', '', true, 'min');
		$datagrid->addColumn('value', '', false, '');

		// hookData
		$datagrid->hookData(function($row){
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

		$form->addText('package', '', true)->setValue('default');
		$form->addText('name', '', true);
		$form->addTextarea('value', '', true, ['class' => 'code-highlighter code-highlighter-json']);


		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$form->save();
			}

			return $form->json();
		}


		return $form->render();
	}

}