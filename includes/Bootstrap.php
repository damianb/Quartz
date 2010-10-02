<?php
/**
 *
 *===================================================================
 *
 *  Quartz - Site base
 *-------------------------------------------------------------------
 * @category    Quartz
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @copyright   (c) 2010 - Codebite.net
 * @license     MIT License
 *
 *===================================================================
 *
 */

if(defined('QUARTZ_DEBUG'))
{
	@error_reporting(-1);
	@ini_set('display_errors', 1);
}

// Set the time to UTC.
@date_default_timezone_set('UTC');

require QUARTZ . 'includes/Core.php';

// load the openflame framework
define('OF_ROOT', QUARTZ . 'vendor/OpenFlame-Framework/src/');
require OF_ROOT . 'Of.php';
require OF_ROOT . 'OfException.php';
spl_autoload_register('Of::loader');

// setup our own exception handler
require QUARTZ . 'includes/Exception.php';
require QUARTZ . 'includes/Handler.php';
set_exception_handler('QuartzHandler::catcher');

// init the base object
Quartz::init();

// if the site is disabled, disable it!
if($config('site.disable_site', false))
{
	header('HTTP/1.0 503 Service Unavailable');
	QuartzHandler::asplode('The site is currently unavailable', Quartz::config('site.disable_message', 'The site is currently disabled.'));
	exit;
}

// setup some of the various framework parts
$cache = new OfCache(Quartz::config('framework.cache_engine', 'JSON'), QUARTZ . 'data/cache/');
$template = new OfTwig();
$url = new OfUrlHandler(Quartz::config('framework.url_base', '/'));

// Let's store references for later use, as well.
Quartz::obj('cache', $cache);
Quartz::obj('template', $template);
Quartz::obj('url', $url);

// load and setup doctrine
require QUARTZ . 'vendor/doctrine/lib/Doctrine/Core.php';
spl_autoload_register(array('Doctrine_Core', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
// we want this to be under the data/db/ dir
Doctrine_Core::loadModels(QUARTZ . 'data/db/models');

// doctrine connection stuffs
$conn = Doctrine_Manager::connection(Quartz::config('doctrine.dsn', 'sqlite:data/db/develop.db'));
$conn->setCharset('utf8');
$conn->setCollate('utf8_bin');

// Some more references, for later use
Quartz::obj('doctrine_manager', $manager);
Quartz::obj('doctrine_conn', $conn);

// twig setup
require QUARTZ . 'vendor/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

// We want to be able to fall back on the default twig template dir
$twig_dirs = array();
if(Quartz::config('twig.template_dir', ''))
	$twig_dirs[] = QUARTZ . 'data/template/' . Quartz::config('twig.template_dir', '');

$twig_loader = new Twig_Loader_Filesystem(array_merge($twig_dirs, array(QUARTZ . 'data/template/quartz')));
$twig = new Twig_Environment($twig_loader, array(
	'cache' => QUARTZ . 'data/cache/twig',
	'debug' => Quartz::config('twig.debug', false),
	'trim_blocks' => Quartz::config('twig.trim_blocks', false),
));
