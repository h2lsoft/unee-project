<?php

namespace Plugin\Core_Backend;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\HttpFoundation\JsonResponse;

class User_BookmarkController extends \Core\Controller {
	
	public string $table = 'xcore_user_bookmark';

	/**
	 * @route /@backend/user-bookmark/
	 */
	public function list()
	{
		if(App()->request->getRequestFormat() == 'json')
		{
			$sql = "SELECT
                            xcore_plugin.id,
                            xcore_plugin.name,
                            xcore_plugin.type,
                            xcore_plugin.icon
					FROM
					        xcore_plugin,
					        xcore_user_bookmark
					WHERE
					        xcore_plugin.deleted = 'no' AND
					        xcore_user_bookmark.deleted = 'no' AND
					        xcore_plugin.id = xcore_user_bookmark.xcore_plugin_id AND
					        xcore_user_bookmark.xcore_user_id = :uid
					ORDER BY
					        xcore_user_bookmark.position,
					        xcore_plugin.name";

			$resp = [];
			$resp['error'] = false;
			$resp['data'] = DB()->query($sql, [':uid' => \Model\User::getUID()])->fetchAll();
			
			return new JsonResponse($resp);
		}
	}

	/**
	 * @route /@backend/user-bookmark/reorder/ {method: "POST"}
	 */
	public function updatePosition()
	{
		$UID = \Model\User::getUID();
		\Model\User_Bookmark::update(['position' => 0], ["xcore_user_id = :uid", [':uid' => $UID]]);

		$ids = post('ids', []);
		if(is_string($ids))
			$ids = explode(';', $ids);

		$position = 1;
		foreach($ids as $id)
		{
			$id = (int) $id;
			if(!$id)continue;

			\Model\User_Bookmark::update(['position' => $position], ["xcore_plugin_id = :plugin_id and xcore_user_id = :uid", [':uid' => $UID, ':plugin_id' => $id]]);
			$position++;
		}


		$resp = [];
		$resp['error'] = false;
		return new JsonResponse($resp);
	}



	/**
	 * @route /@backend/user-bookmark/toggle/{id}/
	 */
	public function toggle(int $id)
	{
		$bookmark_id = false;
		$where = "xcore_user_id = :xcore_user_id AND xcore_plugin_id = :xcore_plugin_id";
		$params = [':xcore_user_id' => \Model\User::getUID(), ':xcore_plugin_id' => $id];
		
		if(!($bookmark = \Model\User_Bookmark::findOne($where, $params)))
		{
			$f = [];
			$f['xcore_user_id'] = \Model\User::getUID();
			$f['xcore_plugin_id'] = $id;
			$f['position'] = @\Model\User_Bookmark::findOne('xcore_user_id = :xcore_user_id', [':xcore_user_id' => \Model\User::getUID()], 'position' , [], 'position')['position'] + 1;

			$bookmark_id = \Model\User_Bookmark::insert($f);
		}
		else
		{
			DB()->setSoftMode(0);
			\Model\User_Bookmark::delete($bookmark['id']);
			DB()->setSoftMode(1);
		}
		
		
		$data = $this->validator->result();
		$data['record_id'] = $bookmark_id;
		
		return new JsonResponse($data);
	}
	
}