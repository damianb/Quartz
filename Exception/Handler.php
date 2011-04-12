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

namespace Codebite\Quartz\Exception;

/**
 * Quartz - Exception handler extension,
 * 		Overrides the HTML for the OpenFlame Framework exception handler.
 *
 * @category    Quartz
 * @package     Quartz
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/Quartz
 */
class Handler extends \OpenFlame\Framework\Exception\Handler
{
	public static $page_format = '
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
}
