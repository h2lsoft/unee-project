<?php

namespace Plugin\Core_Frontend;

use Core\JsonResponse;

class SliderController extends \Core\Controller {

	public string $table = 'xcore_slider';
	public string $object_label = 'slider';


	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 50);
		$datagrid->qSelectEmbedCount('xcore_slider_card', 'nb_cards');
		$datagrid->qSelect("'' as target_button");

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('active');
		$datagrid->searchAddTagManager();

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnHtml('name', '', true, '');
		$datagrid->addColumnButton('cards', 'Cards', "/@backend/slider-card/?search[]=xcore_slider_id||[ID]&_popup=1", false, 'bi bi-images', 'btn-primary py-2', ['target' => '_popup']);
		$datagrid->addColumnTags();
		$datagrid->addColumnBoolean('active', '', false);

		// selectable
		if(!empty(get('target', '')))
		{
			$datagrid->addColumnHtml('target_button', ' ', false, 'min center');
		}


		$datagrid->hookData(function($row){

			$row['cards'] = "{$row['nb_cards']}";

			if(!empty(get('target', '')))
			{
				$itemX = addslashes("{$row['name']} - #{$row['id']}");
				$row['target_button'] = "<a class=\"btn btn-dark\" href=\"javascript:;\" onclick=\"opener.$('[name=".get('target')."]').val('{$itemX}'); window.close();\"><i class='bi bi-arrow-down'></i></a>";
			}


			if(!empty($row['description']))
				$row['name'] .= " <br><small class='text-muted'>{$row['description']}</small>";


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
		$form->addText('description', '', false, ['class' => 'ucfirst']);
		$form->addText('class', '', false, []);
		// $form->addSwitch('js')->setValue('yes');

		$form->addHeader("Options");
		$form->addNumber('option_slider_per_view', 'Slide per view', true, ['class' => 'text-center'])->setValue(1)->setInputSize(1);
		$form->addSwitch('option_navigation', 'Navigation')->setValue('yes');
		$form->addSwitch('option_pagination', 'Pagination')->setValue('yes');
		$form->addSwitch('option_loop', 'Loop')->setValue('yes');
		$form->addSwitch('option_centered_slides', 'Centered')->setValue('yes');

		$form->addSwitch('option_autoplay', 'Autoplay')->setValue('yes');
		$form->addNumber('option_autoplay_delay_ms', 'Autoplay delay', true, ['class' => 'text-center'])->setAfter('ms')->setValue(3000)->setInputSize(1);

		$form->addSelectEnum('option_direction', 'Direction', true)->setValue('horizontal');
		$form->addSelectEnum('option_effect', 'Effect', true)->setValue('fade');

		$form->addNumber('option_gap', 'Gap', true, ['class' => 'text-center'])->setValue(0)->setAfter('px')->setInputSize(1);
		$form->addNumber('option_speed', 'Speed', true, ['class' => 'text-center'])->setAfter('ms')->setValue(300)->setInputSize(1);
		$form->addSwitch('option_allow_touch', 'Touchable')->setValue('yes');
		$form->addSwitch('option_grab_cursor', 'Grab cursor')->setValue('yes');
		$form->addSwitch('option_mousewheel', 'Mousewheel')->setValue('yes');
		$form->addSwitch('option_keyboard', 'Keyboard')->setValue('yes');
		$form->addSwitch('option_zoom', 'Zoom')->setValue('yes');

		$placeholder = '{"768": {"slidesPerView": 3}}';
		$form->addTextarea('option_breakpoints', 'Breakpoints', false, ['placeholder' => $placeholder])->setHelp("JSON format");

		$form->addHr();
		$form->addTagManager();


		$form->addHr();
		$form->addSwitch('active')->setValue('yes');



		// validation
		if($form->isSubmitted())
		{
			// $form->validator->input('class')->alphaNumeric("-_", false, true, false);

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
	 * @route /@backend/@module/all/
	 */
	public static function all():\Core\JsonResponse
	{
		$records = \Model\Block::all("active = 'yes'", [], '*', '', 'name');
		return new \Core\JsonResponse($records);
	}


	/**
	 * frontend render
	 *
	 * @param int $id
	 * @param string $fullname
	 * @param array $data
	 *
	 * @return string
	 */
	static public function render(int $id, string $fullname="", array $data=[]):string
	{
		$slider = Db()->query("select * from xcore_slider where deleted='no' and active = 'yes' and id={$id}")->fetch();
		$cards = Db()->query("select * from xcore_slider_card where deleted='no' and visible = 'yes' and xcore_slider_id={$id} order by position")->fetchAll();

		$parameters = [];
		$parameters['navigation'] = (@$slider['option_navigation'] === 'yes') ? 'true' : 'false';
		$parameters['pagination'] = (@$slider['option_pagination'] === 'yes') ? 'true' : 'false';
		$parameters['space-between'] = @$slider['option_gap'];
		$parameters['direction'] = @$slider['option_direction'];
		$parameters['speed'] = @$slider['option_speed'];
		$parameters['slides-per-view'] = @$slider['option_slider_per_view'];
		$parameters['centered-slides'] = (@$slider['option_centered_slides'] === 'yes') ? 'true' : 'false';
		$parameters['autoplay'] = (@$slider['option_autoplay'] === 'yes') ? 'true' : 'false';
		$parameters['loop'] = (@$slider['option_loop'] === 'yes') ? 'true' : 'false';
		$parameters['zoom'] = (@$slider['option_zoom'] === 'yes') ? 'true' : 'false';

		$parameters['effect'] = @$slider['option_effect'];

		if($parameters['autoplay'] == 'true')
			$parameters['autoplay-delay'] = @$slider['option_autoplay_delay_ms'];


		$parameters['keyboard'] = (@$slider['option_keyboard'] === 'yes') ? 'true' : 'false';
		$parameters['mousewheel'] = (@$slider['option_mousewheel'] === 'yes') ? 'true' : 'false';
		$parameters['grab-cursor'] = (@$slider['option_grab_cursor'] === 'yes') ? 'true' : 'false';

		if(!@empty($slider['option_breakpoints']))
			$parameters['breakpoints'] = $slider['option_breakpoints'];


		$data = [];
		$data['slider'] = $slider;
		$data['cards'] = $cards;
		$data['parameters'] = $parameters;


		return View('render', $data)->getContent();

	}




}