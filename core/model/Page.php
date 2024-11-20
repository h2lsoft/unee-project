<?php
namespace Model;

use Core\Debugbar;
use Symfony\Component\HttpFoundation\Response;



class Page extends \Core\Entity
{
	public static string $table = 'xcore_page';
	public static string $route_tmp_current_lang = '';
	public static bool $route_tmp_current_lang_is_default = false;

	public static string $page_edit_url = '';

	private static $pattern = [];
	private static $id = false;
	private static $data = [];


	public static function currentGet($key)
	{
		return self::$data[$key];
	}


	public static function getId():int
	{
		return self::$id;
	}

	public static function addPattern(string $pattern, string $replace):void
	{
		self::$pattern[] = ['pattern' => $pattern, 'replace' => $replace];
	}

	public static function render(string|array $url, string $where_add="", array $where_add_params=[], array $data=[]):Response
	{

		// maintenance mode
		$cur_ip = getVisitorIp();
		$ips_allowed = \Core\Config::get('frontend/maintenance.ips_allowed');

		if(\Core\Config::get('frontend/maintenance.ips_allowed_include_debug') && count(\Core\Config::get('debug_for_ip')))
			$ips_allowed = array_merge($ips_allowed, \Core\Config::get('debug_for_ip'));


		if(\Core\Config::get('frontend/maintenance') && !in_array($cur_ip, $ips_allowed))
		{
			// url exlude ?
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
			$cur_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

			$url_exclude_patterns = \Core\Config::get('frontend/maintenance.url_exclude_patterns');
			$url_exclude_found = false;
			foreach($url_exclude_patterns as $url_exclude_pattern)
			{
				if(preg_match($url_exclude_pattern, $cur_url))
				{
					$url_exclude_found = true;
					break;
				}
			}

			if(!$url_exclude_found)
			{
				Debugbar::disable();

				$maintenance_info = \Core\Config::get('frontend/maintenance.information');

				// redirect url
				if(str_starts_with($maintenance_info, 'http') || str_starts_with($maintenance_info, '/'))
				{
					http_redirect($maintenance_info);
					die();
				}

				if(empty($maintenance_info))
				{
					$tpl = \Core\Config::get('frontend/maintenance.template');
					return View($tpl, ['locale' => App()->locale, 'logo_url' => \Core\Config::get('frontend/maintenance.information.logo_url')], 503);

				}
				else
				{
					return new \Core\Response($maintenance_info, 503);
				}

			}


		}


		// normal mode
		$where = "";

		if(!User::isLogon() || !User::hasRight('edit', 'page'))
			$where = "status = 'published'";

		$where_params = [];

		if(!is_array($url))
		{
			if(!empty($url))
			{
				if(!empty($where))$where .= " and ";
				$where .= " url = :url ";

				$where_params = [':url' => $url];
			}
		}
		else
		{
			if(count($url))
			{
				$urls = '"'.join('", "', $url).'"';
				if(!empty($where))$where .= " and ";
				$where .= " url IN({$urls}) ";
			}
		}


		if(!empty($where_add))
		{
			if(empty($where))
				$where = "{$where_add}";
			else
				$where .= " and {$where_add}";
		}

		if(count($where_add_params))
			$where_params = array_merge($where_params, $where_add_params);

		$page = Page::findOne($where, $where_params, "*");

		// default values
		$theme_uri = "/theme/".\Core\Config::get('frontend/theme');
		$theme_path = $theme_uri;
		$template = (empty($page['template'])) ? 'index.twig' : "{$page['template']}";
		$status = 200;

		// error 404
		if(!$page)
		{
			$status = 404;
			$template = '@error404.twig';
		}
		else
		{
			self::$id = $page['id'];
			self::$data = $page;

			// option list_subpage
			if($page['list_subpage'] == 'yes')
			{
				$subpages = \Model\Page::getAll($page['xcore_page_zone_id'], $page['language'], $page['id'], 'id, url, name, headline');

				if(count($subpages))
				{
					$page['content'] .= "\n<!-- list subpage -->\n";
					$page['content'] .= "\n<div class=\"unee-list-subpage\">\n";
					$page['content'] .= "\n\t<ul>\n";

					foreach($subpages as $sp)
					{
						if(!empty($sp['headline']))
							$sp['name'] = $sp['headline'];

						$page['content'] .= "\n\t<li data-id=\"{$sp['id']}\"><a href=\"{$sp['url']}\">{$sp['name']}</a></li>\n";
					}

					$page['content'] .= "\n\t</ul>\n";
					$page['content'] .= "\n</div>\n";
					$page['content'] .= "\n<!-- /list subpage -->\n";
				}
			}

			$data['unee_version'] = \Model\Live_Updater::$version;
			$data['page'] = $page;
			$data['theme_path'] = $theme_path;
		}

		$view = "{$theme_uri}/{$template}";

		// parsing page variables
		$response = View($view, $data, $status);
		if(!$page)
		{

		}
		else
		{
			$content = $response->getContent();


			// replace pattern
			$original_patterns = DB()->query("SELECT * FROM xcore_pattern WHERE deleted = 'no' and active = 'yes'")->fetchAll();
			$patterns = $original_patterns;
			foreach($original_patterns as $pattern)
				$content = str_replace($pattern['pattern'], $pattern['content'], $content);

			// replace x-plugin
			$pattern = '/<x-plugin\s+(?:[^>]*\s+)?data-name="([^"]+)"[^>]*>([\s\S]*?)<\/x-plugin>/';
			while(preg_match($pattern, $content, $matches))
			{
				$data_name = $matches[1];
				$parameters = $matches[2];

				if(empty($parameters))
					$parameters = '{}';

				$plugin_text = "{{ xPlugin(\"{$data_name}\", {$parameters}) }}";
				$content = str_replace($matches[0], $plugin_text, $content);
			}

			if(str_contains($content, "{{ xPlugin"))
			{
				$template = App()->twig->createTemplate($content);
				$content = $template->render();
			}

			// frontend plugins
			$frontend_plugins = \Core\Config::get('frontend/page/plugins');
			foreach($frontend_plugins as $fp)
			{
				while(preg_match($fp['pattern'], $content, $matches))
				{
					$tmp = explode(' - #', $matches[2]);
					$obj_id = (int)end($tmp);

					$class_instance = $fp['controller'];
					$replace = call_user_func_array("{$class_instance}::{$fp['method']}", [$obj_id, $matches[2], $data]);
					$content = str_replace($matches[0], $replace, $content);

					// replace pattern
					if($fp['reload_pattern'])
					{
						foreach($patterns as $pattern)
							$content = str_replace($pattern['pattern'], $pattern['content'], $content);
					}
				}
			}

			// replace x-plugin
			$pattern = '/<x-plugin\s+(?:[^>]*\s+)?data-name="([^"]+)"[^>]*>([\s\S]*?)<\/x-plugin>/';
			while(preg_match($pattern, $content, $matches))
			{
				$data_name = $matches[1];
				$parameters = $matches[2];

				if(empty($parameters))
					$parameters = '{}';

				$plugin_text = "{{ xPlugin(\"{$data_name}\", {$parameters}) }}";
				$content = str_replace($matches[0], $plugin_text, $content);
			}

			if(str_contains($content, "{{ xPlugin"))
			{
				$template = App()->twig->createTemplate($content);
				$content = $template->render();
			}

			// reload pattern
			foreach($patterns as $pattern)
				$content = str_replace($pattern['pattern'], $pattern['content'], $content);


			$content = str_replace("@theme_url", $theme_uri, $content);
			$content = str_replace("@theme_assets_css", "{$theme_uri}/assets/css", $content);
			$content = str_replace("@theme_assets_js", "{$theme_uri}/assets/js", $content);
			$content = str_replace("@theme_assets", "{$theme_uri}/assets", $content);
			$content = str_replace("@theme", \Core\Config::get('frontend/theme'), $content);

			foreach($page as $var => $val)
			{
				$content = str_replace("@page_{$var}", "{$val}", $content);
			}

			// page pattern
			foreach(self::$pattern as $pattern)
			{
				$content = str_replace($pattern['pattern'], $pattern['replace'], $content);
			}

			// replace second time
			foreach($original_patterns as $pattern)
				$content = str_replace($pattern['pattern'], $pattern['content'], $content);

			$content = str_replace(" contenteditable", ' x-contenteditable', $content);

			// xMinifier detected **************************************************************************************
			if(!\Core\Config::get('frontend/minify'))
			{
				$content = str_erase(['<x-minifier>', '</x-minifier>'], $content);
			}
			else
			{
				if(\Core\Config::get('debug'))
				{
					$content = str_replace(['<x-minifier>', '</x-minifier>'], ['<!-- x-minifier:debug -->', '<!-- /x-minifier:debug -->'], $content);
				}
				else
				{
					$pattern = '#<x-minifier>(.*?)</x-minifier>#s';
					preg_match($pattern, $content, $matches);
					if(isset($matches[1]))
					{
						$file_compiled_css = APP_PATH."{$theme_uri}/assets/css/x-minifier--v".\Core\Config::get("frontend/assets/version").".min.css";
						$file_compiled_js = APP_PATH."{$theme_uri}/assets/js/x-minifier--v".\Core\Config::get("frontend/assets/version").".min.js";

						if(\Core\Config::get('debug'))
						{
							// compile css
							$pattern_css = '/<link\s+rel="stylesheet"\s+href="([^"]+)"/';
							preg_match_all($pattern_css, $matches[1], $matches_css);

							if(!empty($matches_css[1]))
							{
								$css = '';
								foreach($matches_css[1] as $css_file)
								{
									// if(!empty($css))$css .= "\n";
									// $css .= "/** file:{$css_file} **/\n";
									$current_file = APP_PATH."/".ltrim($css_file, "/");

									$css_min = file_get_contents($current_file);
									$css_min = preg_replace('!/\*.*?\*/!s', '', $css_min);
									$css_min = preg_replace('/\n\s*\n/', "\n", $css_min);
									$css_min = preg_replace('/[\r\n\t]+/', ' ', $css_min);
									$css_min = preg_replace('/\s*([:;{},])\s*/', '$1', $css_min);

									$css .= $css_min;
								}

								if(!file_put_contents($file_compiled_css, $css))
								{
									die("Error while creating css min file `{$file_compiled_css}`");
								}
							}

							// compile js
							$pattern_js = '/<script\s+src="([^"]+)"/';
							preg_match_all($pattern_js, $matches[1], $matches_js);

							if(!empty($matches_js[1]))
							{
								$js = '';
								foreach($matches_js[1] as $js_file)
								{
									if(!empty($js))$js .= "\n";
									$js .= "// file:{$js_file}\n";

									$js_min = file_get_contents(APP_PATH."/".ltrim($js_file, "/"));
									$js_min = preg_replace('/\n\s*\n/', "\n", $js_min);
									$js_min = preg_replace('/[\r\n\t]+/', ' ', $js_min);
									$js_min = preg_replace('/\s*([:=,;{}()&|<>!+\-*\/])\s*/', '$1', $js_min);
									$js .= $js_min;
								}

								if(!file_put_contents($file_compiled_js, $js))
								{
									die("Error while creating css min file `{$file_compiled_js}`");
								}
							}
						}

						// replace all contents
						$replace = "";
						if(file_exists($file_compiled_css))
						{
							$abs_file_compiled_css = get_absolute_path($file_compiled_css);
							$replace .= "<link rel=\"stylesheet\" href=\"{$abs_file_compiled_css}\" />\n";
						}
						if(file_exists($file_compiled_js))
						{
							$abs_file_compiled_js = get_absolute_path($file_compiled_js);
							$replace .= "<script src=\"{$abs_file_compiled_js}\" />\n";
						}

						$content = str_replace($matches[0], $replace, $content);

					}

				}
			}

			// html minify *********************************************************************************************
			if(!\Core\Config::get('debug') && \Core\Config::get('frontend/minify'))
			{
				$content = preg_replace('/\s+/', ' ', $content);
				$content = preg_replace('/<!--(?!<!)[^\[>].*?-->/', '', $content);
				$content = preg_replace('/>\s+</', '><', $content);
			}

			$response->setContent($content);
		}

		// frontbar
		if($page && \Model\User::isLogon() && \Model\User::hasRight('edit', 'page'))
		{
			$content = $response->getContent();

			$page_edit_url = \Core\Config::get('frontend/toolbar_url_prefix')."/".\Core\Config::get('backend/dirname')."/page/edit/{$page['id']}/";
			if(!empty(\Model\Page::$page_edit_url))
			{
				$page_edit_url = \Model\Page::$page_edit_url;
				if($page_edit_url[0] != '/' && !str_starts_with($page_edit_url, 'http'))
					$page_edit_url = '/'.$page_edit_url;

				$page_edit_url = \Core\Config::get('frontend/toolbar_url_prefix').$page_edit_url;
			}

			$pages_zone_url = \Core\Config::get('frontend/toolbar_url_prefix')."/".\Core\Config::get('backend/dirname')."/page/?xcore_page_zone_id={$page['xcore_page_zone_id']}&language={$page['language']}&auto_select_id={$page['id']}";

			$frontbar = file_get_contents(APP_PATH."/core/module/core-frontend/page/view/toolbar.html");
			$frontbar = str_replace('[PAGE_ID]', $page['id'], $frontbar);
			$frontbar = str_replace('[BACKEND_URL]', \Core\Config::get('frontend/toolbar_url_prefix')."/".\Core\Config::get('backend/dirname')."/", $frontbar);
			$frontbar = str_replace('[BACKEND_PAGE_EDIT_URL]', $page_edit_url, $frontbar);
			$frontbar = str_replace('[BACKEND_PAGE_ZONE_URL]', $pages_zone_url, $frontbar);

			$content = str_replace('</body>', "{$frontbar}</body>", $content);


			$response->setContent($content);
		}
		
		

		// tracking visitor
		$current_url = (!isset($_SERVER['REQUEST_URI'])) ? '' : $_SERVER['REQUEST_URI'];
		$current_domain = (!isset($_SERVER['SERVER_NAME'])) ? '' : str_erase('www.', strtolower($_SERVER['SERVER_NAME']));

		if(
			$status != 404 &&
			\Core\Config::get('frontend/analytics/enabled') &&
			!APP_CLI_MODE &&
			in_array($current_domain, \Core\Config::get('frontend/analytics/domains_allowed_collect')) &&
			(isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'get') &&
			!in_array(getVisitorIp(), \Core\Config::get('frontend/analytics/ips_excluded'))
		)
		{
			$detect = new \Detection\MobileDetect();

			$user_ip = 	getVisitorIp();
			$user_agent = $detect->getUserAgent();



			// exclude bot
			$allow_tracking = true;
			$excluded = \Core\Config::get('frontend/analytics/user_agent_regex_excluded');
			foreach($excluded as $exclude)
			{
				if(empty($user_agent) || preg_match($exclude, $user_agent))
				{
					$allow_tracking = false;
					break;
				}
			}


			$referer = (!isset($_SERVER['HTTP_REFERER'])) ? '' : $_SERVER['HTTP_REFERER'];
			$referer_parsed = parse_url($referer);



			if(!empty($referer) && isset($referer_parsed['host']))
			{
				$referer_domain = str_erase('www.', $referer_parsed['host']);
				$referer_domain = strtolower($referer_domain);

				if(
					in_array($referer_domain, \Core\Config::get('frontend/analytics/domains_allowed_collect')) ||
					in_array($referer_domain, \Core\Config::get('frontend/analytics/referer_domains_cleared'))
				)
					$referer = '';
			}


			$url_query_string = $_SERVER['QUERY_STRING'];
			$url_domain = $_SERVER['HTTP_HOST'];

			$current_url = str_erase("?{$url_query_string}", $current_url);
			$current_url = "https://{$_SERVER['HTTP_HOST']}{$current_url}";

			$user_device = 'desktop';
			if($detect->isMobile()) $user_device = 'mobile';
			elseif($detect->isTablet()) $user_device = 'tablet';

			$user_browser = 'unknown';
			$user_browser_version = '';
			$browsers = [
				'Firefox' => 'Firefox/([0-9\.]+)',
				'Chrome'  => 'Chrome/([0-9\.]+)',
				'Safari'  => 'Version/([0-9\.]+).*Safari',
				'Safari Mobile'  => 'Safari/([0-9\.]+)',
				'Opera'   => 'Opera/([0-9\.]+)|OPR/([0-9\.]+)', // Opera can show up as 'Opera' or 'OPR'
				'Edge'    => 'Edg/([0-9\.]+)', // Edge user agent string starts with 'Edg'
				'IE'      => 'MSIE ([0-9\.]+)|Trident.*rv:([0-9\.]+)' // For Internet Explorer
			];

			foreach ($browsers as $browser => $pattern)
			{
				if(preg_match("#$pattern#i", $user_agent, $matches))
				{
					$user_browser = $browser;
					$user_browser_version = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : 'Unknown Version');
					break; // Exit the loop once we find the first matching browser
				}
			}


			// os
			$user_os = 'unknown';
			$user_os_version = '';
			$os_platforms = [
				'Windows'      => 'Windows NT ([0-9\.]+)?',
				'Mac OS'       => 'Macintosh;.*Mac OS X ([0-9\._]+)',
				'Linux'        => 'Linux',
				'iOS'          => 'iPhone OS ([0-9\._]+)',
				'Android'      => 'Android ([0-9\.]+)',
				'BlackBerry'   => 'BlackBerry',
				'Windows Phone' => 'Windows Phone ([0-9\.]+)',
			];

			foreach ($os_platforms as $os => $pattern)
			{
				if (preg_match("/$pattern/i", $user_agent, $matches))
				{
					$user_os = strtolower($os);
					$user_os_version =  !empty($matches[1]) ? $matches[1] : '';
					break;
				}
			}

			$user_os_version = str_replace('_', '.', $user_os_version);

			// user_os_label
			$user_os_label = '';
			if ($user_os == 'windows')
			{
				$user_os_label = $user_os . ' ' . $user_os_version;

				if(preg_match('/Windows NT 10.0/i', $user_agent))
					$user_os_label = 'windows 10';
				elseif(preg_match('/Windows NT 11.0/i', $user_agent))
					$user_os_label = 'windows 11';
				elseif (preg_match('/Windows NT 6.3/i', $user_agent))
					$user_os_label = 'windows 8';
				elseif (preg_match('/Windows NT 6.2/i', $user_agent))
					$user_os_label = 'windows 8';
				elseif (preg_match('/Windows NT 6.1/i', $user_agent))
					$user_os_label = 'windows 7';
				elseif (preg_match('/Windows NT 5.1/i', $user_agent))
					$user_os_label = 'windows xp';

			} elseif($user_os === 'mac OS') {
				$user_os_label = $user_os . ' X ' . $user_os_version;
			} elseif($user_os === 'ios') {
				$user_os_label = $user_os . ' ' . $user_os_version;
			} elseif($user_os === 'android') {
				$user_os_label = $user_os . ' ' . $user_os_version;
			} else {
				$user_os_label = $user_os; // For Linux or other OS
			}




			$user_lang = '';
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				$languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				$primary_language = substr($languages[0], 0, 2);
				$user_lang = $primary_language;
			}


			if(!empty($user_browser_version))
			{
				$tmp = explode('.', $user_browser_version);
				$user_browser_version = $tmp[0];
				if(count($tmp) >= 2)
					$user_browser_version .= '.'.$tmp[1];
			}

			$user_browser = strtolower($user_browser);
			$user_browser = str_erase(' mobile', $user_browser);

			// store result
			$f = [];
			$f['date'] = now();
			$f['user_agent'] = $user_agent;
			$f['user_ip'] = $user_ip;
			$f['user_device'] = strtolower($user_device);
			$f['user_browser'] = strtolower($user_browser);
			$f['user_browser_version'] = strtolower($user_browser_version);
			$f['user_os'] = strtolower($user_os);
			$f['user_os_label'] = strtolower($user_os_label);
			$f['user_os_version'] = strtolower($user_os_version);
			$f['user_lang'] = strtolower($user_lang);
			$f['referer'] = $referer;
			$f['url'] = $current_url;
			$f['url_domain'] = $url_domain;

			if($allow_tracking)
				DB('xcore_page_visitor')->insert($f);
		}




		return $response;

	}


	public static function error404($message="page not found"):\Core\Response
	{
		$theme_uri = "/theme/".\Core\Config::get('frontend/theme');
		$view = "{$theme_uri}/@error404.twig";

		$data = [];
		$data['message'] = $message;

		return View($view, $data);
	}


	/**
	 * get url from page
	 *
	 * @param string $url
	 * @param string $language
	 * @param int $id
	 * @param string $name
	 * @return string
	 */
	public static function getUrl(string $url='', string $language='', int $id=0, string $name='', string $website=''):string
	{
		if(!empty($url))
		{
			if(!empty($website))
				$url = rtrim($website, '/').$url;
			return $url;
		}

		$slug = slugify($name);
		$page_pattern = \Core\Config::get('frontend/page/url_pattern');

		$uri = $page_pattern;
		$uri = str_replace('{locale}', $language, $uri);
		$uri = str_replace('{id}', $id, $uri);
		$uri = str_replace('{slug}', $slug, $uri);

		if(!empty($website))
			$uri = rtrim($website, '/').$uri;

		return $uri;
	}




	/**
	 * @param int $zone_id
	 * @param string $language
	 * @return array|false
	 */
	public static function getAll(int $zone_id, string $language, int $xcore_page_id=0, string $fields="", string $sql_added="", array $params_added=[]):array|false
	{
		$pages = [];

		if(empty($fields))
		{
			$fields = "id, type, name, status, xcore_page_id, url, (select website from xcore_page_zone where id = xcore_page_zone_id) as zone_website";
		}
		else
		{
			if(!str_contains($fields, 'zon_website'))
				$fields .= ", (select website from xcore_page_zone where id = xcore_page_zone_id) as zone_website";
		}

		$params = $params_added;
		$params[':zone_id'] = $zone_id;
		$params[':language'] = $language;
		$params[':xcore_page_id'] = $xcore_page_id;

		$where = "xcore_page_zone_id = :zone_id and language = :language and xcore_page_id = :xcore_page_id ";
		if(!empty($sql_added)) $where .= " AND {$sql_added}";
		$records = self::all($where, $params, $fields, "", "position");
		foreach($records as $p)
		{
			$p['_childrens'] = self::getAll($zone_id, $language, $p['id'], $fields, $sql_added, $params_added);

			if(isset($p['url']))
			{
				$p['url'] = self::getUrl($p['url'], $language, $p['id'], $p['name'], $p['zone_website']);
			}

			$pages[] = $p;
		}


		return $pages;
	}


	public static function getAllChildrenIds(int $page_id):array
	{
		$pages = [];

		$records = self::allOne("xcore_page_id = :xcore_page_id", [':xcore_page_id' => $page_id], 'id');
		foreach($records as $p_id)
		{
			$pages[] = $p_id;

			$ids = self::getAllChildrenIds($p_id);
			foreach($ids as $c_id)
				$pages[] = $c_id;
		}

		return $pages;
	}


	/**
	 * render page thumbs
	 *
	 * @param int $parent_page_id
	 * @return void
	 */
	public static function thumbsRender(int $zone_id, string $language, int $parent_page_id):string
	{

	}


}