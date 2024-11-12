<?php

namespace Plugin\Core_Backend;

use Core\Html;

class Mailinglist_SubscriberController extends \Core\Controller {

	public string $table = 'xcore_mailinglist_subscriber';
	public string $object_label = 'email';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->qSelectEmbed('xcore_mailinglist', 'name', 'list');

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('xcore_mailinglist_id', 'list');
		$datagrid->searchAddSelectSql('language');
		$datagrid->searchAddText('lastname');
		$datagrid->searchAddText('firstname');
		$datagrid->searchAddDatetime('date');


		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumn('list', '', false, 'min');
		$datagrid->addColumnDatetime('date', '', '', true);
		$datagrid->addColumn('language', '', false, 'min center');
		$datagrid->addColumn('lastname', '', false, 'min');
		$datagrid->addColumn('firstname', '', false, 'min');
		$datagrid->addColumn('email', '', false);


		// hookData
		$datagrid->hookData(function($row){

			if(empty($row['lastname']))$row['lastname'] = '-';
			if(empty($row['firstname']))$row['firstname'] = '-';

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

		$form->addDatetime('date', '', true)->setValue(now());
		$form->addEmail('email', '', true, ['class' => 'lower']);
		$form->addText('lastname', '', false, ['class' => 'ucfirst']);
		$form->addText('firstname', '', false, ['class' => 'ucfirst']);

		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$added = [];
				if($form->is_adding)
				{

				}

				$form->save([], $added);
			}

			return $form->json();
		}

		return $form->render();
	}




}
