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

define('QUARTZ', dirname(__FILE__) . '/');
define('IN_QUARTZ_GC', true);
// define('QUARTZ_DEBUG', true);

if(defined('QUARTZ_DEBUG'))
{
	@error_reporting(-1);
	@ini_set('display_errors', 1);
}

require QUARTZ . 'includes/Core.php';

// load the openflame framework
define('OF_ROOT', QUARTZ . 'vendor/OpenFlame-Framework/src/');
require OF_ROOT . 'Of.php';
require OF_ROOT . 'OfException.php';
spl_autoload_register('Of::loader');

// setup our own exception handler
require QUARTZ . 'includes/Exception.php';
require QUARTZ . 'includes/CLIHandler.php';
set_exception_handler('QuartzCLIHandler::catcher');

// init the base object
Quartz::init();

// if the site is disabled, disable it!
if(Quartz::config('site.disable_site', false))
	exit;

// setup some of the various framework parts
$cache = new OfCache($config('framework.cache_engine', 'JSON'), Quartz . 'data/cache/');

// Let's store references for later use, as well.
Quartz::obj('cache', $cache);

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

// perform GC tasks
