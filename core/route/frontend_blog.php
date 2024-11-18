<?php

use Core\Router;

// article> listing
$article_pattern = \Core\Config::get('frontend/blog/url');

Router::get($article_pattern, function($slug, $id){

	// check article
	$article = \Model\Article::findById($id);
	if(!$article || slugify($article['title']) != $slug || $article['status'] != 'published')
	{
		return \Model\Page::error404("article not found");
	}

	\Model\Page::$page_edit_url = "/@backend/article/edit/{$article['id']}/";

	$container = \Core\Config::get('frontend/blog/page_container_id');
	return \Model\Page::render("", "id = :id", [':id' => $container], ['article' => $article]);
});

// article> listing> page
$article_pattern = \Core\Config::get('frontend/blog/listing/search_page_url');
Router::get($article_pattern, function(int $page){
	$_GET['page'] = $page;
	return \Model\Page::render("", "url = :url", [':url' => \Core\Config::get('frontend/blog/listing/url')]);
});

// article> listing> author
$article_pattern = \Core\Config::get('frontend/blog/listing/author_search_url');
Router::get($article_pattern, function(string $author){
	$_GET['author'] = $author;
	return \Model\Page::render("", "url = :url", [':url' => \Core\Config::get('frontend/blog/listing/url')]);
});

// article> listing> author> page
$article_pattern = \Core\Config::get('frontend/blog/listing/author_search_page_url');
Router::get($article_pattern, function(string $author, int $page){
	$_GET['author'] = $author;
	$_GET['page'] = $page;
	return \Model\Page::render("", "url = :url", [':url' => \Core\Config::get('frontend/blog/listing/url')]);
});

