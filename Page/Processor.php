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
		$home = $router->newRoute('/home/', '\\Codebite\\Quartz\\Page\\Instance\\Default::newInstance');
		$router->storeRoute($home)
			->setHomeRoute($home);

		$error = $router->newRoute('/error/', '\\Codebite\\Quartz\\Page\\Instance\\Error::newInstance');
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

	public function executePage()
	{
		$input_handler = Core::getObject('input');
		$router = Core::getObject('router');

		$request_uri = $input_handler->getInput('SERVER::REQUEST_URI')
			->setDefault('/')
			->disableFieldJuggling();

		try
		{
			$router->processRequest($request_uri->getClean())
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
			// the compendium of all the types of server errors.  Oh the joy!
			$server_errors = array(
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				204 => 'No Content',
				205 => 'Reset Content',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found', // Moved Temporarily
				303 => 'See Other',
				304 => 'Not Modified',
				307 => 'Temporary Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				403 => 'Forbidden',
				404 => 'Not Found',
				406 => 'Not Acceptable',
				409 => 'Conflict',
				410 => 'Gone',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
			);

			// dem errors
			$error = isset($server_errors[(int) $e->getCode()]) ? (int) $e->getCode() : 500;

			$router->getErrorRoute()
				->setRequestDataPoint('header', "HTTP/1.0 {$error} {$server_errors[$error]}")
				->setRequestDataPoint('code', $error)
				->setRequestDataPoint('message', $e->getMessage())
				->fireCallback();
		}
	}
}
