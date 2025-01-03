<?php

namespace Plugin\Core_Frontend;

class BlockController extends \Core\Controller
{
	public string $table = 'xcore_block';
	public string $object_label = 'block';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->qSelect("'' as target_button");

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('active');
		$datagrid->searchAddTagManager();

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumn('name', '', false, '');
		$datagrid->addColumn('type', '', false, 'min center');

		$datagrid->addColumnDatetime('publication_date_start', 'publication start', '', false, 'min center');
		$datagrid->addColumnDatetime('publication_date_end', 'publication end', '', false, 'min center');

		$datagrid->addColumnTags();
		$datagrid->addColumnBoolean('active');

		// selectable
		if(!empty(get('target', '')))
		{
			$datagrid->addColumnHtml('target_button', ' ', false, 'min center');
		}


		// hookData
		$datagrid->hookData(function($row){

			if(!empty(get('target', '')))
			{
				$itemX = addslashes("{$row['name']} - #{$row['id']}");
				$row['target_button'] = "<a class=\"btn btn-dark\" href=\"javascript:;\" onclick=\"opener.$('[name=".get('target')."]').val('{$itemX}'); window.close();\"><i class='bi bi-arrow-down'></i></a>";
			}


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

		$form->loadAssetsJs(['form.js']);

		$form->linkController($this, $id);

		$form->addText('name', '', true, []);
		$form->addSelect('type', '', true, ['content','file'])->setValue('content');

		$form->addText('file_path', 'file path <kbd>@theme/blocks/</kbd>', false)->setHelp('path relative to your block theme directory');
		$form->addTextarea('content', '', false, ['style' => "height:350px", 'class' => 'code-highlighter code-highlighter-html']);

		$form->addHeader('Publication');
		$form->addDatetime('publication_date_start', 'date start', false);
		$form->addDatetime('publication_date_end', 'date end', false);
		$form->addHr();

		$form->addSwitch('active')->setValue('yes');
		$form->addTagManager();


		// validation
		if($form->isSubmitted())
		{
			$this->validator->input('file_path')->requiredIf('type', 'file');
			$this->validator->input('content')->requiredIf('type', 'content');

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
		if(empty($fullname))$fullname = $id;

		$block = db()->query("select * from xcore_block where deleted = 'no' and id = {$id}")->fetch();

		if(!$block)
		{
			$replace = "\n<!-- block #{$fullname} not found -->\n";
		}
		elseif($block['active'] == 'no')
		{
			$replace = "\n<!-- block `{$fullname}` is not active -->\n";
		}
		elseif(
				($block['publication_date_start'] != '0000-00-00 00:00:00' && $block['publication_date_start'] > now()) ||
				($block['publication_date_end'] != '0000-00-00 00:00:00' && $block['publication_date_end'] <= now())
		)
		{
			$replace = "\n<!-- block `{$fullname}` is not visible -->\n";
		}
		else
		{
			$block_contents = "";
			if($block['type'] == 'content')
			{
				$block_contents = $block['content'];
			}
			else
			{
				$data_path = $block['file_path'];
				$filename = APP_PATH.'/theme/'.\Core\Config::get('frontend/theme').'/blocks/'.$data_path;
				$file_contents = file_get_contents($filename);

				// extension twig
				if(file_get_extension($data_path) == 'twig')
				{
					$template = App()->twig->createTemplate($file_contents);
					$block_contents = $template->render($data);
				}
				else
				{
					$block_contents = $file_contents;
				}
			}

			$replace = "\n<!-- block #{$fullname} -->\n";
			$replace .= $block_contents;
			$replace .= "\n<!-- /block #{$fullname} -->\n";
		}

		return $replace;


	}



}