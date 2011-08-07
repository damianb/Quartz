<?php
/**
 *
 *===================================================================
 *
 *  Quartz
 *-------------------------------------------------------------------
 * @category    Quartz
 * @package     Quartz
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/Quartz
 *
 *===================================================================
 *
 */

namespace Codebite\Quartz;
use Codebite\Quartz\Internal\QuartzException;
use Codebite\Quartz\Internal\RequirementException;
use OpenFlame\Framework\Autoloader;
use OpenFlame\Framework\Core;
use OpenFlame\Framework\Dependency\Injector;
use OpenFlame\Framework\Event\Instance as Event;
use OpenFlame\Framework\Exception\Handler as ExceptionHandler;
use OpenFlame\Framework\Utility\JSON;
use OpenFlame\Dbal\Connection as DbalConnection;

class Site
{
	/**
	 * @var \Codebite\Quartz\Site - The singleton instance of this object.
	 */
	private static $instance;

	/**
	 * @var \OpenFlame\Framework\Dependency\Injector - The OpenFlame Framework dependency injector object
	 */
	protected $injector;

	/**
	 * @var integer - The error_reporting() level on startup
	 */
	protected $startup_reporting;

	const EVENT_NOBREAK = 1;
	const EVENT_MANUALBREAK = 2;
	const EVENT_RETURNBREAK = 3;

	/**
	 * @return \Codebite\Quartz\Site - Returns the singleton instance created.
	 */
	public static function getInstance()
	{
		if(empty(static::$instance))
		{
			static::$instance = new self();
		}

		return static::$instance;
	}

	public static function definePaths($site_root, $include_root = '/includes/')
	{
		// define the root paths
		if(!defined('Codebite\\Quartz\\SITE_ROOT'))
		{
			define('Codebite\\Quartz\\SITE_ROOT', rtrim($site_root, '\\/') . '/');
		}
		if(!defined('Codebite\\Quartz\\INCLUDE_ROOT'))
		{
			define('Codebite\\Quartz\\INCLUDE_ROOT', rtrim($site_root . $include_root, '\\/') . '/');
		}
	}

	protected function __construct()
	{
		// The original error_reporting() setting.
		$this->startup_reporting = @error_reporting();

		// setup the autoloader
		require \Codebite\Quartz\INCLUDE_ROOT . 'OpenFlame/Framework/Autoloader.php';
		Autoloader::register(\Codebite\Quartz\INCLUDE_ROOT);

		// register the exception handler
		ExceptionHandler::register();

		// Storing in a property and in a local variable here so we can pass it through to the injectors themselves
		$this->injector = Injector::getInstance();

		// Instantiate the timer ASAP so that it's somewhat accurate :)
		Core::setObject('timer', new \OpenFlame\Framework\Utility\Timer());

		// some pre-flight checks
		$this->setConfigPath(\Codebite\Quartz\SITE_ROOT . '/data/config/');
		$this->setExceptionPage();
		$this->setDebugOptions(true);
		$this->checkRequirements();
	}

	/**
	 * @ignore
	 */
	private function checkRequirements()
	{
		if(@ini_get('register_globals'))
		{
			throw new RequirementException('Application will not run with register_globals enabled; please disable register_globals to run this application.', 1001);
		}
		if(@get_magic_quotes_gpc())
		{
			throw new RequirementException('Application will not run with magic_quotes_gpc enabled; please disable magic_quotes_gpc to run this application.', 1002);
		}
		if(@get_magic_quotes_runtime())
		{
			throw new RequirementException('Application will not run with magic_quotes_runtime enabled; please disable magic_quotes_runtime to run this application.', 1003);
		}
	}

	/**
	 * Commonly used functionality
	 */

	public function loadConfig($configs)
	{
		if(is_file(Core::getConfig('path.config') . $configs . '.json'))
		{
			$config_data = JSON::decode(Core::getConfig('path.config') . $configs . '.json');
			foreach($config_data as $config_name => $config_value)
			{
				Core::setConfig($config_name, $config_value);
			}
		}
		elseif(is_array($configs))
		{
			foreach($configs as $config_name => $config_value)
			{
				Core::setConfig($config_name, $config_value);
			}
		}
		else
		{
			throw new QuartzException('Invalid data provided for $configs parameter', 2001);
		}

		return $this;
	}

	public function setConfigPath($config_path)
	{
		Core::setConfig('path.config', $config_path);
	}

	public function setBaseURL($base_url)
	{
		$this->injector->get('router')->setBaseURL($base_url);
		$this->injector->get('asset')->setBaseURL($base_url);
		$this->injector->get('url')->setBaseURL($base_url);

		return $this;
	}

	public function setExceptionPage($page_html = NULL)
	{
		if($page_html === NULL)
		{
			$page_html = '
<!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta charset="utf-8" />
		<title>%1$s</title>
		<style type="text/css">
		* { margin: 0; padding: 0; } html { font-size: 100%%; height: 100%%; margin-bottom: 1px; background-color: #FFFFFF; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #825353; background: #FFFFFF; font-size: 62.5%%; margin: 0; padding: 20px; } a:link, a:active, a:visited { color: #AA1F00; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } header { height: 30px; } footer { clear: both; font-size: 1em; text-align: center; }
		#errorpage #wrap { padding: 12px 20px; min-width: 700px; border-radius: 12px; margin: 4px 0; background-color: #FEEFDA; border: solid 1px #F7941D; } #errorpage #content { margin-top: 10px; margin-bottom: 5px; padding-bottom: 5px; color: #333333; font: bold 1.15em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%%;} #errorpage #content #backtrace { border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; }
		.syntaxbg { color: #FFFFFF; } .syntaxcomment { color: #FF8000; } .syntaxdefault { color: #0000BB; } .syntaxhtml { color: #000000; } .syntaxkeyword { color: #007700; } .syntaxstring { color: #DD0000; }
		</style>
	</head>
	<body id="errorpage">
		<div id="wrap">
			<header>
				<h2>%1$s</h2>
			</header>
			<section id="content">
				<p>%2$s</p>
			</section>
		</div>
		<footer>
			Powered by the <a href="http://openflame-project.org">OpenFlame Framework</a> and <a href="https://github.com/damianb/Quartz/">Quartz</a>
		</footer>
	</body>
</html>';
		}

		ExceptionHandler::setPageFormat($page_html);

		return $this;
	}

	public function setDebugOptions($debug_enabled, $debug_unwrap_count = 3)
	{
		if((bool) $debug_enabled)
		{
			// Force errors to be displayed
			@error_reporting(E_ALL);
			@ini_set("display_errors", "On");
			ExceptionHandler::enableDebug();
		}
		else
		{
			@error_reporting($this->startup_reporting);
			@ini_set("display_errors", "Off");
			ExceptionHandler::disableDebug();
		}
		ExceptionHandler::setUnwrapCount((int) $debug_unwrap_count);

		return $this;
	}

	public function setAssets($assets = NULL, $asset_path = '/style/')
	{
		$asset_manager = $this->injector->get('asset');

		$asset_path = rtrim($asset_path, '/');

		// If we didn't get an array of assets, try to
		if($assets === NULL)
		{
			$assets = Core::getConfig('site.assets');
		}
		elseif(is_string($assets))
		{
			$assets = Core::getConfig($assets);
		}
		elseif(!is_array($assets))
		{
			// Only NULL, a string, or an array are allowed.  If none of the above is provided, we kerboom.
			throw new QuartzException('Invalid data provided for $assets parameter', 2002);
		}

		foreach($assets as $type => $_assets)
		{
			foreach($_assets as $asset_name => $asset_url)
			{
				$asset_manager->registerCustomAsset($type, $asset_name)
					->setURL($asset_path . '/' . $type . '/' . $asset_url);
			}
		}

		return $this;
	}

	public function setRoutes($routes = NULL)
	{
		// @todo use $routes as an override for config name
		$cache = $this->injector->get('cache');
		$router = $this->injector->get('router');

		if($routes === NULL)
		{
			$routes = Core::getConfig('site.routes');
		}
		elseif(is_string($routes))
		{
			$routes = Core::getConfig($routes);
		}
		elseif(!is_array($routes))
		{
			// Only NULL, a string, or an array are allowed.  If none of the above is provided, we kerboom.
			throw new QuartzException('Invalid data provided for $routes parameter', 2003);
		}

		if($cache->dataCached('page_routes'))
		{
			$route_data = $cache->loadData('page_routes');
			$router->loadFromFullRouteCache($route_data);
		}
		else
		{
			// Grab the page routes from the config
			$router->newRoutes($routes);

			$home = $router->newRoute($routes['home']['path'], $routes['home']['callback']);
			$router->storeRoute($home)
				->setHomeRoute($home);

			$error = $router->newRoute($routes['error']['path'], $routes['error']['callback']);
			$router->storeRoute($error)
				->setErrorRoute($error);

			$cache->storeData('page_routes', $router->getFullRouteCache());
		}

		return $this;
	}

	public function setInjector($name, \Closure $injector)
	{
		$this->injector->setInjector($name, $injector);

		return $this;
	}

	public function setListener($event_name, $priority, \Closure $listener)
	{
		$this->injector->get('dispatcher')->register($event_name, $priority, $listener);

		return $this;
	}

	public function triggerEvent($event_name, $trigger_type = self::EVENT_MANUALBREAK)
	{
		$dispatcher = $this->injector->get('dispatcher');

		if($trigger_type === self::EVENT_NOBREAK)
		{
			return $dispatcher->trigger(Event::newEvent($event_name));
		}
		elseif($trigger_type === self::EVENT_MANUALBREAK)
		{
			return $dispatcher->triggerUntilBreak(Event::newEvent($event_name));
		}
		elseif($trigger_type === self::EVENT_RETURNBREAK)
		{
			return $dispatcher->triggerUntilReturn(Event::newEvent($event_name));
		}
		else
		{
			throw new QuartzException('Invalid trigger type specified', 2004);
		}
	}

	public function connectToDatabase($type = NULL)
	{
		$options = Core::getConfigNamespace('db');

		if($type === NULL)
		{
			if(!isset($options['type']))
			{
				throw new QuartzException('No database type specified for connection', 2005);
			}
			$type = $options['type'];
		}

		$dsn = $username = $password = $db_options = NULL;
		switch($type)
		{
			case 'sqlite':
				if(!isset($options['file']))
				{
					throw new QuartzException('No database file specified for sqlite database connection', 2006);
				}
				$dsn = sprintf('sqlite:%s', $options['file']);
			break;

			case 'mysql':
			case 'mysqli': // in case someone doesn't know that pdo doesn't do mysqli
				if(!isset($options['host']) || !isset($options['name']) || !isset($options['username']))
				{
					throw new QuartzException('Missing or invalid database connection parameters, cannot connect to database', 2007);
				}
				$dsn = sprintf('mysql:host=%s;dbname=%s', ($options['host'] ?: 'localhost'), $options['name']);
				$username = $options['username'];
				$password = $options['password'] ?: '';
				$db_options = array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				);
			break;

			case 'pgsql':
			case 'postgres':
			case 'postgresql':
				if(!isset($options['host']) || !isset($options['name']) || !isset($options['username']))
				{
					throw new QuartzException('Missing or invalid database connection parameters, cannot connect to database', 2007);
				}
				$dsn = sprintf('pgsql:host=%s;dbname=%s', ($options['host'] ?: 'localhost'), $options['name']);
				$username = $options['username'];
				$password = $options['password'] ?: '';
			break;

			default:
				throw new QuartzException('Invalid or unsupported database type specified for connection', 2008);
			break;
		}

		DbalConnection::getInstance()
			->connect($dsn, $username, $password, $options);

		return $this;
	}

	/**
	 * Handle dep injection here.
	 */

	public function __isset($name)
	{
		return $this->injector->injectorPresent($name);
	}

	public function __get($name)
	{
		return $this->injector->get($name);
	}


	/**
	 * Post-construct triggers
	 */

	public function init()
	{
		$injector = $this->injector;

		// Define a bunch of injectors
		$this->setInjector('router', function() {
			return new \OpenFlame\Framework\Router\Router();
		});

		$this->setInjector('input', function() {
			return new \OpenFlame\Framework\Input\Handler();
		});

		$this->setInjector('template', function() {
			return new \OpenFlame\Framework\Twig\Variables();
		});

		$this->setInjector('asset', function() {
			return new \OpenFlame\Framework\Asset\Manager();
		});

		$this->setInjector('asset_proxy', function() use($injector) {
			return new \OpenFlame\Framework\Asset\Proxy($injector->get('asset'));
		});

		$this->setInjector('dispatcher', function() {
			return new \OpenFlame\Framework\Event\Dispatcher();
		});

		$this->setInjector('processor', function() {
			return new \Codebite\Quartz\Page\Processor();
		});

		$this->setInjector('language', function() {
			return new \OpenFlame\Framework\Language\Handler();
		});

		$this->setInjector('language_proxy', function() use($injector) {
			return new \OpenFlame\Framework\Language\Proxy($injector->get('language'));
		});

		$this->setInjector('header', function() {
			return new \OpenFlame\Framework\Header\Manager();
		});

		$this->setInjector('url', function() {
			return new \OpenFlame\Framework\URL\Builder();
		});

		$this->setInjector('url_proxy', function() use($injector) {
			return new \OpenFlame\Framework\URL\BuilderProxy($injector->get('url'));
		});

		$this->setInjector('hasher', function() {
			return new \OpenFlame\Framework\Security\Hasher();
		});

		$this->setInjector('seeder', function() {
			return new \OpenFlame\Framework\Security\Seeder();
		});

		$this->setInjector('debugtime', function() {
			return new \Codebite\Quartz\Debug\Tracker();
		});

		$this->setInjector('twig', function() {
			$twig = new \OpenFlame\Framework\Twig\Wrapper();
			$twig->setTwigRootPath(Core::getConfig('twig.lib_path') ?: \Codebite\Quartz\INCLUDE_ROOT . 'vendor/Twig/lib/Twig/')
				->setTwigCachePath((Core::getConfig('twig.cache_path') ?: \Codebite\Quartz\SITE_ROOT . 'cache/twig/'))
				->setTemplatePath((Core::getConfig('twig.template_path') ?: \Codebite\Quartz\SITE_ROOT . 'data/template/'))
				->setTwigOption('debug', (Core::getConfig('twig.debug') ?: false));
			$twig->initTwig();

			return $twig;
		});

		$this->setInjector('cache_engine', function() {
			$cache_engine = new \OpenFlame\Framework\Cache\Engine\File\FileEngineJSON();
			$cache_engine->setCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/');
			return $cache_engine;
		});

		$this->setInjector('cache', function() use($injector) {
			$cache = new \OpenFlame\Framework\Cache\Driver();
			$cache->setEngine($injector->get('cache_engine'));
			return $cache;
		});

		/**
		 * Define some events
		 */

		// Snag control of the headers
		$this->setListener('page.prepare', -15, function(Event $event) use($injector) {
			$header_manager = $injector->get('header');
			$header_manager->snagHeaders();
		});

		// Load the language file
		$this->setListener('page.prepare', 15, function(Event $event) use($injector) {
			$language = $injector->get('language');
			$language_entries = JSON::decode((Core::getConfig('language.file') ?: \Codebite\Quartz\SITE_ROOT . '/data/language/en-us.json'));
			$language->loadEntries($language_entries);
		});

		// Create the template proxies and load them into twig
		$this->setListener('page.prepare', 18, function(Event $event) use($injector) {
			$twig = $injector->get('twig');

			$twig_env = $twig->getTwigEnvironment();
			$twig_env->addGlobal('timer', $injector->get('timer'));
			$twig_env->addGlobal('asset', $injector->get('asset_proxy'));
			$twig_env->addGlobal('language', $injector->get('language_proxy'));
			$twig_env->addGlobal('url', $injector->get('url_proxy'));
		});

		// touch the $_SERVER superglobal so that the input handler can make use of it
		$this->setListener('page.execute', -20, function(Event $event) use($injector) {
			$_SERVER;
		});

		$this->setListener('page.execute', 0, function(Event $event) use($injector) {
			$dispatcher = $injector->get('dispatcher');
			$dispatcher->triggerUntilBreak(Event::newEvent('page.route'));
		});

		// Execute the page logic
		$this->setListener('page.route', 0, function(Event $event) use($injector) {
			$dispatcher = $injector->get('dispatcher');
			$input = $injector->get('input');
			$router = $injector->get('router');
			$header = $injector->get('header');
			$debugtime = $injector->get('debugtime');

			$request_uri = $input->getInput('SERVER::REQUEST_URI', '/')
				->getClean();

			$dbg_instance = $dbg_instance2 = NULL;
			$debugtime->newEntry('app->route', '', $dbg_instance);

			$page = $router->processRequest($request_uri)
				->fireCallback();
			Core::setObject('page', $page);

			$debugtime->newEntry('app->route', 'Application route parsing', $dbg_instance, array('request' => $request_uri));
			$quartz->debugtime->newEntry('app->executepage', '', $dbg_instance2);

			try
			{
				$page->executePage();
			}
			catch(\Codebite\Quartz\Exception\RedirectException $e)
			{
				// *punt* WHEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE~
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: " . $e->getMessage());
				exit;
			}
			catch(\Codebite\Quartz\Exception\ServerErrorException $e)
			{
				$page = $router->getErrorRoute()
					->setRequestDataPoint('code', ($e->getCode() ?: 500))
					->setRequestDataPoint('message', $e->getMessage())
					->fireCallback();
				$page->executePage();
				Core::setObject('page', $page);
			}

			$quartz->debugtime->newEntry('app->executepage', 'Application page execution complete', $dbg_instance2);
		});

		// Enable invalid asset exceptions (low priority listener!)
		$this->setListener('page.execute', 19, function(Event $event) use($injector) {
			$assets = $injector->get('asset');
			$assets->enableInvalidAssetExceptions();
		});

		// Display the page
		$this->setListener('page.display', 0, function(Event $event) use($injector) {
			$page = Core::getObject('page');
			$twig = $injector->get('twig');
			$template = $injector->get('template');
			$header = $injector->get('header');
			$debugtime = $injector->get('debugtime');

			$twig_env = $twig->getTwigEnvironment();
			$twig_page = $twig_env->loadTemplate($page->getTemplateName());
			try
			{
				ob_start();
				$dbg_instance = NULL;
				$debugtime->newEntry('app->display', 'Rendering page', $dbg_instance);
				$html = $twig_page->render($template->fetchAllVars());
				echo $html;

			}
			catch(\Exception $e)
			{
				ob_clean();
				throw $e;
			}

			// Set our content-length header, and send all headers.
			$header->setHeader('Content-Length', ob_get_length());
			$header->sendHeaders();
			ob_end_flush();
		});

		// Do some basic setup.
		$this->loadConfig('config');

		$this->setDebugOptions((bool) Core::getConfig('site.debug'));
		$this->setBaseURL(Core::getConfig('page.base_url') ?: '/');

		return $this;
	}

	/**
	 * Page runtime stuff
	 */
	public function pagePrepare()
	{
		$this->triggerEvent('page.prepare');

		return $this;
	}

	public function pageExecute()
	{
		$this->triggerEvent('page.execute');

		return $this;
	}

	public function pageDisplay()
	{
		$this->triggerEvent('page.display');

		return $this;
	}
}
