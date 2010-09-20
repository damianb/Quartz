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

require QUARTZ . 'includes/Core.php';

// load the openflame framework
define('OF_ROOT', QUARTZ . 'vendor/OpenFlame-Framework/src/');
require OF_ROOT . 'Of.php';
require OF_ROOT . 'OfException.php';
spl_autoload_register('Of::loader');

// setup our own exception handler
require QUARTZ . 'includes/Exception.php';

// init the base object
Quartz::init();

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

$cli = new Doctrine_Cli(array(
    'models_path' => QUARTZ . 'data/db/models',
    'migrations_path' => QUARTZ . 'data/db/migrations',
    'yaml_schema_path' => QUARTZ . 'data/db/schema',
	'data_fixtures_path' => QUARTZ . 'data/db/fixtures',
    'generate_models_options' => array(
        'pearStyle' => true,
        'generateTableClasses' => true,
        'baseClassPrefix' => 'Base',
        'baseClassesDirectory' => null,
    ),
));

$cli->run($_SERVER['argv']);
