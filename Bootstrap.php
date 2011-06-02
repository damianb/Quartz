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

$exception_page = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>%1$s</title>
		<style type="text/css">
			/* <![CDATA[ */
			* { margin: 0; padding: 0; } html { font-size: 100%%; height: 100%%; margin-bottom: 1px; background-color: #FFFFFF; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #825353; background: #FFFFFF; font-size: 62.5%%; margin: 0; } a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } #wrap { padding: 0 20px 15px 20px; min-width: 700px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } .panel { margin: 4px 0; background-color: #FEEFDA; border: solid 1px #F7941D; /*height: 330px;*/ } #errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 14px; } #errorpage h1 { line-height: 1.2em; margin: 0 45px 15px; color: #000000; } #errorpage #content div { margin-top: 10px; margin-bottom: 5px; padding-bottom: 5px; color: #333333; font: bold 1.15em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%%;} #errorpage #content #backtrace { border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; }
			* .round { -moz-border-radius-bottomleft: 4px; -moz-border-radius-bottomright: 10px; -moz-border-radius-topleft: 10px; -moz-border-radius-topright: 4px; -webkit-border-bottom-left-radius: 4px; -webkit-border-bottom-right-radius: 10px; -webkit-border-top-left-radius: 10px; -webkit-border-top-right-radius: 4px; border-radius-bottomleft: 4px; border-radius-bottomright: 10px; border-radius-topleft: 10px; border-radius-topright: 4px; }
			* .syntaxbg { color: #FFFFFF; } .syntaxcomment { color: #FF8000; } .syntaxdefault { color: #0000BB; } .syntaxhtml { color: #000000; } .syntaxkeyword { color: #007700; } .syntaxstring { color: #DD0000; }
			* .logo { display: block; margin-left: auto; margin-right: auto; }
			/* ]]> */
		</style>
	</head>
	<body id="errorpage">
		<div id="wrap">
			<div id="page-header"></div>
			<div id="acp">
				<div class="panel round">
					<div id="content">
						<h2>%1$s</h2>

						<div>%2$s</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>';
\OpenFlame\Framework\Exception\Handler::setPageFormat($exception_page);

// Load the config file and its data.
if(file_exists(\Codebite\Quartz\SITE_ROOT . '/data/config/config.json'))
{
	$config_data = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/config.json');
	foreach($config_data as $config_name => $config_value)
	{
		Core::setConfig($config_name, $config_value);
	}
}

// Get the base URL for HTTP stuff.
$base_url = Core::getConfig('page.base_url') ?: '/';

/**
 * Load up some core objects, and setup the injectors for what we don't absolutely need.
 * recommended commit: fae7e2f315c8699cf1367035682c3068899f8c92
 */
$timer = Core::setObject('timer', new \OpenFlame\Framework\Utility\Timer());
$injector = \OpenFlame\Framework\Dependency\Injector::getInjector();

$injector->setInjector('router', function() use($base_url) {
    $router = new \OpenFlame\Framework\Router\Router();
    $router->setBaseURL($base_url);
    return $router;
});

$injector->setInjector('input', function() {
    return new \OpenFlame\Framework\Input\Handler();
});

$injector->setInjector('template', function() {
    return new \OpenFlame\Framework\Twig\Variables();
});

$injector->setInjector('asset_manager', function() use($base_url) {
    $asset_manager = new \OpenFlame\Framework\Asset\Manager();
    $asset_manager->setBaseURL($base_url);
    return $asset_manager;
});

$injector->setInjector('dispatcher', function() {
    return new \OpenFlame\Framework\Event\Dispatcher();
});

$injector->setInjector('processor', function() {
    return new \Codebite\Quartz\Page\Processor();
});

$injector->setInjector('language', function() {
    return new \OpenFlame\Framework\Language\Handler();
});

$injector->setInjector('header', function() {
    return new \OpenFlame\Framework\Header\Manager();
});

$injector->setInjector('url_builder', function() {
    return new \OpenFlame\Framework\URL\Builder();
});

$injector->setInjector('hasher', function() {
	return new \OpenFlame\Framework\Security\Hasher();
});
$injector->setInjector('seeder', function() {
	return new \OpenFlame\Framework\Security\Seeder();
});

$injector->setInjector('twig', function() {
    $twig = new \OpenFlame\Framework\Twig\Wrapper();
    $twig->setTwigRootPath(\OpenFlame\ROOT_PATH . '/vendor/Twig/lib/Twig/')
    	->setTwigCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/twig/')
		->setTemplatePath(\Codebite\Quartz\SITE_ROOT . '/data/template/')
    	->setTwigOption('debug', true);
    $twig->initTwig();

    return $twig;
});

$injector->setInjector('cache', function() {
    $cache_engine = new \OpenFlame\Framework\Cache\Engine\File\FileEngineJSON();
    $cache_engine->setCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/');
	$cache = new \OpenFlame\Framework\Cache\Driver();
	$cache->setEngine($cache_engine);
    return $cache;
});

/**
 * Define our various core event listeners here
 */

$dispatcher = $injector->get('dispatcher');


// Create the template proxies and load them into twig
$dispatcher->register('page.assets.define', 19, function(Event $event) use($injector) {
    $twig = $injector->get('twig');
    $timer = $injector->get('timer');
    $asset_manager = $injector->get('asset_manager');
    $language = $injector->get('language');
    $twig_env = $twig->getTwigEnvironment();
	$twig_env->addGlobal('timer', $timer);
	$twig_env->addGlobal('asset', new \OpenFlame\Framework\Asset\Proxy($asset_manager));
	$twig_env->addGlobal('language', new \OpenFlame\Framework\Language\Proxy($language_handler));
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
