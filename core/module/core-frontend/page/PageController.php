<?php

namespace Plugin\Core_Frontend;

use Core\Config;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Symfony\Component\HttpFoundation\JsonResponse;

class PageController extends \Core\Controller {

	public string $table = 'xcore_page';
	public string $object_label = 'page';

	/**
	 * @route /@backend/@module/
	 */
	public function list() {

		$cur_language = get('language', \Core\Config::get('frontend/langs')[0][0]);
		$cur_zone_id = get('xcore_page_zone_id', 1);

		$data = [];
		$data['zones'] = \Model\Zone::all('', [], '', '', 'position');

		$data['langs'] = \Core\Config::get('frontend/langs');
		$data['language'] = $cur_language;
		$data['xcore_page_zone_id'] = $cur_zone_id;

		$data['pages'] = \Model\Page::getAll($cur_zone_id, $cur_language, false, "*");


		return View("treeview", $data);

	}


	public function getBreadcrumbs(int $zone_id, string $language, int $xcore_page_id, int $id):string
	{
		$zone = \Model\Zone::findById($zone_id);

		$str = "<div class=\"page-breadcrumb\">";
		$str .= "<ul>";
		$str .= "	<li><a href='/@backend/page/?xcore_page_zone_id={$zone_id}&language={$language}'>{$zone['name']}</a></li>";

		if($id)
		{
			$cur_page = \Model\Page::findById($id);
			$xcore_page_id = \Model\Page::findById($id)['xcore_page_id'];
		}

		$page = \Model\Page::findById($xcore_page_id, "id, name, xcore_page_id");

		$reverse = [];
		do
		{
			if($page)
			{
				$reverse[] = "	<li><a href='/@backend/page/edit/{$page['id']}/?xcore_page_zone_id={$zone_id}&language={$language}'>{$page['name']} - #{$page['id']}</a></li>";
				$page = \Model\Page::findById($page['xcore_page_id'], "id, name, xcore_page_id");
			}

		} while($page);

		$reverse = array_reverse($reverse);
		foreach($reverse as $r)
		{
			$str .= "{$r}\n";
		}

		if(!$id)
		{
			$str .= "	<li><i18n>New page</i18n></li>";
		}
		else
		{
			$str .= "	<li>{$cur_page['name']}</li>";
		}


		$str .= "</ul>";
		$str .= "</div>";


		return $str;
	}


	/**
	 * @route /@backend/@module/add/ {method:"GET|POST", controller:"add"}
	 * @route /@backend/@module/edit/{id}/ {method:"GET|PUT", controller:"edit"}
	 */
	public function getForm(int $id=0)
	{
		$form_attr = (!$id) ? [] : ['data-success-notification' => 'ok'];
		$form = new \Component\Form("", $form_attr);
		$form->loadAssetsJs(['func.js']);

		$form->linkController($this, $id);

		$cur_lang = ($form->is_adding) ? get('language', \Core\Config::get('frontend/langs')[0][0]) : $form->getValue('language');
		$cur_zone_id = ($form->is_adding) ? get('xcore_page_zone_id', 1) : $form->getValue('xcore_page_zone_id');
		$cur_page_id = ($form->is_adding) ? get('xcore_page_id', false) : false;

		$zone_prefix_url = \Model\Zone::findById($cur_zone_id)['website'];

		// breadcrumbs
		$form->addHtml($this->getBreadcrumbs($cur_zone_id, $cur_lang, get('xcore_page_id', 0), $id));


		// lock detected
		if($form->is_editing && $form->getValue('locked') == 'yes' && \Model\User::getUID() != $form->getValue('locked_xcore_user_id'))
		{
			$locked_user_id = $form->getValue('locked_xcore_user_id');
			$user_info = \Model\User::findById($locked_user_id);
			$locked_user_login = $user_info['login'];
			$locked_avatar_image = \Model\User::getAvatarBadge($locked_user_id, $user_info['avatar'], $locked_user_login);

			$form->addHtml("<div class='text-center py-2 mb-4 bg-warning text-white'>{$locked_avatar_image} <b>`{$locked_user_login}`</b> <i18n>had locked this page, only him or prior member group can unlock it</i18n></div>");
		}

		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addRadio('type', '', true, Config::get('frontend/page/type'))->setValue('page');



		$form->addUrl('url', '', false, ['class' => 'lower', 'data-prefix' => $zone_prefix_url]);

		// CONTENT *****************************************************************************************************
		$form->addTabMenu();

		$form->addTab("Content");
		$form->addText('headline', "", false);

		$theme_url = "/theme/".\Core\Config::get('frontend/theme');
		$form->addHtmlarea("content", '', false, ['data-blockee-css-file' => "{$theme_url}/assets/css/@blockee.css"]);

		// authors
		$sql_author_where = <<<SQL
				active = 'yes' and 
     			(
				    xcore_group_id = 1 or
				    xcore_group_id IN(SELECT xcore_group_id FROM xcore_group_right WHERE deleted = 'no' AND xcore_plugin_id = (SELECT id FROM xcore_plugin WHERE name IN('page') and deleted = 'no'))
				)
SQL;
		$allowed_authors = \Model\User::all($sql_author_where, [], "id as value, CONCAT(lastname,' ', firstname) as label", "", "lastname, firstname");
		$form->addSelect('xcore_user_id', 'author', false, $allowed_authors)->setValue(\Model\User::getUID());

		$form->addTabEnd();

		// SEO *********************************************************************************************************
		$form->addTab('SEO');
		$form->addText('meta_title', 'meta title', false);
		$form->addText('meta_description', 'meta description', false);
		$form->addText('meta_keywords', 'meta keywords', false);
		$form->addSelect('meta_robot', 'meta robot', false, ['noindex', 'nofollow', 'noindex, follow', 'noindex, nofollow']);

		$form->addText('sitemap_priority', 'sitemap priority', false);
		$form->addSelectEnum('sitemap_change_freq', 'sitemap change freq', false);
		$form->addText('sitemap_pagination_pattern', 'sitemap pagination pattern', false)->setHelp("ex: `/news/page/[:page]/`");
		$form->addText('sitemap_follow_url_pattern', 'sitemap follow url pattern', false)->setHelp("ex: `/article/[:slug]`");
		$form->addText('sitemap_follow_url_priority', 'sitemap follow url priority', false);

		$form->addTabEnd();


		// OPTION ******************************************************************************************************
		$form->addTab('Options');
		$form->addSwitch('menu_visible', 'menu visible', false)->setValue('yes');
		$form->addSwitch('list_subpage', 'add subpages list', false);
		$form->addSwitch('is_homepage', 'homepage', false);

		$form->addSwitch('locked', '', false)->setAfter("Page won't be editable");

		/*
		$form->addFileImage(
								'featured_image',
								'image',
								false,
								\Core\Config::get("dir")['page'],
								\Core\Config::get("frontend/page/featured/upload_max_size"),
								\Core\Config::get("frontend/page/featured/allowed_extension"),
								\Core\Config::get("frontend/page/featured/allowed_mimes"),
								\Core\Config::get("frontend/page/featured/width"), false,
								\Core\Config::get("frontend/page/featured/height"), false
		);*/

		$form->addFileBrowser('featured_image', "image", false, "x-page/featured_image", 'image')->setHelp("<i18n>Recommended size</i18n>".": 1920 x 600");


		$tpl_dir = APP_PATH.'/theme/'.\Core\Config::get('frontend/theme')."/*.twig";
		$tpl_dirs = glob($tpl_dir);

		$templates = [];
		foreach($tpl_dirs as $tpl_dir)
		{
			$tpl_file = basename($tpl_dir);
			$tpl_file = str_erase('.twig', $tpl_file);

			if(empty($tpl_file) || $tpl_file[0] == '@' || $tpl_file == 'index'  || $tpl_file[0] == '_')continue;
			$templates[] = ['label' => $tpl_file, 'value' => $tpl_file.".twig"];
		}

		$form->addSelect('template', '', false, $templates);

		$form->addTabEnd();
		$form->addTabMenuEnd();
		$form->addHr();

		$form->addRadio('status', '', true, \Core\Config::get('frontend/page/status'))->setValue('draft');
		$form->addDatetime('publication_date', 'Publication date', false, ['class' => 'text-center'])->setInputSize(2);

		$form->addTagManager();

		// init values
		if($cur_page_id)
		{
			$father_page = \Model\Page::findById($cur_page_id);
			$keys = ['xcore_user_id', 'type', 'template', 'menu_visible', 'list_subpage'];

			foreach($keys as $key)
				$form->setValue($father_page[$key], $key);

		}



		// validation
		if($form->isSubmitted())
		{
			$form->validator->input('url')->requiredIf('type', ['url', 'url external']);
			$form->validator->input('publication_date')->requiredIf('status', ['scheduled']);

			// checked locked
			if($form->is_editing && $form->getValue('locked') == 'yes' && \Model\User::getUID() != $form->getValue('locked_xcore_user_id'))
			{
				$locked_user_id = $form->getValue('locked_xcore_user_id');
				$cur_user_group_id = \Core\Session::get('auth.xcore_group_id');
				$cur_user_group_priority = \Core\Session::get('auth.group_priority');

				$locked_user_priority = \Model\Group::findOne("id = (select xcore_group_id from xcore_user where id = {$locked_user_id})");

				// current lock
				if(($cur_user_group_id == \Model\Group::SUPER_ADMIN_ID || $cur_user_group_priority > $locked_user_priority))
				{
					if($form->inputGet('locked') == 'yes')
					{
						$msg = "Please, unlock this page to edit";
						$form->validator->input('locked')->addError($msg);
					}
				}
				else
				{
					$msg = "You can't edit this page, page is locked";
					$form->validator->input('locked')->addError($msg);
				}
			}

			// valid
			if($form->isValid())
			{
				$added = [];

				// locked
				if(
					($form->is_adding && $form->validator->inputGet('locked') == 'yes') ||
					($form->is_editing && $form->getValue('locked') == 'no' && $form->validator->inputGet('locked') == 'yes'))
				{
					$added['locked_at'] = now();
					$added['locked_xcore_user_id'] = \Model\User::getUID();
				}

				// reset lock
				if($form->validator->inputGet('locked') == 'no')
				{
					$added['locked_at'] = '';
					$added['locked_xcore_user_id'] = 0;
				}


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
					$lng = \Model\Page::findById($form->id)['language'];
					$where = ["language = :language and is_homepage = 'yes' and id != :id", [':language' => $lng, ':id' => $form->id]];
					\Model\Page::update(['is_homepage' => 'no'], $where);
				}
			}

			return $form->json();
		}

		return $form->render();
	}


	/**
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function delete(int $id):JsonResponse
	{
		$this->id = $id;

		$page = \Model\Page::findById($id);
		if(!$page)
		{
			$this->validator->addError("Page not found");
		}
		elseif($page['locked'] == 'yes' && \model\User::getUID() != $page['locked_xcore_user_id'])
		{
			$this->validator->addError("Page is locked");
		}

		// get all linked pages
		if($this->validator->success())
		{
			DB($this->table)->delete($id, 1, \Core\Session::get('auth.login'));

			// delete all children
			$c_ids = \Model\Page::getAllChildrenIds($id);
			foreach($c_ids as $c_id)
				DB($this->table)->delete($c_id, 1, \Core\Session::get('auth.login'));
		}

		return new JsonResponse($this->validator->result());
	}

	/**
	 * @route /@backend/@module/rename/ {method:"POST", controller:"rename"}
	 */
	public function rename():JsonResponse
	{
		$this->validator->input('id')->required()->integer();
		$this->validator->input('page_name')->required();


		$id = post('id');
		$page_name = post('page_name');
		$page_name = ucfirst(trim($page_name));

		if($this->validator->success())
		{
			$page = \Model\Page::findById($id);
			if(!$page)
			{
				$this->validator->addError("Page not found");
			}
			elseif($page['locked'] == 'yes' && \model\User::getUID() != $page['locked_xcore_user_id'])
			{
				$this->validator->addError("Page is locked");
			}
			else
			{
				$f = [];
				$f['name'] = $page_name;
				\Model\Page::update($f, $id, 1, \Core\Session::get('auth.login'));
			}

		}

		return new JsonResponse($this->validator->result());



	}

	/**
	 * @route /@backend/@module/add_direct/ {method:"POST", controller:"add_direct"}
	 */
	public function add_direct():JsonResponse {

		$this->validator->input('xcore_page_zone_id')->required();
		$this->validator->input('language')->required();
		$this->validator->input('page_name')->required();

		if($this->validator->success())
		{

		}

		return new JsonResponse($this->validator->result());
	}


	/**
	 * @route /@backend/@module/paste/ {method:"POST", controller:"paste"}
	 */
	public function paste()
	{
		$paste_action = post('paste_action');
		$paste_type = post('paste_type');
		$paste_source_page_id = post('paste_source_page_id');
		$paste_target_page_id = post('paste_target_page_id');

		$this->validator->input('paste_action')->in(['cut', 'copy']);
		$this->validator->input('paste_type')->in(['children', 'before', 'after']);

		$source_page = \Model\Page::findById((int)$paste_source_page_id);
		if(!$source_page)$this->validator->addError("Source page not found");

		$target_page = \Model\Page::findById((int)$paste_target_page_id);
		if(!$target_page)$this->validator->addError("Target page not found");

		if($this->validator->success())
		{
			if($paste_action == 'copy')
			{
				$new_page = $source_page;
				unset($new_page['id'], $new_page['deleted'], $new_page['created_at'], $new_page['created_by'], $new_page['updated_at'], $new_page['updated_by'], $new_page['deleted_at'], $new_page['deleted_by']);
				$new_page['position'] = (int)\Model\Page::findOne("xcore_page_id = {$paste_target_page_id}", [], 'position', [], 'position desc') + 1;
				$new_page['xcore_page_id'] = $target_page['xcore_page_id'];
				$new_page_id = \Model\Page::insert($new_page);

				$source_page = $new_page;
				$paste_source_page_id = $new_page_id;

				$paste_action = 'cut';
			}


			// cut action
			if($paste_action == 'cut')
			{
				// clean position
				$where = "xcore_page_zone_id = :zone_id and language = :language and xcore_page_id = :page_id";
				$pid = ($paste_type != 'children') ? $target_page['xcore_page_id'] : $target_page['id'];
				$params = [':zone_id' => $target_page['xcore_page_zone_id'], ':language' => $target_page['language'], ':page_id' => $pid];

				$all_pages = \Model\Page::all($where, $params, 'id', '', 'position');
				$start = 1;
				foreach($all_pages as $ap)
					\Model\Page::update(['position' => $start++], $ap['id']);

				// reload page after position
				$source_page = \Model\Page::findById((int)$paste_source_page_id);
				$target_page = \Model\Page::findById((int)$paste_target_page_id);

				if($paste_type == 'before' || $paste_type == 'after')
				{
					// get all page ids on branch
					$where = "xcore_page_zone_id = :zone_id and language = :language and xcore_page_id = :page_id and id != {$paste_source_page_id}";
					$params = [':zone_id' => $target_page['xcore_page_zone_id'], ':language' => $target_page['language'], ':page_id' => $target_page['xcore_page_id']];
					$page_under_ids = \Model\Page::allOne($where, $params, 'id', 'position');

					$start = 1;
					foreach($page_under_ids as $page_under_id)
					{
						if($page_under_id == $paste_target_page_id && $paste_type == 'before')
							\Model\Page::update(['xcore_page_id' => $target_page['xcore_page_id'], 'position' => $start++], $paste_source_page_id);

						\Model\Page::update(['position' => $start++], $page_under_id);

						if($page_under_id == $paste_target_page_id && $paste_type == 'after')
							\Model\Page::update(['xcore_page_id' => $target_page['xcore_page_id'], 'position' => $start++], $paste_source_page_id);
					}
				}
				elseif($paste_type == 'children')
				{
					// get max position from parent
					$where = "xcore_page_id = :page_id";
					$params = [':page_id' => $target_page['id']];
					$next_position = (int)\Model\Page::findOne($where, $params, 'position', [], 'position desc') + 1;
					\Model\Page::update(['position' => $next_position, 'xcore_page_id' => $target_page['id']], $source_page['id']);
				}
			}

		}

		return new JsonResponse($this->validator->result());
	}


	/**
	 * render thumbnail page
	 *
	 * @param int $parent_page_id
	 * @param string $full_name
	 * @param array $data
	 *
	 * @return string
	 * @throws ImageResizeException
	 */
	public static function thumbnailsRender(int $parent_page_id, string $full_name, array $data=[]):string
	{
		if(!$parent_page_id)
		{
			$parent_page_id = \Model\Page::getId();
		}

		$sql_where = "xcore_page_id = {$parent_page_id}";
		if(!\Model\User::isLogon() || !\model\User::hasRight('edit', 'page'))
			$sql_where .= " and status = 'published' " ;

		$data = [];
		$data['thumb_width'] = \Core\Config::get('frontend/page/featured/thumbnail_width');
		$data['thumb_height'] = \Core\Config::get('frontend/page/featured/thumbnail_height');

		$sub_pages = \Model\Page::all($sql_where, [], 'id, name, url, type, status, featured_image', '', 'position');
		for($i=0; $i < count($sub_pages); $i++)
		{
			$sub_pages[$i]['thumb_url'] = \Core\Config::get('frontend/page/featured/thumbnail_default_url');

			if(!empty($sub_pages[$i]['featured_image']))
			{
				// apply thumbnail
				if(str_starts_with($sub_pages[$i]['featured_image'], "/public"))
				{
					$image = $sub_pages[$i]['featured_image'];
					$width = \Core\Config::get('frontend/page/featured/thumbnail_width') * 2;
					$height = \Core\Config::get('frontend/page/featured/thumbnail_height') * 2;

					$image_dir = pathinfo($image, PATHINFO_DIRNAME);
					$image_no_extension = pathinfo($image, PATHINFO_FILENAME);
					$image_extension = pathinfo($image, PATHINFO_EXTENSION);

					$img_path = APP_PATH."{$image}";
					$thumbnail_path = APP_PATH."{$image_dir}/{$image_no_extension}-{$width}x{$height}.{$image_extension}";
					
					if(!file_exists($thumbnail_path))
					{
						$img = new \Gumlet\ImageResize($img_path);
						$img->resize($width, $height);
						$img->save($thumbnail_path);
					}

					$sub_pages[$i]['featured_image'] = get_absolute_path($thumbnail_path);
				}

				$sub_pages[$i]['thumb_url'] = $sub_pages[$i]['featured_image'];
			}
		}

		$data['sub_pages'] = $sub_pages;

		$key = 'frontend.page.thumbnails.template';
		if(!($tpl = \Core\Globals::get($key, false)))
		{
			\Core\Globals::set($key, 'thumbnails');
			$tpl = 'thumbnails';
		}

		return View($tpl, $data)->getContent();
	}


	/**
	 * @return array colors
	 */
	public static function getColorsOptions():array
	{
		$colors = [];

		$colors[] = "aliceblue";
		$colors[] = "antiquewhite";
		$colors[] = "aqua";
		$colors[] = "aquamarine";
		$colors[] = "azure";
		$colors[] = "beige";
		$colors[] = "bisque";
		$colors[] = "black";
		$colors[] = "blanchedalmond";
		$colors[] = "blue";
		$colors[] = "blueviolet";
		$colors[] = "brown";
		$colors[] = "burlywood";
		$colors[] = "cadetblue";
		$colors[] = "chartreuse";
		$colors[] = "chocolate";
		$colors[] = "coral";
		$colors[] = "cornflowerblue";
		$colors[] = "cornsilk";
		$colors[] = "crimson";
		$colors[] = "cyan";
		$colors[] = "darkblue";
		$colors[] = "darkcyan";
		$colors[] = "darkgoldenrod";
		$colors[] = "darkgray";
		$colors[] = "darkgreen";
		$colors[] = "darkkhaki";
		$colors[] = "darkmagenta";
		$colors[] = "darkolivegreen";
		$colors[] = "darkorange";
		$colors[] = "darkorchid";
		$colors[] = "darkred";
		$colors[] = "darksalmon";
		$colors[] = "darkseagreen";
		$colors[] = "darkslateblue";
		$colors[] = "darkslategray";
		$colors[] = "darkturquoise";
		$colors[] = "darkviolet";
		$colors[] = "deeppink";
		$colors[] = "deepskyblue";
		$colors[] = "dimgray";
		$colors[] = "dodgerblue";
		$colors[] = "firebrick";
		$colors[] = "floralwhite";
		$colors[] = "forestgreen";
		$colors[] = "fuchsia";
		$colors[] = "gainsboro";
		$colors[] = "ghostwhite";
		$colors[] = "gold";
		$colors[] = "goldenrod";
		$colors[] = "gray";
		$colors[] = "green";
		$colors[] = "greenyellow";
		$colors[] = "honeydew";
		$colors[] = "hotpink";
		$colors[] = "indianred";
		$colors[] = "indigo";
		$colors[] = "ivory";
		$colors[] = "khaki";
		$colors[] = "lavender";
		$colors[] = "lavenderblush";
		$colors[] = "lawngreen";
		$colors[] = "lemonchiffon";
		$colors[] = "lightblue";
		$colors[] = "lightcoral";
		$colors[] = "lightcyan";
		$colors[] = "lightgoldenrodyellow";
		$colors[] = "lightgray";
		$colors[] = "lightgreen";
		$colors[] = "lightpink";
		$colors[] = "lightsalmon";
		$colors[] = "lightseagreen";
		$colors[] = "lightskyblue";
		$colors[] = "lightslategray";
		$colors[] = "lightsteelblue";
		$colors[] = "lightyellow";
		$colors[] = "lime";
		$colors[] = "limegreen";
		$colors[] = "linen";
		$colors[] = "magenta";
		$colors[] = "maroon";
		$colors[] = "mediumaquamarine";
		$colors[] = "mediumblue";
		$colors[] = "mediumorchid";
		$colors[] = "mediumpurple";
		$colors[] = "mediumseagreen";
		$colors[] = "mediumslateblue";
		$colors[] = "mediumspringgreen";
		$colors[] = "mediumturquoise";
		$colors[] = "mediumvioletred";
		$colors[] = "midnightblue";
		$colors[] = "mintcream";
		$colors[] = "mistyrose";
		$colors[] = "moccasin";
		$colors[] = "navajowhite";
		$colors[] = "navy";
		$colors[] = "oldlace";
		$colors[] = "olive";
		$colors[] = "olivedrab";
		$colors[] = "orange";
		$colors[] = "orangered";
		$colors[] = "orchid";
		$colors[] = "palegoldenrod";
		$colors[] = "palegreen";
		$colors[] = "paleturquoise";
		$colors[] = "palevioletred";
		$colors[] = "papayawhip";
		$colors[] = "peachpuff";
		$colors[] = "peru";
		$colors[] = "pink";
		$colors[] = "plum";
		$colors[] = "powderblue";
		$colors[] = "purple";
		$colors[] = "rebeccapurple";
		$colors[] = "red";
		$colors[] = "rosybrown";
		$colors[] = "royalblue";
		$colors[] = "saddlebrown";
		$colors[] = "salmon";
		$colors[] = "sandybrown";
		$colors[] = "seagreen";
		$colors[] = "seashell";
		$colors[] = "sienna";
		$colors[] = "silver";
		$colors[] = "skyblue";
		$colors[] = "slateblue";
		$colors[] = "slategray";
		$colors[] = "snow";
		$colors[] = "springgreen";
		$colors[] = "steelblue";
		$colors[] = "tan";
		$colors[] = "teal";
		$colors[] = "thistle";
		$colors[] = "tomato";
		$colors[] = "turquoise";
		$colors[] = "violet";
		$colors[] = "wheat";
		$colors[] = "white";
		$colors[] = "whitesmoke";
		$colors[] = "yellow";



		return $colors;
	}


	/**
	 * get page content plugin
	 *
	 * @param int $id
	 * @return string
	 */
	public static function getContent(int $id):string
	{
		$content = \Model\Page::findById($id)['content'];
		return $content;
	}


}


