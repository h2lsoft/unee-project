<?php

namespace Model;

class Zone extends \Core\Entity
{
	static public string $table = 'xcore_page_zone';


	static function getHtml(int $zone_id, string $language='')
	{
		if(empty($language))$language = \Model\Page::currentGet('language');
		$str = "<ul data-zone-id='{$zone_id}'>\n";

		// pages
		$sql_added = "menu_visible='yes'";

		// display for user
		if(!User::hasRight('edit', 'page'))
			$sql_added .= " and status='published' ";

		$pages = \Model\Page::getAll($zone_id, $language, 0, '', $sql_added);

		foreach($pages as $page)
		{
			$str .= "<li data-page-id=\"{$page['id']}\">\n";

			$target = '';
			if($page['type'] == 'url external')
				$target = 'target="_blank"';

			$str .= "<a href=\"{$page['url']}\" {$target}>{$page['name']}</a>\n";
			$str .= "</li>";
		}


		$str .= "</ul>";

		return $str;
	}


}