<?php

namespace Plugin\Core_Frontend;

use Gumlet\ImageResize;
use Symfony\Component\Finder\Finder;

class GalleryController extends \Core\Controller {

	public string $table = 'xcore_gallery';
	public string $object_label = 'gallery';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 50);
		$datagrid->qSelectEmbedCount('xcore_gallery_card', 'nb_cards');
		$datagrid->qSelect("'' as target_button");

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddText('name');
		$datagrid->searchAddBoolean('active');
		$datagrid->searchAddTagManager();

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnHtml('name', '', true, '');
		$datagrid->addColumnButton('cards', 'Cards', "/@backend/gallery-card/?search[]=xcore_gallery_id||[ID]&_popup=1", false, 'bi bi-images', 'btn-primary py-2', ['target' => '_popup']);
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

		$form->addHr();

		if($form->is_adding)
		{
			$form->addFileBrowser('import', "", false, 'x-gallery-import', 'image', true);
		}

		$form->addHr();
		$form->addTagManager();

		$form->addHr();
		$form->addSwitch('active')->setValue('yes');


		// validation
		if($form->isSubmitted())
		{

			// valid
			if($form->isValid())
			{
				$exception = [];

				$import_detected = false;
				if($form->is_adding)
				{
					$exception[] = 'import';

					if(!empty(post('import', '')))
						$import_detected = true;
				}

				$added = [];
				$form->save($exception, $added);

				// import detected
				if($import_detected)
				{
					$folder = APP_PATH.post('import');
					if(is_file($folder))
						$folder = dirname($folder);

					$finder = new Finder();
					$finder->files()->in($folder)->depth('== 0')->name('/\.('.join('|', \Core\Config::get('file_manager/filters/image')).')$/i');

					$import_images = [];
					$position = 0;
					foreach ($finder as $file)
					{
						$image_path = $file->getRealPath();
						$file_name = $file->getFilename();
						$card_ext = strtolower($file->getExtension());

						$f2 = [];
						$f2['xcore_gallery_id'] = $form->id;
						$f2['title'] = trim(str_replace(['-', '_', ".{$card_ext}"],' ', $file_name));
						$f2['position'] = ++$position;
						$card_id = db('xcore_gallery_card')->insert($f2);

						$dest = \Core\Config::get('dir/gallery_card')."/{$card_id}.{$card_ext}";
						db('xcore_gallery_card')->update(['image' => get_absolute_path($dest)], $card_id);

						if(@copy($image_path, $dest))
							$import_images[] = $image_path;
					}

					foreach($import_images as $import_image)
					{
						@unlink($import_image);
					}
				}
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
		$records = \Model\Gallery::all("active = 'yes'", [], '*', '', 'name');
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
		$gallery = Db()->query("select * from xcore_gallery where deleted='no' and active = 'yes' and id={$id}")->fetch();
		$cards = Db()->query("select * from xcore_gallery_card where deleted='no' and visible = 'yes' and xcore_gallery_id={$id} order by position")->fetchAll();

		// thumbnails
		for($i=0; $i < count($cards); $i++)
		{
			$cards[$i]['thumbnail_url'] = \Model\Gallery::getThumbnailUrl($cards[$i]['id'], $cards[$i]['image']);
		}

		$data = [];
		$data['gallery'] = $gallery;
		$data['cards'] = $cards;

		return View('render', $data)->getContent();
	}







}
