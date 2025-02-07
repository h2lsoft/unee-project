<?php

namespace Core;

class File
{

	/**
	 * Create tmp file in tmp directory
	 * @param string $dir_name (in current directory)
	 * @param string $file_prefix
	 * @param string $file_suffix
	 * @param string $contents
	 * @return string
	 */
	public static function tmpCreate(string $dir_name, string $file_prefix='', string $file_suffix='', string $contents=""):string
	{
		$path = \Core\Config::get('dir/tmp')."/{$dir_name}";
		if(!is_dir($path)) mkdir($path, 0777);
		
		$filename = $path."/".uniqid($file_prefix).$file_suffix;
		file_put_contents($filename, $contents);
		
		return $filename;
		
	}

	public static function get($file_path)
	{
		if(!file_exists($file_path))
			return new \Core\Response("Error : file not found", 404);

		$response = new Response(file_get_contents($file_path));
		$response->headers->set('Content-Type', 'application/pdf');

		return $response;

	}

}