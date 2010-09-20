<?php
/**
 *
 *===================================================================
 *
 *  Quartz - Site base
 *-------------------------------------------------------------------
 * @category    Quartz
 * @package     pages
 * @author      Damian Bushong ("Obsidian")
 * @copyright   (c) 2010 - Codebite.net
 * @license     MIT License
 *
 *===================================================================
 *
 */

if(!defined('QUARTZ')) exit;

/**
 * Quartz - Page class,
 * 		One of the pages.
 *
 *
 * @category    Quartz
 * @package     pages
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
class Page_about extends QuartzPageBase
{
	protected $tpl_name = 'about.html';

	public function executePage()
	{
		/* @var OfUrlHandler */
		$url = Quartz::obj('url');
		/* @var OfTwig */
		$template = Quartz::obj('template');

		// Make sure we're not accessing an "invalid" page
		if($url->checkExtra())
			$this->throw404();

		$template->assignVar('sub_title', 'About');
	}
}
