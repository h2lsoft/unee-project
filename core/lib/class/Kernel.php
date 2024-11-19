<?php

namespace Core;

// use APCIterator;
// use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use Elastic\Apm\ExecutionSegmentContextInterface;
use h2lsoft\Data\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;

class Kernel extends HttpKernel {
	
	protected \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher;
	protected $controllerResolver;
	protected \Symfony\Component\HttpFoundation\RequestStack $requestStack;
	protected $argumentResolver;
	
	public $request;
	public string $locale = 'en';
	public bool $is_backend = false;
	public bool $is_frontend = true;
	
	public $db;
	public $pdo;
	public $twig;
	public $plugin;
	public mixed $debugbar = false;
	
	public $validator;
	public array $globals = [];
	
	public function __construct($dispatcher, $controllerResolver, $requestStack, $argumentResolver, Request $request)
	{
		$this->request = $request;
		$this->dispatcher = $dispatcher;
		$this->controllerResolver = $controllerResolver;
		$this->requestStack = $requestStack;
		$this->argumentResolver = $argumentResolver;
		parent::__construct($this->dispatcher, $this->controllerResolver, $this->requestStack, $this->argumentResolver);
		
		
		// app part
		$cur_uri = strtok($request->getRequestUri(), '?');
		$backend_dirname = \Core\Config::get('backend/dirname');
		if(preg_match("#^/{$backend_dirname}/#i", $cur_uri))
		{
			$this->is_backend = true;
			$this->is_frontend = false;
		}
		
		// @todo> init locale
		if($this->is_backend)
		{
			if(!\Model\User::isLogon())
			{
				$browser_lang = @trim(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
				if($browser_lang != '.' && $browser_lang != '..' && strlen($browser_lang) == 2 && file_exists(APP_PATH."/core/i18n/{$browser_lang}.php"));
					$this->locale = $browser_lang;
			}
			else
			{
				$this->locale = \Core\Session::get('auth.language', 'en');
				if(empty(trim($this->locale)))$this->locale = 'en';
			}
		}
		else
		{
			$this->locale = \Core\Config::get('frontend/langs')[0][0];
			/*$browser_lang = @trim(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
			if($browser_lang != '..' && strlen($browser_lang) == 2 && file_exists(APP_PATH."/core/i18n/{$browser_lang}.php"));
			{
				$this->locale = $browser_lang;
			}*/

		}
		
		
		
		// validator
		$this->validator = new Validator($this->locale);
		
		// load twig
		$paths = [];
		$loader = new \Twig\Loader\FilesystemLoader(".");
		$this->twig = new \Twig\Environment($loader, \Core\Config::get('twig/options'));
		$this->twig->getExtension(\Twig\Extension\CoreExtension::class)->setNumberFormat(\Core\Config::get('format/number_decimal'), \Core\Config::get('format/number_separator'), \Core\Config::get('format/number_thousand_separator'));
		
		
		include APP_PATH.'/core/lib/inc/twig_functions.inc.php';
		
		if($this->is_backend)
			include APP_PATH.'/core/lib/inc/twig_functions_backend.inc.php';
		if($this->is_frontend)
			include APP_PATH.'/core/lib/inc/twig_functions_frontend.inc.php';
		
		$files = \Core\Config::get('twig/functions_file');
		foreach($files as $f)
			include $f;
			
		
		// db
		$cfg = \Core\Config::get("db/package/".APP_DB_PACKAGE);
		
		// cli mode
		if(APP_CLI_MODE)
		{
			$cli_db_username = \Core\Config::get('cli/database/username');
			if(!empty($cli_db_username))$cfg['username'] = $cli_db_username;
			
			$cli_db_password = \Core\Config::get('cli/database/password');
			if(!empty($cli_db_password))$cfg['password'] = $cli_db_password;
		}
		
		$this->db = new \Core\DB();
		$this->pdo = $this->db->connect(
			$cfg['driver'],
			$cfg['host'],
			$cfg['username'],
			$cfg['password'],
			$cfg['database'],
			$cfg['port'],
			$cfg['pdo_options']
		);
		
		foreach($cfg['init_queries'] as $sql)
			$this->db->query($sql);
		
		// globals
		$sql = "select * from xcore_globals where deleted = 'no' and package = :package  order by name";
		$recs = $this->db->query($sql, [':package' => APP_PACKAGE])->fetchAll();
		foreach($recs as $rec)
			$this->globals[$rec['name']] = $rec['value'];
		

		// debugbar
		if(\Core\Config::get('debug')  && \Core\Config::get('debug_bar') && $request->getRequestFormat() == 'html' && !APP_CLI_MODE)
		{
			$this->debugbar = new \DebugBar\DebugBar();
			
			$cfg = (array)\Core\Config::get();


			// mask data sensitive
			$stack = [&$cfg];

			while (!empty($stack)) {
				$current_array = &$stack[array_key_last($stack)];
				array_pop($stack);

				foreach ($current_array as $key => &$value)
				{
					if(in_array($key, ['password', 'pass', 'pwd', 'api_key']) || str_contains($key, '.password'))
					{
						if (!is_array($value))
							$value = '*******';
						else
							$stack[] = &$value;

					}
					elseif(is_array($value))
					{
						$stack[] = &$value;
					}

				}

				unset($value);
			}


			
			$cfg['APP_PATH'] = APP_PATH;
			$cfg['APP_PACKAGE'] = APP_PACKAGE;
			$cfg['APP_MAIL_PACKAGE'] = APP_MAIL_PACKAGE;
			$cfg['APP_DB_PACKAGE'] = APP_DB_PACKAGE;
			$cfg['APP_ENV'] = APP_ENV;
			ksort($cfg);
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($cfg, 'CONFIG'));



			$server = (array)$_SERVER;
			ksort($server);
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($server, 'SERVER'));

			
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($this->globals, 'GLOBALS'));
			
			

			
			$cookie = (array)$_COOKIE;
			ksort($cookie);
			
			$__files = (array)$_FILES;
			ksort($__files);

			$session = (array)\Core\Session::get();
			$session['@cookie-parameters'] = session_get_cookie_params();
			ksort($session, SORT_STRING);
			

			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($session, 'SESSION'));
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($cookie, 'COOKIE'));
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($request->query->all(), 'GET'));
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($_POST, 'POST'));
			$this->debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($__files, 'FILES'));
			
			$connection_collector = new \DebugBar\DataCollector\PDO\TraceablePDO($this->pdo);
			$this->debugbar->addCollector(new \DebugBar\DataCollector\PDO\PDOCollector($connection_collector));
			
			$this->debugbar->addCollector(new \DebugBar\DataCollector\MemoryCollector());
			$this->debugbar->addCollector(new \DebugBar\DataCollector\PhpInfoCollector());
			// $app->debugbar->addCollector(new \DebugBar\DataCollector\ExceptionsCollector());
			$this->debugbar->addCollector(new \DebugBar\DataCollector\TimeDataCollector());
			$this->debugbar->addCollector(new \DebugBar\DataCollector\MessagesCollector());
			
		}
		
		
		// plugin
		if($this->is_backend)
		{
			if(\Model\User::isLogon())
			{
				// @todo> user has right plugin_name/plugin_action ?
				$plugin_name = \Model\Plugin::extractName($cur_uri);
				
				if(!in_array($plugin_name, ['@error', 'logout', 'login', 'password', 'password-reset']))
				{
					$rec = $this->db->table(\Model\Plugin::$table)
									->select("
												*,
												(SELECT name FROM xcore_menu WHERE id = xcore_menu_id) AS category_name
												")
									->where('route_prefix_name = :route_prefix_name')
									->limit(1)
									->executeSQL([':route_prefix_name' => $plugin_name])
									->limit(1)
									->fetch();
					
					$bookmarks = $this->db->select('xcore_plugin_id')
										  ->from(\Model\User_Bookmark::$table)
							 			  ->where("xcore_user_id = :xcore_user_id")
										  ->executeSQL([':xcore_user_id' => \Model\User::getUID()])
										  ->fetchAllOne();
					
					$tmp = [];
					if($rec)
					{
						$rec['bookmarked'] = (in_array($rec['id'], $bookmarks));
						
						// breadcrumbs
						
						if($rec['name'] != 'Dashboard')
						{
							// category
							$tmp[] = [
								'name' => $rec['category_name'],
								'url' => "/@backend/{$rec['route_prefix_name']}/"
							];
							
							// @todo> add parent plugin
							
							// plugin
							$tmp[] = [
								'name' => $rec['name'],
								'url' => "/@backend/{$rec['route_prefix_name']}/"
							];
						}
					}

					@$rec['breadcrumbs'] = $tmp;
					
					$this->plugin = $rec;
				}

				// force page link
				$found = $this->db->table(\Model\Plugin::$table)
					->select("id")
					->where('route_prefix_name = :route_prefix_name')
					->where("actions LIKE '%list%'")
					->executeSQL([':route_prefix_name' => 'page'])
					->limit(1)
					->fetch();

				if($found)
				{
					$cur_menu = ['name' => 'page', 'text' => 'New page', 'icon' => 'bi bi-pencil-square', 'action' => '/@backend/page/', 'attributes' => []];
					$GLOBALS['config']['backend']['menu']['user'][] = $cur_menu;
				}

				// force article link
				$found = $this->db->table(\Model\Plugin::$table)
					->select("id")
					->where('route_prefix_name = :route_prefix_name')
					->where("actions LIKE '%list%'")
					->executeSQL([':route_prefix_name' => 'article'])
					->limit(1)
					->fetch();
				if($found)
				{
					$cur_menu = ['name' => 'article', 'text' => 'New article', 'icon' => 'bi bi-newspaper', 'action' => '/@backend/article/', 'attributes' => []];
					$GLOBALS['config']['backend']['menu']['user'][] = $cur_menu;
				}
			}
		}
	}
	
	
	
}