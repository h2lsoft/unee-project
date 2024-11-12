<?php

namespace Plugin\Core_Backend;


use Core\Config;

class Page_ManagerController extends \Core\Controller {

	public string $table = 'xcore_page';
	public string $object_label = 'page';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$cur_language = get('language', \Core\Config::get('frontend/langs')[0][0]);
		$cur_zone_id = get('xcore_page_zone_id', 1);

		$data = [];
		$data['zones'] = \Model\Page_Zone::all('', [], '', '', 'position');

		$data['langs'] = \Core\Config::get('frontend/langs');
		$data['language'] = $cur_language;
		$data['xcore_page_zone_id'] = $cur_zone_id;

		$data['pages'] = \Model\Page::getAll($cur_zone_id, $cur_language);


		return View("treeview", $data);

	}

	/**
	 * @route /@backend/@module/add/ {method:"GET|POST", controller:"add"}
	 * @route /@backend/@module/edit/{id}/ {method:"GET|PUT", controller:"edit"}
	 */
	public function getForm(int $id=0)
	{
		$form = new \Component\Form();
		$form->loadAssetsJs(['func.js']);

		$form->linkController($this, $id);

		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addRadio('type', '', true, ['page','url', 'url external'])->setValue('page');

		$cur_lang = ($form->is_adding) ? get('language', \Core\Config::get('frontend/langs')[0][0]) : $form->getValue('language');
		$cur_zone_id = ($form->is_adding) ? get('xcore_page_zone_id', 1) : $form->getValue('xcore_page_zone_id');

		// CONTENT *****************************************************************************************************
		$form->addTabMenu();

		$form->addTab("Content");
		$form->addTextarea('resume', '', false);
		$form->addTextarea("content", '', false, ['style' => 'min-height:300px']);

		// authors
		$sql_author_where = <<<SQL
				active = 'yes' and 
     			(
				    xcore_group_id = 1 or
				    xcore_group_id IN(SELECT xcore_group_id FROM xcore_group_right WHERE deleted = 'no' AND xcore_plugin_id = (SELECT id FROM xcore_plugin WHERE name IN('page-manager') and deleted = 'no'))
				)
SQL;
		$allowed_authors = \Model\User::all($sql_author_where, [], "id as value, CONCAT(lastname,' ', firstname) as label", "", "lastname, firstname");
		$form->addSelect('xcore_user_id', 'author', false, $allowed_authors)->setValue(\Model\User::getUID());

		$form->addTabEnd();

		// OPTION ******************************************************************************************************
		$form->addTab('Options');
		// $pages = \Model\Page::getAllPages($cur_lang, $id);
		// $form->addSelect('xcore_page_id', 'Parent page', false, $pages);

		$form->addUrl('url', '', false);

		$form->addSwitch('menu_visible', 'menu visible', true)->setValue('yes');
		$form->addSwitch('is_homepage', 'homepage', false);
		$form->addFileImage('header_image', 'image', false, \Core\Config::get("dir")['page']);

		$tpl_dir = APP_PATH.'/public/'.APP_PACKAGE."/theme/".\Core\Config::get('frontend/theme')."/*.twig";
		$tpl_dirs = glob($tpl_dir);

		$templates = [];
		foreach($tpl_dirs as $tpl_dir)
		{
			$tpl_file = basename($tpl_dir);
			if(empty($tpl_file) || $tpl_file[0] == '@' || $tpl_file[0] == 'index.twig')continue;
			$templates[] = ['label' => $tpl_file, 'value' => $tpl_dir];
		}

		$form->addSelect('template', '', false, $templates);

		$form->addTabEnd();

		// SEO *********************************************************************************************************
		$form->addTab('SEO');
		$form->addText('meta_title', 'meta title', false);
		$form->addText('meta_description', 'meta description', false);
		$form->addText('meta_keywords', 'meta keywords', false);
		$form->addSelect('meta_robot', 'meta robot', false, ['noindex', 'nofollow', 'noindex, follow', 'noindex, nofollow']);
		$form->addText('meta_og_type', 'meta og:type', false);
		$form->addText('meta_og_image', 'meta og:image', false)->setHelp('1200 x 630 recommended');
		$form->addTabEnd();

		$form->addTabMenuEnd();


		$form->addHr();



		$form->addRadio('status', '', true, \Core\Config::get('frontend/page/status'))->setValue('draft');
		$form->addDatetime('publication_date', 'Publication date', false, ['class' => 'text-center'])->setInputSize(2);

		$form->addTagManager();

		// validation
		if($form->isSubmitted())
		{
			$form->validator->input('url')->requiredIf('type', ['url', 'url external']);
			$form->validator->input('resume')->requiredIf('type', ['article']);
			$form->validator->input('publication_date')->requiredIf('status', ['scheduled']);

			// valid
			if($form->isValid())
			{
				$added = [];

				if($form->is_adding)
				{
					$added['language'] = get('language', \Core\Config::get('frontend/langs')[0][0]);
					$added['xcore_page_zone_id'] = get('xcore_page_zone_id', 1);
					$added['xcore_page_id'] = get('xcore_page_id', 0);
					$added['xcore_user_id'] = \Model\User::getUID();

					// max position
					$sql = "SELECT position FROM xcore_page WHERE deleted = 'no' AND xcore_page_zone_id = :zone_id AND xcore_page_id = :page_id ORDER BY position DESC LIMIT 1";
					$max_position = DB()->query($sql, [':zone_id' => $added['xcore_page_zone_id'], ':page_id' => $added['xcore_page_id']])->fetchOne();
					$added['position'] = (int)$max_position + 1;
				}

				$f = $form->save(['seo_properties'], $added);

				// homepage url
				if(post('is_homepage') == 'yes')
				{
					$lng = \Model\Page::findByID($form->id)['language'];
					$where = ["language = :language and is_homepage = 'yes' and id != :id", [':language' => $lng, ':id' => $form->id]];
					\Model\Page::update(['is_homepage' => 'no'], $where);
				}

			}

			return $form->json();
		}




		return $form->render();
	}





}


