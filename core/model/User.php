<?php

namespace Model;

use Core\Config;

class User extends \Core\Entity
{
	public static string $table = 'xcore_user';
	
	/**
	 * get User ID
	 * @return int|false
	 */
	public static function getUID():int|false
	{
		return \Core\Session::get(\Core\Config::get('auth/key_name'), false);
	}
	
	/**
	 * check if user exists
	 * @param string $key
	 * @param string $password
	 *
	 * @return bool|int
	 */
	public static function exists(string $key, string $password):bool|int
	{
		$part = App()->is_backend ? 'backend' : 'frontend';
		$column = \Core\Config::get("auth/{$part}/key");
		
		$params = [];
		$params[':key'] = $key;
		
		$rec = db(self::$table)->select('id, password')
								->where("active = 'yes'")
								->where("{$column} = :key")
								->limit(1)
								->executeSQL($params)
								->fetch();
				
		if(!$rec)return false;
		
		// check password
		if(!password_verify($password, $rec['password']))
			return false;
			
		return $rec['id'];
	}
	
	/**
	 * check if user is logon
	 * @return void
	 */
	public static function isLogon():bool
	{
		return \Core\Session::get(\Core\Config::get('auth/key_name'), false);
	}
	
	/**
	 * logon user (@event: user.logon)
	 * @param int $id
	 *
	 * @return void
	 */
	public static function logon(int $id):void
	{
		$columns = "id, xcore_group_id, avatar, avatar as avatar_thumb, language, firstname, lastname, login, email, timezone, format_date, format_datetime\n";
		$columns .= ", (select name from xcore_group where id = xcore_group_id) AS group_name\n";
		$columns .= ", (select priority from xcore_group where id = xcore_group_id) AS group_priority\n";
		$columns .= ", (select access_backend from xcore_group where id = xcore_group_id) AS access_backend\n";
		$columns .= ", (select access_frontend from xcore_group where id = xcore_group_id) AS access_frontend\n";
		
		if(!empty(\Core\Config::get('auth/session_columns_added', '')))
		{
			$columns .= ", ".\Core\Config::get('auth/session_columns_added', '')."\n";
		}
		
		
		$rec = db(self::$table)->select($columns)
							  ->where($id)
							  ->executeSQL()
							  ->fetch();
		
		$id = $rec['id'];
		
		foreach($rec as $key => $val)
		{
			if(in_array($key, ['access_backend', 'access_frontend']))
				$val = (strtolower($val) == 'yes');
			
			if($key == 'avatar_thumb' && !empty($val))
			{
				$ext = file_get_extension($val);
				$val = str_replace(".{$ext}", "_thumb.{$ext}", $val);
			}
			
			if(is_null($val))$val = '';
			\Core\Session::set("auth.{$key}", $val);
		}
		
		$f = [];
		$f['last_connection_date'] = system_date();
		$f['last_connection_ip'] = getVisitorIp();
		$f['last_connection_application'] = App()->is_backend ? 'backend' : 'frontend';
		self::update($f, $id);
		
		\Core\Log::write('logon', '', [], 0, 'info', 'auth', \Core\Session::get('auth.login'), (App()->is_backend) ? 'backend' : 'frontend');
		
		
		\Core\Session::set(\Core\Config::get('auth/key_name'), $id);
		\Core\EventManager::emit('user.logon', ['id' => $id]);
	}
	
	/**
	 * logout user (@event: user.logout)
	 * @return void
	 */
	public static function logout():void
	{
		\Core\Log::write('logout', '', [], 0, 'info', 'auth', \Core\Session::get('auth.login'), (App()->is_backend) ? 'backend' : 'frontend');
		
		\Core\Session::destroy(\Core\Config::get('auth/key_name'));
		\Core\Session::destroy('auth.*');
		\Core\EventManager::emit('user.logout');
	}
	
	/**
	 * get allowed menu by user
	 * @string contents
	 */
	public static function getMenu():array
	{
		$menu = [];
		if(!\Model\User::isLogon())return $menu;
		
		// get all alloweds by user group (backend_access)
		$sql_added = "";
		$bind = [];
		if(\Core\Session::get('auth.xcore_group_id') != 1)
		{
			$sql_added = " AND xcore_plugin.id IN(SELECT xcore_plugin_id FROM xcore_group_right WHERE deleted = 'no' AND xcore_group_id = :group_id)";
			$bind[':group_id'] = \Core\Session::get('auth.xcore_group_id');
		}
		
		$sql = "SELECT
						xcore_plugin.*,
						xcore_menu.name AS category,
						xcore_menu.icon AS category_icon
				FROM
				        xcore_plugin,
				        xcore_menu
				WHERE
				        xcore_plugin.deleted = 'no' AND
				        xcore_menu.deleted = 'no' AND
				        xcore_menu.id = xcore_plugin.xcore_menu_id AND
				        xcore_plugin.visible= 'yes' AND
				        xcore_plugin.active = 'yes'
				        {$sql_added}
				ORDER BY
				        xcore_menu.position,
				        xcore_plugin.position";
		
		$recs = Db()->query($sql, $bind)->fetchAll();
		
		$backend_dirname = \Core\Config::get('backend/dirname');
		foreach($recs as $r)
		{
			$tmp = $r;
			
			if($tmp['type'] == 'normal')
				$tmp['url'] = "/{$backend_dirname}/{$r['route_prefix_name']}/";
				
			$menu[$r['category']][] = $tmp;
		}
		
		return $menu;
	}
	
	/**
	 * get if User has right on plugin (current if empty)
	 *
	 * @param string $right
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public static function hasRight(string $right, string $plugin=''):bool
	{
		$right = trim($right);
		
		// current
		if(empty($plugin))
		{
			if(App()->is_backend)
			{
				$plugin = App()->plugin['route_prefix_name'];
				$actions = explode(CR, trim(App()->plugin['actions']));
				$actions = array_map('trim', $actions);

				// right exists ?
				if(empty($right) || !in_array($right, $actions))
					return false;
			}
		}
		
		// verify if action exits globally
		if(\Model\User::isLogon() && \Core\Session::get('auth.xcore_group_id') == 1)
		{
			return true;
		}
		
		// verify from route
		$sql = "SELECT
                       *
                FROM
                       xcore_plugin,
                       xcore_group_right
                WHERE
                       xcore_plugin.deleted = 'no' AND
                       xcore_group_right.deleted = 'no' AND
                       xcore_group_right.xcore_plugin_id = xcore_plugin.id AND
                       xcore_group_right.xcore_group_id = :group AND
                       xcore_group_right.action = :action AND
                       xcore_plugin.active = 'yes' AND
                       xcore_plugin.route_prefix_name = :route
                LIMIT
                       1";
		
		$route = \Model\Plugin::extractName();
		$rec = Db()->query($sql, [':action' => $right, ':route' => $route, ':group' => \Core\Session::get('auth.xcore_group_id')])->fetch();



		if(!$rec)return false;
		
		
		
		return true;
	}


	/**
	 * Return html image for user
	 *
	 * @param int $id
	 * @param string|null $avatar_img
	 * @param string $lastname_or_login
	 * @param string $firstname
	 * @return string
	 */
	public static function getAvatarBadge(int $id, string|null $avatar_img, string $lastname_or_login, string $firstname=""):string
	{
		if(empty($avatar_img))$avatar_img = '0.png';
		$avatar_img_url = \Core\Config::get('dir/avatar')."/{$avatar_img}";
		$avatar_img_url = get_absolute_path($avatar_img_url);

		$fullname = $lastname_or_login;
		if(!empty($firstname))$fullname .= " {$firstname}";
		$fullname = trim($fullname);

		$badge = "<img class=\"img-avatar img-avatar-xs border tt\" data-bs-toggle='tooltip' title=\"{$fullname}\" src=\"{$avatar_img_url}\">";

		return $badge;
	}



}

