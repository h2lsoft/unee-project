<?php

namespace Plugin\Core_Frontend;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class FilesController extends \Core\Controller {

	public string $table = 'xcore_files';
	public string $object_label = 'file';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 25);
		$datagrid->qSelect("'' as file");


		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('category');
		$datagrid->searchAddDatetime('date');
		$datagrid->searchAddText('title');
		$datagrid->searchAddSelectSql('source');
		$datagrid->searchAddBoolean('visible');
		$datagrid->searchAddTagManager();

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnDate('date', '', '', true);
		$datagrid->addColumn('category', '', true, 'min');
		$datagrid->addColumnHtml('title', '', false, '');
		$datagrid->addColumnHtml('source', '', true, 'min');
		$datagrid->addColumnTags();
		$datagrid->addColumnBoolean('visible');
		$datagrid->addColumnHtml('file', '', false, 'min center');

		// hookData
		$datagrid->hookData(function($row){

			$file_icon = \Core\Html::getIconExtension($row['url'], 'fs-4');

			$row['file'] = "<a class='btn btn-light px-2 py-2' href=\"{$row['url']}\" target=\"_blank\">{$file_icon}";
			if($row['url'][0] == '/' && file_exists(APP_PATH.$row['url']))
			{
				$row['file'] .= " ".human_filesize(@filesize(APP_PATH.$row['url']), 0);
			}

			$row['file'] .= "</a>";

			if(empty($row['source']))$row['source'] = '-';

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

		$form->addDate('date', '', true)->setValue(now('', false));

		$form->addText('category', '', true, ['class' => 'upper'])->datalist();
		$form->addText('title', '', true, ['class' => 'ucfirst']);
		$form->addFileBrowser('url', '', true, "x-files");

		$form->addText('source', '', false, ['class' => 'ucfirst'])->datalist();
		$form->addSwitch('visible')->setValue('yes');
		$form->addTagManager();


		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$exceptions = [];
				$added = [];
				$added['category_slug'] = slugify(post('category'));

				$form->save($exceptions, $added);
			}

			return $form->json();
		}

		return $form->render();
	}


	public static function render(array $tags=[], string $date_format="Y/m/d", string $class="", string $category_forced=""):string
	{
		$sql_added = "";
		$sql_tags = "";
		$sql_added_parameters = [];
		$categories = [];

		// search by tags
		if(count($tags))
		{
			$tagsx = array_map('addslashes', $tags);
			$tags_str = "'".join("', '", $tagsx)."'";
			$sql_tags = " and id in(select record_id from xcore_tag where deleted = 'no' and signature = 'xcore_files' and tag IN({$tags_str})) ";
			$sql_added .= " {$sql_tags} ";
		}

		// category
		if(empty($category_forced))
		{
			// get categories
			$sql = "select distinct category from xcore_files where deleted = 'no' and visible='yes' {$sql_tags} ";
			$categories = Db()->query($sql)->fetchAllOne();
			$cur_category = get('category', '');

			if(empty($cur_category) && count($categories))
			{
				$cur_category = slugify($categories[0]);
			}

			$sql_added .= " and category_slug = :category_slug ";
			$sql_added_parameters['category_slug'] = $cur_category;
		}
		else
		{
			$category_forcedX = addslashes($category_forced);
			$sql_added .= " and category = '{$category_forcedX}' ";
		}

		// files by category
		$sql = "select * from xcore_files where deleted = 'no' and visible='yes' {$sql_added} order by title";
		$files = Db()->query($sql, $sql_added_parameters)->fetchAll();

		$files2 = [];
		foreach($files as $file)
		{
			if(empty($file['source']))$file['source'] = '-';

			$file['size'] = '';
			if(str_starts_with($file['url'], '/') && file_exists(APP_PATH.$file['url']))
			{
				$file['size'] = human_filesize(@filesize(APP_PATH . $file['url']), 0);
			}

			// icon change
			$file['link_icon'] = \Core\Html::getIconExtension($file['url'], "", true);


			$files2[] = $file;
		}

		$files = $files2;


		$data = [];
		$data['category_forced'] = $category_forced;
		$data['tags'] = $tags;
		$data['categories'] = $categories;
		$data['class'] = $class;
		$data['files'] = $files;
		$data['date_format'] = $date_format;


		return View("render", $data)->getContent();
	}

}
