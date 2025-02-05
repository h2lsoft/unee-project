<?php

namespace Plugin\Core;

use Symfony\Component\Finder\Finder;
use Core\Controller;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Live_UpdaterController extends Controller {

	public static function widgetRender(): string
	{

		$uri = \Core\Config::get('live_updater/url')."/?canal=".\Core\Config::get('live_updater/canal');
		$versions = @file_get_contents($uri);
		$versions = json_decode($versions);
		$last_version = @$versions[0];

		$content = "";
		if(!empty($last_version) && version_compare(\Model\Live_Updater::$version, $last_version, '<'))
		{
			$data = [];
			$data['current_version'] = \Model\Live_Updater::$version;
			$content = View('widget', $data, false);
		}


		return $content;
	}

	/**
	 * @route /@backend/live-updater/
	 */
	public static function execute()
	{
		// get version
		$versions = \Model\Live_Updater::getVersions();
		$next_version = \Model\Live_Updater::$version;

		$versions_asc = array_reverse($versions);
		foreach($versions_asc as $current)
		{
			if(version_compare($current, \Model\Live_Updater::$version, '>'))
			{
				$next_version = $current;
				break;
			}
		}

		// treatment
		$console = "";
		if(get('ajaxer-action') == 'update')
			$console = self::update($next_version);

		$data = [];
		$data['core_version'] = \Model\Live_Updater::$version;
		$data['core_canal'] = \Core\Config::get('live_updater/canal');
		$data['core_last_version'] = $versions[0];
		$data['next_version'] = $next_version;
		$data['console_output'] = $console;

		return View('template', $data);
	}

	public static function update($next_version)
	{
		if(version_compare($next_version, \Model\Live_Updater::$version, '<='))
			return "";

		// check requirements
		$output = "";
		if(!extension_loaded('zip'))
		{
			$output = "<error>extension `php_zip` not active</error>";
			return $output;
		}

		if(!ini_get('allow_url_fopen'))
		{
			$output = "<error>php configuration `allow_url_fopen` not active</error>";
			return $output;
		}

		// entity file
		$entity_live_updater_path = APP_PATH.'/core/model/Live_Updater.php';
		if(!is_writable($entity_live_updater_path))
		{
			$output = "<error>file `/core/model/Live_Updater.php` is not writable</error>";
			return $output;
		}

		// get update-zip
		$zip_url = \Core\Config::get('live_updater/url')."/".\Core\Config::get('live_updater/canal')."/{$next_version}/{$next_version}.zip";
		if(($zip_contents = @file_get_contents($zip_url)) === FALSE)
		{
			$output = "<error>can't download zip `{$zip_url}`</error>";
			return $output;
		}

		// zip extract
		$destination_dir = \Core\Config::get('dir/tmp')."/_live-updater/{$next_version}/{$next_version}.zip";
		$tmp_dir = dirname($destination_dir);
		if(!is_dir($tmp_dir))
			@mkdir($tmp_dir, 0755, true);

		if(!is_dir($tmp_dir))
		{
			$output = "<error>can't create tmp dir `{$tmp_dir}`</error>";
			return $output;
		}

		if(!@file_put_contents($destination_dir, $zip_contents))
		{
			$output = "<error>can't copy zip `{$destination_dir}`</error>";
			return $output;
		}


		// extract zip content
		$zip = new \ZipArchive();
		if($zip->open($destination_dir) === FALSE)
		{
			$output = "<error>can't open zip `{$destination_dir}`</error>";
			return $output;
		}

		$zip->extractTo(dirname($destination_dir));
		$zip->close();

		if(!@unlink($destination_dir))
		{
			$output = "<error>can't delete zip `{$destination_dir}`</error>";
			return $output;
		}

		// testing file creation
		$target_files = [];
		$target_files_errors = false;

		$finder = new Finder();
		$finder->files()->in($tmp_dir);
		foreach($finder as $file)
		{
			$absoluteFilePath = $file->getRealPath();
			$fileNameWithExtension = $file->getRelativePathname();
			$file_path = APP_PATH.'/'.$fileNameWithExtension;
			$dir_path = dirname($file_path);

			// dir exists
			if(!empty(dirname($fileNameWithExtension)) && is_writable($dir_path))
			{
				if(!is_dir($dir_path) && !@mkdir($dir_path, 0777, true))
				{
					$output .= "\n<error>>> dir is not writable `{$dir_path}`</error>";
					$target_files_errors = true;
				}

				if(file_exists($file_path) && !is_writable($file_path))
				{
					$output .= "\n<error>>> file is not writable `{$file_path}`</error>";
					$target_files_errors = true;
				}
				else
				{
					$target_files[] = ['source' => $absoluteFilePath, 'target' => $file_path];
				}
			}
		}

		// error found
		if($target_files_errors)
			return trim($output);

		foreach($target_files as $target_file)
		{
			if(!@copy($target_file['source'], $target_file['target']))
			{
				$output .= "\n<error>>>> copy error `{$target_file['source']}` to `{$target_file['target']}`</error>";
				$target_files_errors = true;
			}
			else
			{
				$output .= "\n>>> copy `{$target_file['source']}` to `{$target_file['target']}`";
			}
		}



		// execute file /core/module/live-updater/@cmd/{$next_version}.inc.php
		if(file_exists(APP_PATH."/core/module/core/live-updater/@cmd/{$next_version}.inc.php"))
			include(APP_PATH."/core/module/core/live-updater/@cmd/{$next_version}.inc.php");

		if($target_files_errors)
			return trim($output);

		// remove dir
		$cmd = "rmdir -rf {$tmp_dir}";
		if(strtolower(PHP_OS_FAMILY) === 'windows')
			$cmd = "rmdir /s /q \"{$tmp_dir}\"";
		shell_exec($cmd);

		// update version
		$current_version = \Model\Live_Updater::$version;
		$contents = file_get_contents($entity_live_updater_path);
		$contents = str_replace("version = '{$current_version}';", "version = '{$next_version}';", $contents);

		if(@file_put_contents($entity_live_updater_path, $contents) === false)
		{
			$output .= "\n<error>`{$entity_live_updater_path}` is not updated from version {$current_version} to {$next_version}</error>";
			return trim($output);
		}

		return trim($output);
	}




}