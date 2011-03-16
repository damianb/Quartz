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
 * @todo add in \Codebite\Quartz\Database\Sqlite
 *
 */

namespace Codebite\Quartz;
use \OpenFlame\Framework\Core;

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

// Init the URL router
$router = Core::setObject('router', new \OpenFlame\Framework\URL\Router());
// Init the input handler
$input = Core::setObject('input', new \OpenFlame\Framework\Input\Handler());

// Start up the cache subsystem.
$cache_engine = new \OpenFlame\Framework\Cache\Engine\EngineJSON();
$cache_engine->setCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/');
$cache = Core::setObject('cache', new \OpenFlame\Framework\Cache\Driver($cache_engine));

// Handle twig variables and page assets.
$template = Core::setObject('template', new \OpenFlame\Framework\Template\Variables());
$asset_manager = new \OpenFlame\Framework\Template\Asset\Manager();

// Load Twig.
$twig = Core::setObject('twig.frontend', new \OpenFlame\Framework\Template\Twig());
$twig->setTwigRootPath(\OpenFlame\ROOT_PATH . '/Twig/lib/Twig/')
	->setTwigCachePath(\Codebite\Quartz\SITE_ROOT . '/cache/twig/')
	->setTwigOption('autoescape', false)
	->setTwigOption('debug', true)
	->setTemplatePath(\Codebite\Quartz\SITE_ROOT . '/data/template/');
$twig->initTwig();

// Load the config file and its data.
$config_data = \Symfony\Component\Yaml\Yaml::load(\Codebite\Quartz\SITE_ROOT . '/data/config/config.yml');
foreach($config_data as $config_name => $config_value)
{
	Core::setConfig($config_name, $config_value);
}

// Load the language file if we want to.
$language_handler = Core::setObject('language', new \OpenFlame\Framework\Language\Handler());
/* @todo rework this for more flexibility
 $language_entries = \Symfony\Component\Yaml\Yaml::load(\Codebite\Quartz\SITE_ROOT . '/data/language/en.yml');
 $language_handler->loadEntries($language_entries);
*/

// Set the base URL for HTTP stuff.
$base_url = Core::getConfig('page.base_url') ?: '/';
$router->setBaseURL($base_url);
$asset_manager->setBaseURL($base_url);

// Define a few assets...
$jquery = $asset_manager->registerJSAsset('jquery');
$jquery->setURL('/style/js/jquery.min.js');
$css = $asset_manager->registerCSSAsset('common');
$css->setURL('/style/css/common.css');

// Load our defined routes and the page processor.
$page = Core::setObject('processor', new \Codebite\Quartz\Page\Processor());
$page->loadRoutes();

// Create the template proxies and load them into twig
$twig_env = Core::getObject('twig.environment');
$twig_env->addGlobal('asset', new \OpenFlame\Framework\Template\Asset\Proxy($asset_manager));
$twig_env->addGlobal('language', new \OpenFlame\Framework\Language\Proxy($language_handler));

/* index.php should contain something like this:

// Execute the page...
$page = Core::getObject('processor');
$template = Core::getObject('template');

$page->executePage();
$page_instance = Core::getObject('page.instance');

$twig_env = Core::getObject('twig.environment');
$twig_env->loadTemplate($page_instance->getTemplateName());
$twig_env->display($template->fetchAllVars());

*/
