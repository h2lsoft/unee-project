<?php

use \Core\Router;

// HOMEPAGE
Router::get('/', function(){
	return \Model\Page::render('/', "is_homepage='yes'");
}, 'homepage');


// HOMEPAGE::LANG
$langs = \Core\Config::get('frontend/langs');
for($i=0; $i < count($langs); $i++)
{
	$cur_lang = $langs[$i][0];

	\Model\Page::$route_tmp_current_lang = $cur_lang;
	\Model\Page::$route_tmp_current_lang_is_default = ($i == 0) ? true : false;


	Router::get("/{$cur_lang}/", function(){

		$lang = \Model\Page::$route_tmp_current_lang;

		$urls = ["/{$lang}/"];
		if(\Model\Page::$route_tmp_current_lang_is_default)
			$urls[] = "/";

		return \Model\Page::render($urls, "language = :lang", [':lang' => $lang]);

	}, "homepage-{$cur_lang}");
}







