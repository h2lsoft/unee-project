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
	\Model\Page::$route_tmp_current_lang_is_default = ($i == 0);


	Router::get("/{$cur_lang}/", function(){
		$lang = \Model\Page::$route_tmp_current_lang;
		$urls = ["/{$lang}/"];
		if(\Model\Page::$route_tmp_current_lang_is_default)
			$urls[] = "/";

		return \Model\Page::render($urls, "language = :lang", [':lang' => $lang]);
	}, "homepage-{$cur_lang}");


	// by id => /@locale/@id.html
	Router::get("/{$cur_lang}/{slug}.html", function($slug){
		$id = explode("-", $slug);
		$id = (int)end($id);
		return \Model\Page::render("", "id = :id", [':id' => $id]);
	});
}

// BLOG enabled
if(\Core\Config::get('frontend/blog/enabled'))
{
	include __DIR__.'/frontend_blog.php';
}

// CUSTOM URL **********************************************************************************************************

// CUSTOM URL> level 1
Router::get("/{slug}/", function($slug){

	if($slug == \Core\Config::get('backend/dirname'))return;

	$cur_uri = explode('?', $_SERVER['REQUEST_URI'])[0];
	return \Model\Page::render("", "url = :url", [':url' => $cur_uri]);
});

// CUSTOM URL> level 2
Router::get("/{slug}/{slug2}/", function($slug, $slug2){

	if($slug == \Core\Config::get('backend/dirname'))return;

	$cur_uri = explode('?', $_SERVER['REQUEST_URI'])[0];
	return \Model\Page::render("", "url = :url", [':url' => $cur_uri]);
});

// CUSTOM URL> level 3
Router::get("/{slug}/{slug2}/{slug3}/", function($slug, $slug2, $slug3){

	if($slug == \Core\Config::get('backend/dirname'))return;

	$cur_uri = explode('?', $_SERVER['REQUEST_URI'])[0];
	return \Model\Page::render("", "url = :url", [':url' => $cur_uri]);
});





