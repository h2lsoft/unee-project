<?php

namespace Plugin\Core_Frontend;

class PatternController extends \Core\Controller {

	public string $table = 'xcore_pattern';
	public string $object_label = 'pattern';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);


		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('category');
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('active');

		// columns
		$datagrid->addColumn('id');
		$datagrid->addColumn('category', '', false, 'min');
		$datagrid->addColumnHtml('name', '', false, '');
		$datagrid->addColumnHtml('pattern', '', false, 'min');
		$datagrid->addColumnBoolean('active');

		// hookData
		$datagrid->hookData(function($row){

			if(!empty($row['description']))
				$row['name'] .= " <br><small class='text-muted'>{$row['description']}</small>";

			$row['pattern'] = "<code>{$row['pattern']}</code>";

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

		$form->addText('category', '', true, ['class' => 'upper'])->datalist();
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addText('description', '', false, ['class' => 'ucfirst']);
		$form->addText('pattern', '', true);
		$form->addTextarea('content', '', true);
		$form->addSwitch('active')->setValue('yes');


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

