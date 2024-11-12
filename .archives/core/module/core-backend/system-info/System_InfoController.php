<?php

namespace Plugin\Core_Backend;

class System_InfoController extends \Core\Controller
{
	/**
	 * @route /@backend/system-info/    {name:"backend-system-info"}
	 */
	public function index()
	{
		
		ob_start();
		phpinfo();
		$c = ob_get_contents();
		ob_end_clean();
		
		$data = [];
		// $data['info'] = $c;
		$data['info'] = str_extract('<body>', '</body>', $c);
		
		return View('index', $data);
	}
}