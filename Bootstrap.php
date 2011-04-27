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
use OpenFlame\Framework\Event\Instance as Event;
use OpenFlame\Framework\Utility\JSON;

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

// Set our exception handler
set_exception_handler('\\Codebite\\Quartz\\Exception\\Handler::catcher');

// Load the config file and its data.
$config_data = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/config.json');
foreach($config_data as $config_name => $config_value)
{
	Core::setConfig($config_name, $config_value);
}

/**
 * Load up the core objects
 */
$timer = Core::setObject('timer', new \OpenFlame\Framework\Utility\Timer());
$router = Core::setObject('router', new \OpenFlame\Framework\Router\Router());
$input = Core::setObject('input', new \OpenFlame\Framework\Input\Handler());
$template = Core::setObject('template', new \OpenFlame\Framework\Twig\Variables());
$asset_manager = Core::setObject('asset_manager', new \OpenFlame\Framework\Asset\Manager());
$dispatcher = Core::setObject('dispatcher', new \OpenFlame\Framework\Event\Dispatcher());
$processor = Core::setObject('processor', new \Codebite\Quartz\Page\Processor());
$language_handler = Core::setObject('language', new \OpenFlame\Framework\Language\Handler());
$header_manager = Core::setObject('header', new \OpenFlame\Framework\Header\Manager());
$twig = Core::setObject('twig.frontend', new \OpenFlame\Framework\Twig\Wrapper());

// Set the base URL for HTTP stuff.
$base_url = Core::getConfig('page.base_url') ?: '/';
$router->setBaseURL($base_url);
$asset_manager->setBaseURL($base_url);

/**
 * Define our various core event listeners here
 */

// Start up the cache subsystem.
$dispatcher->register('cache.load', 0, function(Event $event) {
	$cache_engine = new \OpenFlame\Framework\Cache\Engine\EngineJSON();
	$cache_engine->setCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/');
	$cache = Core::setObject('cache', new \OpenFlame\Framework\Cache\Driver());
	$cache->setEngine($cache_engine);
});

// Set twig properties
$dispatcher->register('twig.load', 0, function(Event $event) use($twig) {
	$twig->setTwigRootPath(\OpenFlame\ROOT_PATH . '/vendor/Twig/lib/Twig/')
		->setTwigCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/twig/')
		->setTwigOption('autoescape', false)
		->setTwigOption('debug', true)
		->setTemplatePath(\Codebite\Quartz\SITE_ROOT . '/data/template/');
});

// Load twig
$dispatcher->register('twig.load', 10, function(Event $event) use($twig) {
	$twig->initTwig();
});

// Snag control of the headers
$dispatcher->register('page.headers.snag', 0, function(Event $event) use($header_manager) {
	$header_manager->snagHeaders();
});

// Define our assets...
$dispatcher->register('page.assets.define', 0, function(Event $event) use($asset_manager) {
	$asset_manager->registerJSAsset('jquery')->setURL('/style/js/jquery.min.js');
	$asset_manager->registerCSSAsset('common')->setURL('/style/css/common.css');
});

// Create the template proxies and load them into twig
$dispatcher->register('page.assets.define', 19, function(Event $event) use($twig, $timer, $asset_manager, $language_handler) {
	$twig_env = $twig->getTwigEnvironment();
	$twig_env->addGlobal('timer', $timer);
	$twig_env->addGlobal('asset', new \OpenFlame\Framework\Asset\Proxy($asset_manager));
	$twig_env->addGlobal('language', new \OpenFlame\Framework\Language\Proxy($language_handler));
});

// Enable invalid asset exceptions (lowest priority listener!)
$dispatcher->register('page.assets.define', 20, function(Event $event) use($asset_manager) {
	$asset_manager->enableInvalidAssetExceptions();
});

// Load our routes.
$dispatcher->register('page.routes.load', 15, function(Event $event) use($processor) {
	$processor->loadRoutes();
});

// Load the language file
$dispatcher->register('page.language.load', 15, function(Event $event) use($language_handler) {
	$language_entries = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/language/en.json');
	$language_handler->loadEntries($language_entries);
});

// Send headers
$dispatcher->register('page.headers.send', 10, function(Event $event) use($header_manager) {
	$header_manager->sendHeaders();
});

// Execute the page logic
$dispatcher->register('page.execute', 10, function(Event $event) use($dispatcher, $processor) {
	$page = $processor->run();
	$page->executePage();

	$dispatcher->triggerUntilBreak(Event::newEvent('page.display')->setDataPoint('page', $page));
});

// Display the page
$dispatcher->register('page.display', 10, function(Event $event) use($dispatcher, $twig, $template) {
	$page = $event->getDataPoint('page');
	$twig_env = $twig->getTwigEnvironment();
	$twig_page = $twig_env->loadTemplate($page->getTemplateName());
	try
	{
		$dispatcher->triggerUntilBreak(Event::newEvent('page.headers.send'));
		ob_start();
		$html = $twig_page->render($template->fetchAllVars());
		echo $html;
		ob_end_flush();
	}
	catch(Exception $e)
	{
		ob_clean();
		throw $e;
	}
});
