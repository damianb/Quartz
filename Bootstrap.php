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
use \OpenFlame\Framework\Core;

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

// Set our exception handler
set_exception_handler('\\Codebite\\Quartz\\Exception\\Handler::catcher');

// Load the config file and its data.
$config_data = \Symfony\Component\Yaml\Yaml::load(\Codebite\Quartz\SITE_ROOT . '/data/config/config.yml');
foreach($config_data as $config_name => $config_value)
{
	Core::setConfig($config_name, $config_value);
}

/**
 * Load up the core objects
 */
$router = Core::setObject('router', new \OpenFlame\Framework\URL\Router());
$input = Core::setObject('input', new \OpenFlame\Framework\Input\Handler());
$template = Core::setObject('template', new \OpenFlame\Framework\Template\Variables());
$asset_manager = Core::setObject('asset_manager', new \OpenFlame\Framework\Template\Asset\Manager());
$dispatcher = Core::setObject('dispatcher', new \OpenFlame\Framework\Event\Dispatcher());
$processor = Core::setObject('processor', new \Codebite\Quartz\Page\Processor());
$language_handler = Core::setObject('language', new \OpenFlame\Framework\Language\Handler());
$twig = Core::setObject('twig.frontend', new \OpenFlame\Framework\Template\Twig());

// Set the base URL for HTTP stuff.
$base_url = Core::getConfig('page.base_url') ?: '/';
$router->setBaseURL($base_url);
$asset_manager->setBaseURL($base_url);

/**
 * Define our various core event listeners here
 */

// Start up the cache subsystem.
$dispatcher->register('cache.load', function(\OpenFlame\Framework\Event\Instance $event) {
	$cache_engine = new \OpenFlame\Framework\Cache\Engine\EngineJSON();
	$cache_engine->setCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/');
	$cache = Core::setObject('cache', new \OpenFlame\Framework\Cache\Driver());
	$cache->setEngine($cache_engine);
}, array(), 0);

// Set twig properties
$dispatcher->register('twig.load', function(\OpenFlame\Framework\Event\Instance $event) use($twig) {
	$twig->setTwigRootPath(\OpenFlame\ROOT_PATH . '/vendor/Twig/lib/Twig/')
		->setTwigCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/twig/')
		->setTwigOption('autoescape', false)
		->setTwigOption('debug', true)
		->setTemplatePath(\Codebite\Quartz\SITE_ROOT . '/data/template/')
}, array(), 0);

// Load twig
$dispatcher->register('twig.load', function(\OpenFlame\Framework\Event\Instance $event) use($twig) {
	$twig->initTwig();
}, array(), 10);

// Create the template proxies and load them into twig
$dispatcher->register('twig.load', function(\OpenFlame\Framework\Event\Instance $event) use($asset_manager, $language_handler) {
	$twig_env = Core::getObject('twig.environment');
	$twig_env->addGlobal('asset', new \OpenFlame\Framework\Template\Asset\Proxy($asset_manager));
	$twig_env->addGlobal('language', new \OpenFlame\Framework\Language\Proxy($language_handler));
}, array(), 15);

// Load up Doctrine2 DBAL, and set it up
$dispatcher->register('dbal.load', function(\OpenFlame\Framework\Event\Instance $event) {
	$required_dbal_configs = array('db.name', 'db.user', 'db.password');
	foreach($required_dbal_configs as $c)
	{
		if(Core::getConfig($c) === NULL)
		{
			throw new \RuntimeException('Required database connection configuration setting not set');
		}
	}
	$autoloader = Core::getObject('autoloader');
	$autoloader->setPath(\OpenFlame\ROOT_PATH . '/vendor/Doctrine/DBAL/lib/');
	$dbal_config = Core::setObject('doctrine.dbal.config', new \Doctrine\DBAL\Configuration());
	$dbal_connection = Core::setObject('doctrine.dbal.connection', Doctrine\DBAL\DriverManager::getConnection(array(
		'host'		=> Core::getConfig('db.host') ?: 'localhost',
		'dbname'	=> Core::getConfig('db.name'),
		'user'		=> Core::getConfig('db.user'),
		'password'	=> Core::getConfig('db.password'),
		'driver'	=> 'pdo_mysql',
	)));
}, array(), 0);

// Define our assets...
$dispatcher->register('page.assets.define', function(\OpenFlame\Framework\Event\Instance $event) use($asset_manager) {
	$asset_manager->registerJSAsset('jquery')->setURL('/style/js/jquery.min.js');
	$asset_manager->registerCSSAsset('common')->setURL('/style/css/common.css');
}, array(), 0);

// Enable invalid asset exceptions (lowest priority listener!)
$dispatcher->register('page.assets.define', function(\OpenFlame\Framework\Event\Instance $event) use($asset_manager) {
	$asset_manager->enableInvalidAssetExceptions();
}, array(), 20);

// Load our routes.
$dispatcher->register('page.routes.load', function(\OpenFlame\Framework\Event\Instance $event) use($processor) {
	$processor->loadRoutes();
}, array(), 15);

// Load the language file
$dispatcher->register('page.language.load', function(\OpenFlame\Framework\Event\Instance $event) use($language_handler) {
	$language_entries = \Symfony\Component\Yaml\Yaml::load(\Codebite\Quartz\SITE_ROOT . '/data/language/en.yml');
	$language_handler->loadEntries($language_entries);
}, array(), 15);

$dispatcher->register('page.execute', function(\OpenFlame\Framework\Event\Instance $event) use($processor) {
	$processor->executePage();
}, array(), 0);

$dispatcher->register('page.display', function(\OpenFlame\Framework\Event\Instance $event) use($template) {
	$page = Core::getObject('page.instance');
	$twig_env = Core::getObject('twig.environment');
	$twig_page = $twig_env->loadTemplate($page->getTemplateName());
}, array(), 0);

$dispatcher->register('page.display', function(\OpenFlame\Framework\Event\Instance $event) use($template) {
	try
	{
		ob_start();
		$twig_page->display($template->fetchAllVars());
		ob_end_flush();
	}
	catch(Exception $e)
	{
		ob_clean();
		throw $e;
	}
}, array(), 10);