<?php

namespace Plugin\Core_Frontend;

use Core\Config;
use Model\Article;

class ArticleController extends \Core\Controller {

	public string $table = 'xcore_article';
	public string $object_label = 'article';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table);
		$datagrid->qOrderBy('date desc');

		// force language
		$datagrid->searchAddNumber('id');

		$language_default = get('language', \Core\Config::get('frontend/langs')[0][0]);
		if(!$datagrid->userIsSearching('language'))
			$datagrid->searchSet('language', $language_default);

		// search
		$langs = \Core\Config::get('frontend/langs');
		$lang_option = [];
		foreach($langs as $lang)
			$lang_option[] = ['value' => $lang[0], 'label' => $lang[1]];

		$datagrid->searchAddSelect('language', '', $lang_option);
		$datagrid->searchAddSelectSql('xcore_user_id', 'author', "", "CONCAT(lastname, ' ', firstname)");
		$datagrid->searchAddSelectSql('type');
		$datagrid->searchAddText('title');
		$datagrid->searchAddBoolean('archived');
		$datagrid->searchAddTagManager();


		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnImage('header_image', 'Image', false, 'border thumbnail', false, "edit/[ID]");
		$datagrid->addColumn('type', '', true, 'min');
		$datagrid->addColumnDate('date', '', '', true);
		$datagrid->addColumnHtml('title', '', true, '');
		$datagrid->addColumnTags();
		$datagrid->addColumnHtml('status', '', false, 'min center');
		$datagrid->addColumnNote('note');
		$datagrid->addColumnHtml('url', '', false, 'min');

		$datagrid->setOrderByInit('date', 'desc');


		// hookData
		$datagrid->hookData(function($row){

			if(empty($row['header_image']))
				$row['header_image'] = get_absolute_path(\Core\Config::get('dir/article')."/0.jpg");


			// status
			$statuses = \Core\Config::get('frontend/blog/status');
			$icon = $class = $style = '';
			foreach($statuses as $s)
			{
				if($s['value'] == $row['status'])
				{
					if(!empty($s['icon']))$icon = "<i class='{$s['icon']} me-2'></i>";
					if(!empty($s['class']))$class = $s['class'];
					if(!empty($s['style']))$style = $s['style'];
					if(!empty($s['label']))$row['status'] = $s['label'];
				}
			}

			$row['status'] = \Core\Html::Badge($icon.'<i18n>'.$row['status'].'</i18n>', '', $class, ['style' => $style]);

			$url = \Model\Article::getUrl($row['id'], $row['url'], $row['language'], $row['title']);
			$row['url'] = \Core\Html::A(\Core\Html::Icon("bi bi-globe"), $url, ['target' => '_blank']);


			if(!empty($row['category']))
				$row['title'] = \Core\Html::Badge($row['category'])." {$row['title']}";


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

		$theme_url = "/theme/".\Core\Config::get('frontend/theme');

		$form_attr = (get('_popup') != 1) ? [] : ['data-success-notification' => 'ok'];
		$form = new \Component\Form("", $form_attr);

		$form->loadAssetsJs(['form.js']);
		$form->linkController($this, $id);

		$form->addText('type', '', true, ['class' => 'upper'])->datalist();

		$form->addFileImage('header_image', 'image', false, \Core\Config::get("dir/article"));
		$form->addDate('date', '', true)->setValue(now('', false));
		$form->addText('title', '', true, ['class' => 'ucfirst']);


		$form->addTabMenu();

		// CONTENT *****************************************************************************************************
		$form->addTab("Content");
		$form->addHtmlarea('content', '', true, ['style' => 'height:250px', 'data-blockee-css-file' => "{$theme_url}/assets/css/@blockee.css"]);
		// $form->addText('source', '', false, ['class' => 'ucfirst'])->datalist();
		$form->addTabEnd();

		// OPTION ******************************************************************************************************
		$form->addTab('Options');

		// authors
		$sql_author_where = <<<SQL
				active = 'yes' and 
     			(
				    xcore_group_id = 1 or
				    xcore_group_id IN(SELECT xcore_group_id FROM xcore_group_right WHERE deleted = 'no' AND xcore_plugin_id = (SELECT id FROM xcore_plugin WHERE name IN('article') and deleted = 'no'))
				)
SQL;
		$allowed_authors = \Model\User::all($sql_author_where, [], "id as value, CONCAT(lastname,' ', firstname) as label", "", "lastname, firstname");
		$form->addSelect('xcore_user_id', 'author', false, $allowed_authors)->setValue(\Model\User::getUID());

		// language
		$langs = \Core\Config::get('frontend/langs');
		$lang_option = [];
		foreach($langs as $lang)
			$lang_option[] = ['value' => $lang[0], 'label' => $lang[1]];

		$form->addSelect('language', '', true, $lang_option)->setValue($langs[0][0]);



		$form->addText('resume', '', false)->setHelp("Between 120 and 155 characters for SEO");





		$form->addHr();

		$form->addSwitch('archived', '', false);
		$form->addDatetime('archived_date', 'archived date', false);

		$form->addTabEnd();

		// Note ********************************************************************************************************
		$form->addTab('Note');
		$form->addTextarea('note', '', false, ['style' => 'height:250px']);
		$form->addTabEnd();


		$form->addTabMenuEnd();


		$form->addHr();
		$form->addTagManager();

		$form->addHr();
		$form->addRadio('status', '', true, \Core\Config::get('frontend/blog/status'))->setValue('draft');
		$form->addDatetime('publication_date', 'Publication date', false, ['class' => 'text-center'])->setInputSize(2);



		// validation
		if($form->isSubmitted())
		{
			$form->validator->input('archived_date')->requiredIf('archived', 'yes');

			// valid
			if($form->isValid())
			{
				$form->save();
			}

			return $form->json();
		}

		return $form->render();
	}


	/**
	 * frontend render
	 *
	 * @param string $template
	 * @return string
	 */
	public static function render(string $template='article'):string
	{
		$author_column = \Core\Config::get("frontend/blog/author");
		$fields = "
					*,
					(select login from xcore_user where id = xcore_user_id) as author_login,
					(select {$author_column} from xcore_user where id = xcore_user_id) as author,
					(select avatar from xcore_user where id = xcore_user_id) as author_avatar_image
		";

		// check article exists
		$article_id = \Core\Request::getUrlParameter('id');
		$article = \Model\Article::findById($article_id, $fields);

		$article['avatar_image'] = \Model\User::getAvatarBadge($article['xcore_user_id'], $article['author_avatar_image'], $article['author']);
		$article['dateX'] = db2date($article['date'], \Core\Config::get("frontend/blog/date_format"), \Model\Page::currentGet('language'));

		// reload for user logon
		$article['thumbnail_url'] = \Model\Article::getThumbnailUrl($article['id'], $article['header_image'], (\Model\User::isLogon() && \Model\User::hasRight('edit', 'article')));
		$article['thumbnail_width'] = \Core\Config::get("frontend/blog/thumbnail/width");
		$article['thumbnail_height'] = \Core\Config::get("frontend/blog/thumbnail/height");

		$article['tags'] = [];
		if(\Core\Config::get("frontend/blog/show_tags"))
			$article['tags'] = DB()->query("SELECT DISTINCT tag FROM xcore_tag WHERE deleted = 'no' AND signature = 'xcore_article' AND record_id = {$article_id} ORDER BY tag")->fetchAllOne();

		\Model\Page::addPattern('@article_title', $article['title']);
		\Model\Page::$page_edit_url = \Core\Config::get("backend/dirname")."/article/edit/{$article_id}/";

		$data = [];
		$data['blog_listing_url'] = \Core\Config::get('frontend/blog/listing/url');
		$data['blog_search_author_url'] = str_replace("{author}", $article['author_login'], \Core\Config::get('frontend/blog/listing/author_search_url'));
		$data['article'] = $article;

		return View($template, $data)->getContent();
	}

	/**
	 * frontend plugin
	 *
	 * @param int $nb_article
	 * @param bool $pagination
	 * @param string $fields_added
	 * @param string $sql_added
	 * @param array $sql_parameters
	 * @return string
	 */
	public static function listing(int $nb_article=10, bool $pagination=true, string $fields_added = "", string $sql_added = "", array $sql_parameters=[], string $article_class="", bool $wrapper=true, int|bool $str_cut=false, array $tags=[]):string
	{
		if(!$str_cut)
			$str_cut = \Core\Config::get('frontend/blog/resume_nb_chars');

		$author_column = \Core\Config::get("frontend/blog/author");

		$fields = "
					*,
					(select login from xcore_user where id = xcore_user_id) as author_login,
					(select {$author_column} from xcore_user where id = xcore_user_id) as author
		";

		if(!empty($fields_added))
			$fields .= ", {$fields}";

		$thumbnail_width = \Core\Config::get('frontend/blog/thumbnail/width');
		$thumbnail_height = \Core\Config::get('frontend/blog/thumbnail/height');

		$data = [];
		$data['wrapper'] = $wrapper;
		$data['pagination'] = $pagination;
		$data['article_class'] = $article_class;
		$data['blog_listing_url'] = Config::get('frontend/blog/listing/url');
		if(get('author'))
			$data['blog_listing_url'] .= 'auteur/'.get('author').'/';

		$author_search_url = Config::get('frontend/blog/listing/author_search_url');

		$params = [];

		if(!empty($sql_added) && !str_starts_with(trim($sql_added), 'and'))
			$sql_added = " and {$sql_added}";

		if(get('author'))
		{
			$sql_added = " and xcore_user_id = (select id from xcore_user where login = :author_login limit 1)";
			$params[':author_login'] = get('author');
		}



		$sql = "SELECT
							{$fields} 
					FROM
							xcore_article
					WHERE
							deleted = 'no' and 
							status = 'published'
							{$sql_added} 
					ORDER BY
							date DESC";
		$articles = DB()->paginate($sql, $params, get('page'), $nb_article);

		$articles2 = [];
		foreach($articles['data'] as $article)
		{
			$article['class'] = \Core\Config::get('frontend/blog/listing/class');
			$article['image_class'] = \Core\Config::get('frontend/blog/listing/image_class');
			$article['resume_class'] = \Core\Config::get('frontend/blog/listing/resume_class');
			$article['thumbnail_width'] = $thumbnail_width;
			$article['thumbnail_height'] = $thumbnail_height;

			$article['resume'] = str_cut(strip_tags($article['content']), $str_cut, '...');
			$article['link'] = \Model\Article::getUrl($article['id'], $article['url'], $article['language'], $article['title']);
			$article['thumbnail_url'] = \Model\Article::getThumbnailUrl($article['id'], $article['header_image']);
			$article['dateX'] = db2date($article['date'], \Core\Config::get("frontend/blog/date_format"), \Model\Page::currentGet('language'));
			$article['author_search_url'] = str_replace('{author}', $article['author_login'], $author_search_url);

			$articles2[] = $article;
		}

		$articles['data'] = $articles2;

		$data['articles'] = $articles['data'];
		$data['total'] = $articles['total'];

		$data['last_page'] = $articles['last_page'];
		$data['current_page'] = $articles['current_page'];
		$data['page_start'] = $articles['page_start'];
		$data['page_end'] = $articles['page_end'];

		$listing_url = \Core\Config::get('frontend/blog/listing/url');
		if(get('author'))
			$listing_url .= 'auteur/'.get('author').'/';

		$data['listing_url_first'] = $listing_url;
		$data['listing_url_previous'] = ($data['current_page'] == 2) ? $listing_url : "{$listing_url}/page/".($articles['current_page'] - 1)."/";
		$data['listing_url_next'] = "{$listing_url}page/".($articles['current_page'] + 1)."/";
		$data['listing_url'] = "{$listing_url}page/";






		return View('listing.twig', $data)->getContent();

	}

}
