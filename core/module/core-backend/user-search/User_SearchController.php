<?php

namespace Plugin\Core_Backend;

use Symfony\Component\HttpFoundation\JsonResponse;

class User_SearchController extends \Core\Controller {
	
	public string $table = 'xcore_user_search';

	/**
	 * @route /@backend/user-search/{plugin_id}/
	 */
	public function list(int $plugin_id):JsonResponse
	{
		$searches = \Model\User_Search::all("xcore_user_id = :user_id and xcore_plugin_id = :plugin_id", [':user_id' => \Model\User::getUID(), ':plugin_id' => $plugin_id], '*', '', 'name');
		
		$result = $this->validator->result();
		$result['searches'] = $searches;
		
		
		return new JsonResponse($result);
	}

	/**
	 * @route /@backend/user-search/{plugin_id}/    {method: "POST"}
	 */
	public function add():JsonResponse
	{
		$this->validator->input('xcore_plugin_id')->required()->integer(false);
		$this->validator->input('url')->required();
		$this->validator->input('name')->required();
		
		if($this->validator->success())
		{
			$f = [];
			$f['xcore_user_id'] = \Model\User::getUID();
			$f['xcore_plugin_id'] = $this->validator->inputGet('xcore_plugin_id');
			$f['name'] = ucfirst($this->validator->inputGet('name'));
			$f['url'] = $this->validator->inputGet('url');
			
			
			\Model\User_Search::insert($f);
		}
		
		$result = $this->validator->result();
		return new JsonResponse($result);
	}

	/**
	 * @route /@backend/user-search/delete/{id}/    {method: "DELETE"}
	 */
	public function delete(int $id):JsonResponse
	{
		\Model\User_Search::delete(["id = :id and xcore_user_id = :user_id", [':id' => $id, ':user_id' => \Model\User::getUID()]]);
		
		
		$result = $this->validator->result();
		return new JsonResponse($result);
	}
	
}