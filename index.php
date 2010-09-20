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
// define('QUARTZ_DEBUG', true);
require QUARTZ . 'includes/Bootstrap.php';

$template->assignVars(Quartz::config('template.variables', array()));
if(!$template->issetVar('use_jquery'))
	$template->assignVar('use_jquery', false);

// setup the template root paths here, for later
$style_dir = Quartz::config('framework.url_base', '/') . 'style/' . Quartz::config('twig.template_dir', 'quartz');
$template->assignVars(array(
	'url_base'				=> Quartz::config('framework.url_base', '/'),
	'global_style_path'		=> Quartz::config('framework.url_base', '/') . 'style/global/',
	'base_style_path'		=> $style_dir,
	'js_path'				=> $style_dir . '/js/',
	'css_path'				=> $style_dir . '/css/',
	'images_path'			=> $style_dir . '/images/',
));

/* edit here to assign template variables for session integration */

/*
$navigation = $template->fetchVar('navigation_left');
$template->assignVar('navigation_left', array_merge((($navigation) ? $navigation : array()), array(
	array('url' => $url->build(array('login')), 'text' => 'Login'),
	array('url' => $url->build(array('register')), 'text' => 'Register'),
)));
*/

try
{
	// grab our page
	$mode = $url->get('home');

	// ensure that nobody is peeping at the secwet stuffz
	if(in_array($mode, array_merge(array('base'), Quartz::config('site.blocked_pages', array('403')))))
		throw new ServerErrorException('Access to this page is forbidden.  Keep trying to sneak a peek and you will earn yourself a hadouken to the face.', 403);

	// grab the base page class
	require QUARTZ . 'includes/PageBase.php';
	if(!class_exists('QuartzPageBase', false))
		throw new SiteException('Page base class not found', SiteException::ERR_CORE_PAGEBASE_NOT_FOUND);
	if(!file_exists(QUARTZ . 'data/pages/' . $mode . '.php'))
		throw new ServerErrorException('The page you are trying to access does not exist.', 404);

	require QUARTZ . 'data/pages/' . $mode . '.php';
	$page_class = "Page_$mode";
	if(!class_exists($page_class, false))
		throw new ServerErrorException('The page you are trying to access does not exist.', 404);

	/* @var QuartzPageInterface */
	$page = new $page_class();

	// load the page here
	if(!($page instanceof QuartzPageBase))
		throw new SiteException('Page does not extend page base class', SiteException::ERR_PAGE_NOT_PAGEBASE_CHILD);
	if(!($page instanceof QuartzPageInterface))
		throw new SiteException('Page does not implement page interface', SiteException::ERR_PAGE_NOT_PAGEINTERFACE_CHILD);

	$page->executePage();
	$twig_page = $twig->loadTemplate($page->getTPL());
	$twig_page->display($template->fetchAllVars());
}
catch(RedirectException $e)
{
	// *punt* WHEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE~
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: " . $e->getMessage());
	exit;
}
catch(ServerErrorException $e)
{
	// the compendium of all the types of server errors.  Oh the joy!
	$server_errors = array(
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		204 => 'No Content',
		205 => 'Reset Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // Moved Temporarily
		303 => 'See Other',
		304 => 'Not Modified',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		406 => 'Not Acceptable',
		409 => 'Conflict',
		410 => 'Gone',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
	);

	// dem errors
	$error = isset($server_errors[(int) $e->getCode()]) ? (int) $e->getCode() : 500;

	header("HTTP/1.0 {$error} {$server_errors[$error]}");
	QuartzHandler::asplode("{$error} {$server_errors[$error]}", $e->getMessage(), $e);
	exit;
}
catch(AJAXException $e)
{
	// if we are in debug mode, we want full details!
	if(defined('QUARTZ_DEBUG'))
	{
		$json_output = array('error' => array(
			'type' => get_class($e),
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		));
	}
	else
	{
		$json_output = array('error' => array(
			'type' => get_class($e),
			'code' => $e->getCode(),
		));
	}

	echo OfJSON::encode($json_output);
}
catch(Exception $e)
{
	// if it's an exception here, we might as well just throw the OHSHI-- page now.
	QuartzHandler::catcher($e);
	exit;
}
