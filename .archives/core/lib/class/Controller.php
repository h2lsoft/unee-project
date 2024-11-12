<?php

namespace Core;

use h2lsoft\Data\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class Controller {

	public Validator $validator;
	public string $object_label = 'record';
	public string $table = '';
	public int $id;
	
	
	public function __construct()
	{
		// load locale
		$this->validator = new Validator(App()->locale);

		if(App()->locale != 'en')
		{
			if(file_exists(APP_PATH . "/core/i18n/".App()->locale.".php"))
				$this->validator->addLocaleMessages(App()->locale, require(APP_PATH . "/core/i18n/".App()->locale.".php"));

			if(file_exists(APP_PATH . "/i18n/".App()->locale.".php"))
				$this->validator->addLocaleMessages(App()->locale, require(APP_PATH . "/i18n/".App()->locale.".php"));
		}
		
		// init user logon timestamp
		\Core\EventManager::emit('core.controller.init');
	}
	
	
	public function getForm(int $id=0){}

	public function add(){
		$data = [];
		$data['content'] = $this->getForm();
		return View('@plugin-content', $data);
	}

	public function edit(int $id){
		$data = [];
		$data['content'] = $this->getForm($id);
		return View('@plugin-content', $data);
	}

	public function delete(int $id)
	{
		$this->id = $id;
		$this->onDeleteBefore();
		
		if($this->validator->success())
		{
			$this->onDeleteDatabaseBefore();
			DB($this->table)->delete($id, 1, \Core\Session::get('auth.login'));
			$this->onDeleteDatabaseAfter();
		}
		
		$this->onDeleteAfter();
		
		// return json_encode();
		return new JsonResponse($this->validator->result());
		
	}


	
	public function loadAssetsJs(array $scripts, bool $inside_body=true):void
	{
		global $config;
		
		foreach($scripts as $js)
		{
			if(basename($js) == $js)
			{
				$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[0]['file'];
				$js = str_erase(APP_PATH, dirname($caller)."/assets/js/{$js}");
			}
			
			$section = ($inside_body) ? 'js_body' : 'js_head';
			$config['backend']['assets'][$section][] = $js;
			
		}
	}
	public function loadAssetsCss(array $scripts):void
	{
		global $config;
		
		foreach($scripts as $css)
		{
			if(basename($css) == $css)
			{
				$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[0]['file'];
				$css = str_erase(APP_PATH, dirname($caller)."/assets/css/{$css}");
			}
			
			$config['backend']['assets']['css'][] = $css;
			
		}
	}
	
	
	// event
	public function onSaveDatabaseBefore(array $f):array{return $f;}
	public function onSaveDatabaseAddBefore(array $f):array{return $f;}
	public function onSaveDatabaseEditBefore(array $f):array{return $f;}
	public function onSaveDatabaseAfter(array $f):array{return $f;}
	public function onSaveDatabaseAddAfter(array $f):array{return $f;}
	public function onSaveDatabaseEditAfter(array $f):array{return $f;}
	public function onSaveAfter(array $f):array{return $f;}

	public function onDeleteBefore():void{}
	public function onDeleteAfter():void{}
	public function onDeleteDatabaseBefore():void{}
	public function onDeleteDatabaseAfter():void{}

	/**
	 * get all files controller files
	 * @return void
	 */
	public static function getAll():array
	{

		$all_controllers = [];

		$controllers = glob(APP_PATH."/core/module/*/*/*Controller.php");
		foreach($controllers as $c_controller)
		{
			if(file_exists(str_replace(APP_PATH."/core/", APP_PATH."/", $c_controller)))continue;
			$all_controllers[] = $c_controller;
		}

		// user plugin
		$controllers = glob(APP_PATH."/module/*/*/*Controller.php");
		foreach($controllers as $c_controller)
		{
			$all_controllers[] = $c_controller;
		}

		return $all_controllers;
	}


	/**
	 * get info from controller
	 *
	 * @param string $file_controller_path
	 * @return array
	 */
	public static function getInfo(string $file_controller_path):array
	{
		$info = [];
		$info['core'] = str_starts_with($file_controller_path, APP_PATH."/core/module/");

		$paths = str_erase([APP_PATH."/core/module/", APP_PATH."/module/"], $file_controller_path);
		$paths = explode('/', $paths);

		$package = str_replace(' ', '_', ucwords(str_replace('-', ' ', $paths[0])));
		$module = str_replace(' ', '_', ucwords(str_replace('-', ' ', $paths[1])));
		$controller = ucfirst(str_erase('.php', $paths[2]));
		$class_path = "\Plugin\\{$package}\\{$controller}";

		$info['paths'] = $paths;
		$info['package'] = $package;
		$info['module'] = $module;
		$info['controller'] = $controller;
		$info['class_path'] = $class_path;


		return $info;
	}
	
	
	
}