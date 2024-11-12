<?php
namespace Model;

use Symfony\Component\HttpFoundation\Response;



class Page extends \Core\Entity
{
	public static string $table = 'xcore_page';
	public static string $route_tmp_current_lang = '';
	public static bool $route_tmp_current_lang_is_default = false;


	public static function render(string|array $url, string $where_add="", array $where_add_params=[]):Response
	{

		$where = "";

		$where = "status = 'published'";
		$where_params = [];

		if(!is_array($url))
		{
			$where .= " and url = :url ";
			$where_params = [':url' => $url];
		}
		else
		{
			$urls = '"'.join('", "', $url).'"';
			$where .= " and url IN({$urls}) ";
		}


		if(!empty($where_add))
			$where .= " and {$where_add}";

		if(count($where_add_params))
			$where_params = array_merge($where_params, $where_add_params);

		$page = Page::findOne($where, $where_params, "*");

		// default values
		$theme_uri = "/theme/".\Core\Config::get('frontend/theme');
		$theme_path = $theme_uri;
		$template = (empty($page['Template'])) ? 'index.twig' : "{$page['Template']}.twig";
		$status = 200;
		$data = [];

		// error 404
		if(!$page)
		{
			$status = 404;
			$template = '@error404.twig';
		}
		else
		{
			$data['page'] = $page;
			$data['theme_path'] = $theme_path;
		}

		$view = "{$theme_uri}/{$template}";

		// parsing page variables
		$response = View($view, $data, $status);
		if(!$page)
		{

		}
		else
		{
			$content = $response->getContent();

			$content = str_replace("@theme_url", $theme_uri, $content);
			$content = str_replace("@theme_assets_css", "{$theme_uri}/assets/css", $content);
			$content = str_replace("@theme_assets_js", "{$theme_uri}/assets/js", $content);
			$content = str_replace("@theme_assets", "{$theme_uri}/assets", $content);
			$content = str_replace("@theme", \Core\Config::get('frontend/theme'), $content);

			foreach($page as $var => $val)
			{
				$content = str_replace("@page_{$var}", $val, $content);
			}

			$response->setContent($content);
		}


		return $response;

	}


	/**
	 * get url from page
	 *
	 * @param string $url
	 * @param string $language
	 * @param int $id
	 * @param string $name
	 * @return string
	 */
	public static function getUrl(string $url='', string $language='', int $id=0, string $name=''):string
	{
		if(!empty($url))return $url;

		$slug = slugify($name);
		$uri = "/{$language}/{$slug}-{$id}.html";

		return $uri;
	}


	/**
	 * @param int $zone_id
	 * @param string $language
	 * @return array|false
	 */
	public static function getAll(int $zone_id, string $language, int $xcore_page_id=0, string $fields="", string $sql_added="", array $params_added=[]):array|false
	{
		$pages = [];

		if(empty($fields))$fields = "id, name, status, xcore_page_id";
		if(!empty($sql_added))$sql_added = "and {$sql_added}";

		$params = $params_added;
		$params[':zone_id'] = $zone_id;
		$params[':language'] = $language;
		$params[':xcore_page_id'] = $xcore_page_id;

		$records = self::all("xcore_page_zone_id = :zone_id and language = :language and xcore_page_id = :xcore_page_id {$sql_added}", $params, $fields, "", "position");
		foreach($records as $p)
		{
			$p['_childrens'] = self::getAll($zone_id, $language, $p['id'], $fields, $sql_added, $params_added);
			$pages[] = $p;
		}


		return $pages;
	}


}