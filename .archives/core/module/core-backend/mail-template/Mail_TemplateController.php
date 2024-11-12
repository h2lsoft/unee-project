<?php

namespace Plugin\Core_Backend;

use Symfony\Component\HttpFoundation\JsonResponse;

class Mail_TemplateController extends \Core\Controller {
	
	public string $table = 'xcore_mail_template';
	public string $object_label = 'template';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list()
	{
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		
		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('category');
		$datagrid->searchAddText('name');
		$datagrid->searchAddText('description');
		
		// columns
		$datagrid->addColumn('id', '', true, 'min center');
		$datagrid->addColumn('category', '', false, 'min');
		$datagrid->addColumn('name', '', false, 'min');
		$datagrid->addColumn('description', '');
		
		$datagrid->setOrderByInit('id', 'asc');
		
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
		
		$form->addText('category', '', true, [])->datalist();
		$form->addText('name', '', true, []);
		$form->addTextarea('description', '', false, []);
		
		// frontend languages
		$langs = \Core\Config::get('frontend/langs');
		foreach($langs as $lang)
		{
			$no_translate = ($lang[0] != 'en') ? '' : ' ';
			$flag = ($lang[0] == 'en') ? 'gb' : $lang[0];

			$form->addHeader($lang[1], "<i class='fi fi-{$flag}'></i> ".ucfirst($lang[1]));
			$form->addText("sender_{$lang[0]}", "Sender{$no_translate}", false);
			$form->addText("subject_{$lang[0]}", "Subject{$no_translate}", true);
			$form->addTextarea("body_{$lang[0]}", "Message{$no_translate}", true, ['style' => 'height:10em'], "You can put variables like `[[ VARIABLE ]]`");
		}
		
		
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