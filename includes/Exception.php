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

if(!defined('FREEQ')) exit;

/**
 * Quartz - Primary Exception class,
 * 		Extension of the default Exception class.
 *
 *
 * @category    Quartz
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
class SiteException extends Exception
{
	const ERR_WTF = 0;

	const ERR_CORE_PAGEBASE_NOT_FOUND = 1000;
	const ERR_PAGE_NOT_PAGEBASE_CHILD = 1100;
	const ERR_PAGE_NOT_PAGEINTERFACE_CHILD = 1101;
}

/**
 * Quartz - AJAX Exception class,
 * 		Extension of the SiteException class.
 *
 *
 * @category    Quartz
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
class AJAXException extends Exception { }

/**
 * Quartz - Server Error Exception class,
 * 		Thrown when we want to say that something server-side went boom.
 *
 *
 * @category    Quartz
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
class ServerErrorException extends Exception { }

/**
 * Quartz - Redirect Exception class,
 * 		Used to trigger an HTTP redirect of a user to another page.
 *
 *
 * @category    Quartz
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
class RedirectException extends Exception { }
