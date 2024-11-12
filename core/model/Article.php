<?php

namespace Model;

use Gumlet\ImageResize;

class Article extends \Core\Entity
{
	public static string $table = 'xcore_article';


	public static function getUrl(int $id, string $url, string $language="", string $title=""):string
	{
		$uri = \Core\Config::get("frontend/blog/url");

		$slug = !empty($url) ? $url : slugify($title);

		$uri = str_replace('{id}', $id, $uri);
		$uri = str_replace('{slug}', $slug, $uri);
		$uri = str_replace('{locale}', $language, $uri);

		return $uri;
	}


	public static function getThumbnailUrl(int $id, string $image="", $force_reload=false):string
	{
		if(empty($image))
		{
			$image = \Core\Config::get('dir/article')."/".\Core\Config::get('frontend/blog/thumbnail/default');
			$image = get_absolute_path($image);
		}
		else
		{
			// create thumbnail
			$width = \Core\Config::get('frontend/blog/thumbnail/width') * 2;
			$height = \Core\Config::get('frontend/blog/thumbnail/height') * 2;

			$image_no_extension = pathinfo($image, PATHINFO_FILENAME);
			$image_extension = pathinfo($image, PATHINFO_EXTENSION);

			$img_path = APP_PATH."{$image}";
			$thumbnail_path = \Core\Config::get('dir/article')."/{$image_no_extension}-{$width}x{$height}.{$image_extension}";
			if(!file_exists($thumbnail_path) || $force_reload)
			{
				$i = new ImageResize($img_path);
				$i->resize($width, $height);
				$i->save($thumbnail_path);
			}

			$image = get_absolute_path($thumbnail_path);
		}

		return $image;
	}

		


}