<?php

namespace Plugin\Core_Backend;


class Page_ZoneController extends \Core\Controller {

	public string $table = 'xcore_page_zone';
	public string $object_label = 'page zone';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 0);
		$datagrid->qOrderBy('position asc');

		// columns
		$datagrid->addColumnPosition();
		$datagrid->addColumn('id');
		$datagrid->addColumn('name', '', false, '');
		$datagrid->addColumnHtml('tag', '', false, 'min');

		// hookData
		$datagrid->hookData(function($row){

			// tag
			$row['tag'] = "<code>{{ xZone(\"{$row['name']} #{$row['id']}\", false) }}</code>";


			// main zone
			if($row['id'] == 1)
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

		$form->addText('name', '', true, ['class' => 'upper']);


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

	public function onDeleteBefore():void
	{
		if($this->id == 1)
		{
			$this->validator->addError("You can't delete main zone");
		}

	}


}


