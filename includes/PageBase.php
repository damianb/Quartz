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
 * Quartz - Base page class,
 * 		Prototype for all pages to support.
 *
 *
 * @category    Quartz
 * @package     pages
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
abstract class QuartzPageBase implements QuartzPageInterface
{
	/**
	 * @var string - The name of the template file to use for this page.
	 */
	protected $tpl_name = '';

	/**
	 * @var string - Mode for the page
	 */
	protected $mode = '';

	/**
	 * Obtain the name of the template file for this page.
	 * @return string - This page's template file.
	 */
	public function getTPL()
	{
		return $this->tpl_name;
	}

	/**
	 * Cheap way to throw a 404 error
	 * @return void
	 * @throws ServerErrorException
	 */
	public function throw404()
	{
		throw new ServerErrorException('The page you are trying to access does not exist.', 404);
	}

	/**
	 * Build pagination for whatever
	 * @param array $url_ary - URL structure to pass to the URL builder
	 * @param array $request_ary - GET params to pass to the URL builder
	 * @param integer $start - The current page for the pagination
	 * @param integer $total_pages - The total number of pages available
	 * @return void
	 */
	public function buildPagination(array $url_ary = array(), array $request_ary = array(), $start, $total_pages)
	{
		/* @var OfUrlHandler */
		$url = Quartz::obj('url');
		/* @var OfTwig */
		$template = Quartz::obj('template');

		$template->assignVars(array(
			'first_page'		=> ($start && $start != 1) ? $url->build($url_ary, array_merge($request_ary, array('page' => 1))) : false,
			'previous_page'		=> ($start > 1) ? $url->build($url_ary, array_merge($request_ary, array('page' => $start - 1))) : false,

			'current_page'		=> $start,
			'total_pages'		=> $total_pages,

			'next_page'			=> ($start != $total_pages) ? $url->build($url_ary, array_merge($request_ary, array('page' => $start + 1))) : false,
			'last_page'			=> ($start != $total_pages) ? $url->build($url_ary, array_merge($request_ary, array('page' => $total_pages))) : false,
		));
	}
}

/**
 * Quartz - Page interface.
 * 		Prototype for all pages to implement.
 *
 *
 * @category    Quartz
 * @package     pages
 * @author      Damian Bushong ("Obsidian")
 * @license     MIT License
 */
interface QuartzPageInterface
{
	public function getTPL();
	public function executePage();
	public function throw404();
	public function buildPagination(array $url_ary = array(), array $request_ary = array(), $start, $total_pages);
}
