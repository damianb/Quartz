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

namespace Codebite\Quartz\Page;
use \OpenFlame\Framework\Core;

/**
 * Quartz - Page processor object,
 * 		Processes the current page request, catches redirects and server "errors", along with handling page routing.
 *
 *
 * @category    Quartz
 * @package     Quartz
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/Quartz
 */
class Processor
{
	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		// touch the $_SERVER superglobal so that the input handler can make use of it
		$_SERVER;
	}

	public function registerDefaultRoutes()
	{
		$router = Core::getObject('router');

		// Set the default "home" route.
		$home_callback = Core::getConfig('page.home_callback') ?: '\\Codebite\\Quartz\\Page\\Instance\\Home::newRoutedInstance';
		$home = $router->newRoute('/home/', $home_callback);
		$router->storeRoute($home)
			->setHomeRoute($home);

		$error_callback = Core::getConfig('page.error_callback') ?: '\\Codebite\\Quartz\\Page\\Instance\\Error::newRoutedInstance';
		$error = $router->newRoute('/error/', $error_callback);
		$router->storeRoute($error)
			->setErrorRoute($error);

		return $this;
	}

	public function loadRoutes()
	{
		$cache = Core::getObject('cache');
		$router = Core::getObject('router');

		if($cache->dataCached('page_routes'))
		{
			$route_data = $cache->loadData('page_routes');
			$router->loadFromFullRouteCache($route_data);
		}
		else
		{
			// Grab the page routes from the config
			$routes = Core::getConfig('page.routes');
			$this->registerDefaultRoutes();
			$router->newRoutes($routes);

			$cache->storeData('page_routes', $router->getFullRouteCache());
		}

		return $this;
	}

	public function run()
	{
		$input_handler = Core::getObject('input');
		$router = Core::getObject('router');

		$request_uri = $input_handler->getInput('SERVER::REQUEST_URI')
			->setDefault('/')
			->disableFieldJuggling();

		try
		{
			$page = $router->processRequest($request_uri->getClean())
				->fireCallback();
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
		}

		return $page;
	}
}
