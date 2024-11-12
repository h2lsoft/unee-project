<?php

namespace Plugin\Core;

use Core\JsonResponse;
use \Gumlet\ImageResize;
use Core\Response;
use Symfony\Component\Finder\Finder;

class File_ManagerController extends \Core\Controller {


	/**
	 * @route /@backend/file-manager/    {name:"backend-file-manager"}
	 */
	public function index():Response
	{
		$error_path = false;

		$this->loadAssetsCss(['style.css']);
		$this->loadAssetsJs(['func.js', 'init.js']);

		$root_path = \Core\Config::get('file_manager/dir/root');

		$path = urldecode(get('path', ''));
		$path = trim($path, '/');

		if(!self::checkPath($path, $root_path))
			$error_path = true;

		$root_path_completed =	(empty($path)) ? $root_path : "{$root_path}/{$path}";

		$mode = get('mode', 'standalone', ['standalone']);
		$target = get('target');
		$filter = get('filter', '', ['', 'image', 'audio', 'video', 'document', 'archive']);
		$sort = get('sort', 'type-asc', ['date-desc', 'date-asc', 'name-asc', 'name-desc', 'type-asc', 'type-desc', 'size-asc', 'size-desc']);



		$files = [];
		$folders = [];

		// dropzone
		$dropzone_exts_allowed = [];

		$allowed_exts = \Core\Config::get('file_manager/upload/allowed_ext');
		if(count($allowed_exts))
		{
			$exts = [];
			foreach($allowed_exts as $ext)
			{
				if(!str_starts_with($ext, 'filter:'))
				{
					$exts[] = $ext;
				}
				else
				{
					$ext = str_erase('filter:', $ext);
					$exts = array_merge($exts, \Core\Config::get("file_manager/filters/{$ext}"));
				}
			}

			$dropzone_exts_allowed_str = '.'.join(',.',$exts);
		}




		if(!$error_path)
		{
			$finder = new Finder();
			$finder->in($root_path_completed)->ignoreDotFiles(true)->depth("== 0");

			// add exclude
			foreach(\Core\Config::get('file_manager/security/excludes') as $exclude)
			{
				$finder->exclude($exclude);
			}


			$filter_match = '';
			if(!empty($filter))
			{
				$exts_pipe = \Core\Config::get("file_manager/filters/{$filter}");
				$exts_pipe = join('|', $exts_pipe);
				$filter_match = '/\.('.$exts_pipe.')$/';
			}


			if($sort == 'date-desc')$finder->sortByModifiedTime()->reverseSorting();
			if($sort == 'date-asc')$finder->sortByModifiedTime();

			if($sort == 'name-desc')$finder->sortByName()->reverseSorting();
			if($sort == 'name-asc')$finder->sortByName();

			if($sort == 'type-asc')$finder->sortByType();
			if($sort == 'type-desc')$finder->sortByType()->reverseSorting();

			if($sort == 'size-asc')$finder->sortBySize();
			if($sort == 'size-desc')$finder->sortBySize()->reverseSorting();



			foreach($finder as $item)
			{
				$tmp = [];
				$tmp['name'] = $item->getFilename();

				$file_info = pathinfo($tmp['name']);
				$tmp['name_noext'] = $file_info['filename'];
				$tmp['size'] = human_filesize($item->getSize(), 0);
				$tmp['uri'] = get_absolute_path($item->getRealPath());

				$format_date = empty(\Core\Session::get('auth.format_date')) ? \Core\Config::get('format/datetime') : \Core\Session::get('auth.format_datetime');
				$tmp['updated'] = date($format_date, $item->getATime());

				$tmp['mime'] = mime_content_type($item->getRealPath());
				$tmp['link'] = \Core\Config::get('url').get_absolute_path($item->getRealPath());
				$tmp['link_relative'] = get_absolute_path($item->getRealPath());
				$tmp['details'] = "-";
				$tmp['file_extension'] = file_get_extension($tmp['link_relative']);


				if($item->isDir())
				{
					$tmp['type'] = 'folder';
					$tmp['thumbnail_uri'] = "@assets_img/folder.png";
					$tmp['icon_uri'] = "@assets_img/folder.png";
					$tmp['size'] = '-';
					$tmp['link_relative'] .= '/';
					$files[] = $tmp;
				}
				else
				{
					if(!empty($filter) && !preg_match($filter_match, $item->getFilename()))
						continue;



					$tmp['type'] = 'file';
					$tmp['extension'] = $item->getExtension();
					$tmp['thumbnail_uri'] = $tmp['uri'];
					$tmp['icon_uri'] = $tmp['uri'];

					// generate thumb
					if(in_array($tmp['extension'], \Core\Config::get('file_manager/filters/image')) && $tmp['extension'] != 'svg')
					{
						$info = @getimagesize($item);
						if($info)
							$tmp['details'] = "{$info[0]} x {$info[1]}";


						$cache_name = str_replace(\Core\Config::get('file_manager/dir/root'), \Core\Config::get('file_manager/dir/cache'), $item);
						$tmp['thumbnail_uri'] = get_absolute_path($cache_name);
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];

						if(!file_exists($cache_name))
						{
							@mkdir(dirname($cache_name), \Core\Config::get('file_manager/dir/permission_creation'), true);

							$image = new ImageResize($item);
							$image->resize(160, 160, true);
							$image->save($cache_name);
						}
					}
					elseif(in_array($tmp['extension'], ['svg']))
					{
						$tmp['thumbnail_uri'] = $tmp['uri'];
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], ['pdf']))
					{
						$tmp['thumbnail_uri'] = "@assets_img/pdf.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], ['ps']))
					{
						$tmp['thumbnail_uri'] = "@assets_img/photoshop.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], \Core\Config::get('file_manager/filters/audio')))
					{
						$tmp['thumbnail_uri'] = "@assets_img/audio.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], \Core\Config::get('file_manager/filters/video')))
					{
						$tmp['thumbnail_uri'] = "@assets_img/video.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], ['xls', 'xlsx']))
					{
						$tmp['thumbnail_uri'] = "@assets_img/excel.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], ['doc', 'docx']))
					{
						$tmp['thumbnail_uri'] = "@assets_img/word.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], ['ppt', 'pptx']))
					{
						$tmp['thumbnail_uri'] = "@assets_img/powerpoint.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					elseif(in_array($tmp['extension'], ['zip', 'rar', '7zip', 'tar', 'gz']))
					{
						$tmp['thumbnail_uri'] = "@assets_img/zip.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}
					else
					{
						$tmp['thumbnail_uri'] = "@assets_img/file.png";
						$tmp['icon_uri'] = $tmp['thumbnail_uri'];
					}

					$files[] = $tmp;
				}
			}
		}

		$data = [];

		$data['mode'] = $mode;
		$data['sort'] = $sort;
		$data['filter'] = $filter;
		$data['target'] = $target;

		$data['dropzone_exts_allowed_str'] = $dropzone_exts_allowed_str;
		$data['path'] = $path;
		$data['folders'] = $folders;
		$data['files'] = $files;

		$data['error_path'] = $error_path;
		$data['query_xpath'] = "mode={$mode}&filter={$filter}&sort={$sort}&target={$target}";
		$data['query_xsort'] = "mode={$mode}&filter={$filter}&target={$target}&path={$path}";

		return View('render', $data);
	}


	/**
	 * check path
	 * @param $path
	 * @return bool
	 */
	static function checkPath($path):bool
	{
		if(empty($path))return true;

		if(str_contains($path, "//") || str_contains($path, ".") || str_contains($path, ".."))
		{
			return false;
		}

		// check complete path
		$root_path = \Core\Config::get('file_manager/dir/root');
		$root_path_completed =	(empty($path)) ? $root_path : "{$root_path}/{$path}";
		$real_path = realpath($root_path_completed);

		if(!$real_path || !str_starts_with($real_path, $root_path) || !is_dir($real_path))
		{
			return false;
		}


		return true;
	}


	/**
	 * reconstruct realpath
	 *
	 * @param $path
	 * @param $filename
	 * @return string
	 */
	static function getFilePath($path, $filename):string
	{
		$root_path = \Core\Config::get('file_manager/dir/root');
		$path = trim($path, '/');
		$root_path_completed =	(empty($path)) ? $root_path : "{$root_path}/{$path}";

		return "{$root_path_completed}/{$filename}";
	}


	/**
	 * @route /@backend/@module/rename/ {method:"POST", controller:"rename"}
	 */
	public function rename():JsonResponse
	{
		$filename_original = post('filename_original');
		$new_filename = post('new_filename');
		$path = post('path');
		$type = post('type');

		$this->validator->input('filename_original')->required();
		$this->validator->input('type')->required()->in(['folder', 'file']);

		$key = '';
		if($type == 'folder') $key = 'file_manager/security/dir.name.alnum_allowed_chars_additionnal';
		if($type == 'file') $key = 'file_manager/security/file.name.alnum_allowed_chars_additionnal';
		$allowed_chars = \Core\Config::get($key);

		$this->validator->input('new_filename')->required()->alphaNumeric($allowed_chars);

		// check path
		$path = trim($path, '/');
		if(!self::checkPath($path))
			$this->validator->addError('Path not correct');

		if($this->validator->success())
		{
			// verify filepath
			$filename_original_path = self::getFilePath($path, $filename_original);
			$new_filename = trim($new_filename);

			if($type == 'folder')
			{
				$new_filename_with_ext = $new_filename;
			}
			else
			{
				$new_filename_with_ext = "{$new_filename}.".file_get_extension($filename_original);
			}

			$new_filename_with_ext_path = self::getFilePath($path, $new_filename_with_ext);

			$log_level = 'info';

			$filename_original_pathX = str_erase(APP_PATH, $filename_original_path);
			$new_filename_with_ext_pathX = str_erase(APP_PATH, $new_filename_with_ext_path);

			$log_message = "`{$filename_original_pathX}` => `{$new_filename_with_ext_pathX}`";
			if(empty($new_filename))
			{
				$log_level = 'error';
				$log_message = "Error: `{$new_filename_with_ext}` rename failed";
				$this->validator->addError($log_message);
			}
			elseif($type === 'file' && !file_exists($filename_original_path))
			{
				$log_level = 'error';
				$log_message = "Error: `{$new_filename_with_ext}` doesn't exist";
				$this->validator->addError($log_message);
			}
			elseif($type === 'folder' && !is_dir($filename_original_path))
			{
				$log_level = 'error';
				$log_message = "Error: `{$new_filename_with_ext}` folder doesn't exist";
				$this->validator->addError($log_message);
			}
			elseif($type === 'folder' && is_dir($new_filename_with_ext_path))
			{
				$log_level = 'error';
				$log_message = "Error: `{$new_filename_with_ext}` already exists";
				$this->validator->addError($log_message);
			}
			elseif(!@rename($filename_original_path, $new_filename_with_ext_path))
			{
				$error = error_get_last();

				$log_level = 'error';
				$log_message = "Error: `{$new_filename_with_ext}` rename failed ({$error['message']})";
				$this->validator->addError($log_message);
			}
			else
			{
				$log_level = 'success';
			}

			\Core\Log::write('ufm-rename', $log_message, $_POST, false, $log_level);
		}

		return new JsonResponse($this->validator->result());
	}

	/**
	 * @route /@backend/@module/unlink/ {method:"POST", controller:"unlink"}
	 */
	public function unlink():JsonResponse
	{
		$type = post('type');
		$path = post('path');
		$filename = post('filename');

		$this->validator->input('type')->required()->in(['folder', 'file']);
		$this->validator->input('filename')->required();

		if(in_array($filename, ['.', '..']) || str_starts_with($filename, "."))
			$this->validator->addError('Filename not correct');

		// check path
		$path = trim($path, '/');
		if(!self::checkPath($path))
			$this->validator->addError('Path not correct');

		if($this->validator->success())
		{
			$filename_path = self::getFilePath($path, $filename);
			$filename_pathX = str_erase(APP_PATH, $filename_path);

			$log_level = 'success';
			$log_message = "`{$filename_pathX}` ($type)";

			if(!file_exists($filename_path))
			{
				$log_level = 'error';
				$log_message = "Error: `{$filename}` doesn't exist";
				$this->validator->addError($log_message);
			}
			else
			{
				if($type == 'folder')
				{
					if(!is_writable($filename_path))
					{
						$log_level = 'error';
						$log_message = "Error: `{$filename}` folder is not writable";
						$this->validator->addError($log_message);
					}
					if(!@rmdir($filename_path))
					{
						$log_level = 'error';
						$log_message = "Error: `{$filename}` rmdir failed (dir must be empty)";
						$this->validator->addError($log_message);
					}
				}
				else
				{
					if(!@unlink($filename_path))
					{
						$error = error_get_last();
						$log_level = 'error';
						$log_message = "Error: `{$filename}` unlink failed ({$error['message']})";
						$this->validator->addError($log_message);
					}
					else
					{
						// remove thumbs
						$thumb_path = str_replace(\Core\Config::get("file_manager/dir/root"), \Core\Config::get("file_manager/dir/cache"), $filename_path);
						@unlink($thumb_path);
					}
				}
			}

			\Core\Log::write('ufm-unlink', $log_message, $_POST, false, $log_level);

		}

		return new JsonResponse($this->validator->result());
	}

	/**
	 * @route /@backend/@module/dir/ {method:"POST", controller:"dir"}
	 */
	public function dir():JsonResponse
	{
		$path = post('path');
		$folder_name = post('folder_name');

		$this->validator->input('folder_name')->required()->alphaNumeric(\Core\Config::get("file_manager/security/dir.name.alnum_allowed_chars_additionnal"));

		// check path
		$path = trim($path, '/');
		if(!self::checkPath($path))
			$this->validator->addError('Path not correct');

		if($this->validator->success())
		{
			$folder_path = self::getFilePath($path, $folder_name);
			$folder_pathX = str_erase(APP_PATH, $folder_path);

			$log_level = 'success';
			$log_message = "`{$folder_pathX}`";

			if(is_dir($folder_path))
			{
				$log_level = 'error';
				$log_message = "Error: `{$folder_name}` already exists";
				$this->validator->addError($log_message);
			}
			elseif(!is_writable(dirname($folder_path)))
			{
				$log_level = 'error';
				$log_message = "Error: `{$folder_name}` folder is not writable";
				$this->validator->addError($log_message);
			}
			elseif(!@mkdir($folder_path, \Core\Config::get('file_manager/dir/permission_creation')))
			{
				$log_level = 'error';
				$log_message = "Error: `{$folder_name}` mkdir failed";
				$this->validator->addError($log_message);
			}

			\Core\Log::write('ufm-mkdir', $log_message, $_POST, false, $log_level);

		}

		return new JsonResponse($this->validator->result());
	}


	/**
	 * @route /@backend/@module/upload/ {method:"POST", controller:"upload"}
	 */
	public function upload():JsonResponse
	{
		$path = post('path');

		// check path
		$path = trim($path, '/');
		if(!self::checkPath($path))
			$this->validator->addError('Path not correct');

		$this->validator->input('file')->fileRequired()
									   ->fileUploaded()
									   ->fileMaxSize(\Core\Config::get('file_manager/upload/file_max_size'));

		$allowed_exts = \Core\Config::get('file_manager/upload/allowed_ext');
		if(count($allowed_exts))
		{
			$exts = [];
			foreach($allowed_exts as $ext)
			{
				if(!str_starts_with($ext, 'filter:'))
				{
					$exts[] = $ext;
				}
				else
				{
					$ext = str_erase('filter:', $ext);
					$exts = array_merge($exts, \Core\Config::get("file_manager/filters/{$ext}"));
				}
			}

			$this->validator->input('file')->fileExtension($exts);
		}


		$allowed_mimetypes = \Core\Config::get('file_manager/upload/allowed_mimetype');
		if(count($allowed_mimetypes))
			$this->validator->input('file')->fileMime($allowed_mimetypes);

		if($this->validator->success())
		{
			// check security name
			$file_name = $_FILES['file']['name'];
			$file_name_noext = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
			$file_tmp_name = $_FILES['file']['tmp_name'];

			$this->validator->inputSet('file_name', $file_name_noext);
			$this->validator->input('file_name')->required()->alphaNumeric(\Core\Config::get("file_manager/security/dir.name.alnum_allowed_chars_additionnal"));

			if($this->validator->success())
			{
				$log_message = "upload file `{$path}/{$file_name}`";
				$log_level = 'success';

				$root_path = \Core\Config::get('file_manager/dir/root');
				$root_path_completed =	(empty($path)) ? $root_path : "{$root_path}/{$path}";
				$upload_dir = realpath($root_path_completed);

				if(@is_file("{$upload_dir}/{$file_name}"))
				{
					$log_level = 'error';
					$log_message = "Upload error from server `{$path}/{$file_name}` file exists";
					$this->validator->addError($log_message);
				}
				elseif(@!move_uploaded_file($_FILES['file']['tmp_name'], "{$upload_dir}/{$file_name}"))
				{
					$log_level = 'error';
					$log_message = "Upload error from server `{$path}/{$file_name}`";
					$this->validator->addError($log_message);
				}
				else
				{

				}

				\Core\Log::write('ufm-upload', $log_message, $_POST, false, $log_level);

			}


		}

		return new JsonResponse($this->validator->result());
	}


	/**
	 * @route /@backend/@module/upload-image/ {method:"POST", controller:"uploadImage"}
	 */
	public function uploadImage():JsonResponse
	{
		$url = post('url');
		$path = post('path');


		// check path
		$path = trim($path, '/');
		if(!self::checkPath($path))
			$this->validator->addError('Path not correct');

		$this->validator->input('url')->required()->url(FILTER_VALIDATE_URL);

		if($this->validator->success())
		{
			$contents = @file_get_contents($url);

			if($contents === false)
			{
				$this->validator->addError("File download error");
			}
			elseif(($image_info = getimagesizefromstring($contents)) === false)
			{
				$this->validator->addError("File is not an image");
			}
			else
			{
				$mime = $image_info['mime'];
				$ext = str_erase('image/', $mime);

				if(count(\Core\Config::get("file_manager/upload/allowed_mimetype")) && !in_array($mime, \Core\Config::get("file_manager/upload/allowed_mimetype")))
				{
					$this->validator->addError("File mime not allowed");
					\Core\Log::write('ufm-upload-image', "File mime not allowed", $_POST, false, 'error');
				}
				elseif(!in_array($ext, \Core\Config::get("file_manager/filters/image")))
				{
					$this->validator->addError("File extension not allowed");
					\Core\Log::write('ufm-upload-image', "File extension not allowed", $_POST, false, 'error');
				}
				else
				{
					// create file
					$_FILES['file'] = ['size' => strlen($contents)];
					$this->validator->input('file')->fileMaxSize(\Core\Config::get("file_manager/upload/file_max_size"));
				}

				// success
				if($this->validator->success())
				{
					// check is writable
					$file_name = uniqid("image-").".{$ext}";

					$log_message = "upload file `{$path}/{$file_name}`";
					$log_level = 'success';

					$root_path = \Core\Config::get('file_manager/dir/root');
					$root_path_completed =	(empty($path)) ? $root_path : "{$root_path}/{$path}";
					$upload_dir = realpath($root_path_completed);

					if(@is_file("{$upload_dir}/{$file_name}"))
					{
						$log_level = 'error';
						$log_message = "Upload image error from server `{$path}/{$file_name}` file exists";
						$this->validator->addError($log_message);
					}
					elseif(@is_writeable("{$upload_dir}/{$file_name}"))
					{
						$log_level = 'error';
						$log_message = "Upload image error from server `{$path}/{$file_name}` file exists";
						$this->validator->addError($log_message);
					}
					elseif(@!file_put_contents("{$upload_dir}/{$file_name}", $contents))
					{
						$log_level = 'error';
						$log_message = "Upload image wrting error from server `{$path}/{$file_name}`";
						$this->validator->addError($log_message);
					}

					\Core\Log::write('ufm-upload-image', $log_message, $_POST, false, $log_level);

				}



			}

		}

		return new JsonResponse($this->validator->result());
	}




	public function uploadImageOld():JsonResponse
	{
		$path = post('path');

		// check path
		$path = trim($path, '/');
		if(!self::checkPath($path))
			$this->validator->addError('Path not correct');

		// get file extension
		$this->validator->input('file_name')->required()->alphaNumeric(\Core\Config::get("file_manager/security/dir.name.alnum_allowed_chars_additionnal"));
		$this->validator->input('file')->required();
		if($this->validator->success())
		{
			$this->validator->input('file')->fileImageBase64('png');
			$this->validator->input('file')->fileMaxSize(\Core\Config::get('file_manager/upload/file_max_size'));
			$this->validator->input('file')->fileRequired();
		}

		if($this->validator->success())
		{
			// check security name
			$file_name = post('file_name').".png";
			$file_name_noext = pathinfo($file_name, PATHINFO_FILENAME);
			$file_tmp_name = $_FILES['file']['tmp_name'];

			if($this->validator->success())
			{
				$log_message = "upload image `{$path}/{$file_name}`";
				$log_level = 'success';

				$root_path = \Core\Config::get('file_manager/dir/root');
				$root_path_completed =	(empty($path)) ? $root_path : "{$root_path}/{$path}";
				$upload_dir = realpath($root_path_completed);

				if(@is_file("{$upload_dir}/{$file_name}"))
				{
					$log_level = 'error';
					$log_message = "Upload imaeg error from server `{$path}/{$file_name}` file exists";
					$this->validator->addError($log_message);
				}
				elseif(@!copy($_FILES['file']['tmp_name'], "{$upload_dir}/{$file_name}"))
				{
					$log_level = 'error';
					$log_message = "Upload image error from server `{$path}/{$file_name}`";
					$this->validator->addError($log_message);
				}
				else
				{

				}

				\Core\Log::write('upload-image', $log_message, $_POST, false, $log_level);

			}


		}

		return new JsonResponse($this->validator->result());
	}

}