<?php

namespace Plugin\Core_Backend;

class BlacklistController extends \Core\Controller
{
	public string $table = 'xcore_blacklist';
	public string $object_label = 'email';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddDatetime('date');
		$datagrid->searchAddText('email');

		// columns
		$datagrid->addColumnPosition();
		$datagrid->addColumn('id');
		$datagrid->addColumnDatetime('date');
		$datagrid->addColumn('email');

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

		$form->addEmail('email', '', true)->setHelp("Put `*@domain.com` to exclude all domain");

		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$added = [];
				$added['date'] = now();
				$form->save([], $added);
			}

			return $form->json();
		}

		return $form->render();

	}

}