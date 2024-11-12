<?php

namespace Plugin\Core_Frontend;

class Slider_CardController extends \Core\Controller {

	public string $table = 'xcore_slider_card';
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
		$datagrid->searchAddNumber('xcore_slider_id', 'slider id');

		// columns
		// $datagrid->addColumn('id', '', false);
		$datagrid->addColumnImage('image', 'card', false, '', true);

		$datagrid->addColumn('name', '', false);
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


		$form->addSelectSql('xcore_slider_id', 'Slider', true);
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addFileImage('image', '', true, \Core\Config::get('dir/slider_card'));

		$form->addHeader('Option');

		// title
		$form->addText('title', '', false);
		$form->addSelectColor('title_color', 'title color', false);

		// text
		$form->addTextarea('text', '', false);
		$form->addSelectColor('text_color', 'text color', false);

		// button
		$form->addText('button_text', 'button text', false);
		$form->addSelectColor('button_text_color', 'button text color', false);
		$form->addSelectColor('button_bg_color', 'button bg color', false);

		$form->addText('button_href', 'button href', false);
		$form->addText('button_class', 'button class', false);

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
					$position = $form->getMaxPosition("xcore_slider_id = ".post('xcore_slider_id'));
					$added['position'] = $position;
				}

				$form->save([], $added);
			}

			return $form->json();
		}

		return $form->render();

	}



}
