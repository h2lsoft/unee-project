<?php

namespace Plugin\Core_Frontend;

class Gallery_CardController extends \Core\Controller {

	public string $table = 'xcore_gallery_card';
	public string $object_label = 'card';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list()
	{
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 0);
		$datagrid->qOrderBy('position asc');

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddNumber('xcore_gallery_id', 'gallery id');

		// columns
		// $datagrid->addColumn('id', '', false);
		$datagrid->addColumnImage('image', 'card', false, '', true);
		$datagrid->addColumn('title', '', false);
		$datagrid->addColumnBoolean('visible', '', false);
		$datagrid->addColumnPosition();

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


		$form->addSelectSql('xcore_gallery_id', 'Gallery', true);
		$form->addText('title', '', true, ['class' => 'ucfirst']);
		$form->addTextarea('description', '', true, ['class' => 'ucfirst']);
		$form->addFileImage('image', '', true, \Core\Config::get('dir/slider_card'));
		$form->addHr();
		$form->addSwitch('visible', '', true)->setValue('yes');


		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$added = [];

				if($form->is_adding)
				{
					$position = $form->getMaxPosition("xcore_gallery_id = ".post('xcore_gallery_id'));
					$added['position'] = $position;
				}

				$form->save([], $added);
			}

			return $form->json();
		}

		return $form->render();

	}



}
