<?php

namespace Plugin\Core_Frontend;

class TemplateController extends \Core\Controller
{
	public string $table = 'xcore_page_template';
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
		$datagrid->searchAddSelectSql('collection');
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('active');
		$datagrid->searchAddTagManager();

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnImage('image_preview', 'preview', false, '', true);
		$datagrid->addColumn('collection', '', true, 'min center');
		$datagrid->addColumnHtml('name', '', false, '');
		$datagrid->addColumnTags();
		$datagrid->addColumnBoolean('active');


		// hookData
		$datagrid->hookData(function($row){

			if(empty($row['image_preview']))
			{
				$row['image_preview'] = \Core\Config::get('dir')['page_template'].'/0.png';
				$row['image_preview'] = get_absolute_path($row['image_preview']);
			}

			$row['name'] .= "<br><small>{$row['description']}</small>";

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


		$form->addText('collection', '', true, ['class' => 'ucfirst'])->datalist();
		$form->addFileImage('image_preview', '', false, \Core\Config::get('dir')['page_template']);
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addText('description', '', true, ['class' => 'ucfirst']);

		$msg = "&bull; contenteditable='true' creates an editable text<br>";
		$msg .= "&bull; x-image-editable='true' creates an editable image path (x-folder-path=assign folder path)<br>";

		$form->addTextarea('content', '', true, ['style' => "height:450px", 'class' => "code-highlighter code-highlighter-html"])->setHelp($msg);



		$form->addSwitch('active')->setValue('yes');
		$form->addTagManager();


		// validation
		if($form->isSubmitted())
		{

			// valid
			if($form->isValid())
			{
				$exceptions = [];
				$added = [];

				$form->save($exceptions, $added, ['content']);
			}

			return $form->json();
		}

		return $form->render();

	}


	/**
	 * @route /@backend/@module/all/
	 */
	public static function all()
	{
		$data = [];

		// get collections
		$sql = "select distinct collection from xcore_page_template where deleted = 'no' and active = 'yes' order by collection";
		$collections = DB()->query($sql)->fetchAllOne();

		// get alls
		$sql = "select * from xcore_page_template where deleted = 'no' and active = 'yes' order by name";
		$records = DB()->query($sql)->fetchAll();

		for($i=0; $i < count($records); $i++)
		{
			if(empty($records[$i]['image_preview']))
				$records[$i]['image_preview'] = get_absolute_path(\Core\Config::get('dir')['page_template'].'/0.png');
		}

		$data['collections'] = $collections;
		$data['records'] = $records;
		return View('all', $data);

	}


}