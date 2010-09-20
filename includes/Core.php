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

if(!defined('QUARTZ')) exit;

/**
 * Quartz - Main class,
 * 		It does stuff.
 *
 *
 * @category    Quartz
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
class Quartz
{
	/**
	 * @var array - The array of configuration entries
	 */
	public static $config = array();

	/**
	 * @var array - The array of loaded objects
	 */
	public static $objects = array();

	/**
	 * Initiator method, loads the config file
	 * @return void
	 */
	public static function init(&$config, &$objects)
	{
		// if the config file does not exist, expect an exception to come from this!
		self::$config = OfJSON::decode(QUARTZ . 'data/config.json');
	}
	
	public static function obj($object_name, $object = NULL)
	{
		if(!is_null($object))
			self::$objects[$object_name] = $object;
		return self::$objects[$object_name];
	}
	
	public function config($config_name, $default = false)
	{
		if(isset(self::$config[$config_name]))
		{
			$config = self::$config[$config_name];
			settype($config, gettype($default));
			return $config;
		}
		return $default;
	}
}
