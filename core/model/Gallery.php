<?php

namespace Model;

use Gumlet\ImageResize;

class Gallery extends \Core\Entity
{
	public static string $table = 'xcore_gallery';
	public static string $where_added = "";


	public static function getThumbnailUrl(int $id, string $image=""):string
	{
		if(empty($image))
		{
			$image = \Core\Config::get('dir/gallery_card')."/".\Core\Config::get('frontend/gallery/thumbnail/default');
			$image = get_absolute_path($image);
		}
		else
		{
			// create thumbnail
			$width = \Core\Config::get('frontend/gallery/thumbnail/width') * 2;
			$height = \Core\Config::get('frontend/gallery/thumbnail/height') * 2;

			$image_no_extension = pathinfo($image, PATHINFO_FILENAME);
			$image_extension = pathinfo($image, PATHINFO_EXTENSION);

			$img_path = APP_PATH."{$image}";
			$thumbnail_path = \Core\Config::get('dir/gallery_card')."/{$image_no_extension}-{$width}x{$height}.{$image_extension}";

			if(!file_exists($thumbnail_path))
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