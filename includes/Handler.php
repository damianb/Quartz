<?php
/**
 *
 *===================================================================
 *
 *  FreeQ - Quote Database
 *-------------------------------------------------------------------
 * @category    FreeQ
 * @package     core
 * @author      Damian Bushong ("Obsidian")
 * @copyright   (c) 2010 - Codebite.net
 * @license     All rights reserved
 *
 *===================================================================
 *
 * This code may not be wholly redistributed for any purpose.
 *
 */

if(!defined('FREEQ')) exit;

class FreeQHandler
{
	/**
	 * @var Exception - The exception to store
	 */
	public static $exception;

	/**
	 * @var string - The contents of the page to display
	 */
	public static $page = '';

	/**
	 * @var boolean - Do we want to show debug info to _everyone_?
	 */
	public static $show_throw_info = false;

	/**
	 * Catches an exception and prepares to deal with it
	 * @param Exception $e - The exception to handle
	 * @return void
	 */
	public static function catcher(Exception $e)
	{
		self::$exception = $e;
		if(defined('FREEQ_DEBUG') || self::$show_throw_info)
		{
			self::displayException();
		}
		else
		{
			self::badassError();
		}

		exit;
	}

	/**
	 * Displays a debug page showing full info on the exception thrown
	 * @return void
	 */
	public static function displayException()
	{
		$e = array(
			'e_type' => get_class(self::$exception),
			'message' => self::$exception->getMessage(),
			'code' => self::$exception->getCode(),
			'trace' => self::highlightTrace(implode(self::traceException(self::$exception->getFile(), self::$exception->getLine(), 7))),
			'file' => self::$exception->getFile(),
			'line' => self::$exception->getLine(),
			'stack' => self::formatStackTrace(),
		);

		if(!$e['stack'])
			$e['stack'] = 'No stack trace available.';

		$message = <<<EOD
						<div style="font-size: 0.9em; padding: 20px 30px;">
							<h3 style="padding: 0 0 20px 0; font-size: 1.5em;">Exception information</h3>

							<div style="padding: 0 20px;">
								Exception thrown, error code <span style="font-weight: bold; font-family: monospace; background: #ffffff; color: #007700; padding: 1px 3px; border: solid 1px #007700; font-size: 1.1em;">{$e['e_type']}::{$e['code']}</span> with message “<span style="font-family: monospace; font-weight: bold; font-size: 1.1em;">{$e['message']}</span>”<br /><br />
								on line <span style="font-weight: bold;">{$e['line']}</span> in file: <span style="font-weight: bold; font-family: monospace; background: #ffffff; color: #007700; padding: 1px 3px; border: solid 1px #007700; font-size: 1.1em;">{$e['file']}</span>
							</div>

							<h3 style="padding: 20px 0; font-size: 1.5em;">Trace context</h3>
							<div style="padding: 0 20px;">
								<div style="font-family: monospace; background: #ffffff; color: #007700; padding: 8px; border: solid 1px #007700; font-size: 1.2em; overflow:auto;">
									{$e['trace']}
								</div>
							</div>

							<h3 style="padding: 20px 0; font-size: 1.5em;">Stack trace</h3>
							<div style="padding: 0 20px;">
								{$e['stack']}
							</div>
						</div>
EOD;
		self::buildHTML('FreeQ &nbsp;-&nbsp; Unexpected Exception', $message);
		echo self::$page;
	}

	/**
	 * Display a user-friendly (and obscure) error message.
	 * @return void
	 */
	public static function badassError()
	{
		$e = array(
			'e_type' => get_class(self::$exception),
			'code' => self::$exception->getCode(),
		);

		$message = <<<EOD
						<div style="padding: 0 25px;">
							Looks like something blew up on our end.  If you would be so kind as to report the error below to a site administrator or site technician, we'll get right on fixing it.<br /><br />
							Error code: <span style="font-weight: bold; font-family: monospace; background: #ffffff; color: #007700; padding: 0 3px; border: solid 1px #007700;">{$e['e_type']}::{$e['code']}</span>
						</div>
EOD;
		self::buildHTML('Unexpected Exception', $message);

		echo self::$page;
	}

	/**
	 * Manually throw an error (useful for server errors and such)
	 * @param string $title - The title to use for the page.
	 * @param string $message - The message to display on the page.
	 * @return void
	 */
	public static function asplode($title, $message)
	{
		self::buildHTML($title, '<p style="padding: 0 25px">' . $message . '</p>');
		echo self::$page;
	}

	/**
	 * Builds the rough HTML page for the exception handler.
	 * @param string $title - The title to use for the page.
	 * @param string $page - The page content to display within the HTML layout.
	 */
	public static function buildHTML($title, $page)
	{
		self::$page = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>$title</title>
		<style type="text/css">
			/* <![CDATA[ */
			* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #FFFFFF; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #825353; background: #FFFFFF; font-size: 62.5%; margin: 0; } a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } #wrap { padding: 0 20px 15px 20px; min-width: 700px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } .panel { margin: 4px 0; background-color: #FEEFDA; border: solid 1px #F7941D; /*height: 330px;*/ } #errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 14px; } #errorpage h1 { line-height: 1.2em; margin: 0 45px 15px; color: #000000; } #errorpage #content div { margin-top: 10px; margin-bottom: 5px; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%;} #errorpage #content #backtrace { border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; }
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
						<h2>{$title}</h2>

						<div>{$page}</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
EOD;
	}

	/**
	 * Retrieves the context code from where an exception was thrown (as long as file/line are provided) and outputs it.
	 * @param string $file - The file where the exception occurred.
	 * @param string $line - The line where the exception occurred.
	 * @param integer $context - How many lines of context (above AND below) the troublemaker should we grab?
	 * @return string - String containing the perpetrator + context lines for where the error/exception was thrown.
	 */
	public static function traceException($file, $line, $context = 3)
	{
		$return = array();
		foreach (file($file) as $i => $str)
		{
			if (($i + 1) > ($line - $context))
			{
				if(($i + 1) > ($line + $context))
					break;
				$return[] = $str;
			}
		}

		return $return;
	}

	/**
	 * Highlights the provided trace context code
	 * @param string $code - The code to highlight.
	 * @return string - The HTML highlighted trace context code.
	 */
	public static function highlightTrace($code)
	{
		$remove_tags = false;
		if (!preg_match('/\<\?.*?\?\>/is', $code))
		{
			$remove_tags = true;
			$code = "<?php $code";
		}

		$conf = array('highlight.bg', 'highlight.comment', 'highlight.default', 'highlight.html', 'highlight.keyword', 'highlight.string');
		foreach ($conf as $ini_var)
		{
			@ini_set($ini_var, str_replace('highlight.', 'syntax', $ini_var));
		}

		$code = highlight_string($code, true);

		$str_from = array('<span style="color: ', '<font color="syntax', '</font>', '<code>', '</code>','[', ']', '.', ':');
		$str_to = array('<span class="', '<span class="syntax', '</span>', '', '', '&#91;', '&#93;', '&#46;', '&#58;');

		if ($remove_tags)
		{
			$str_from[] = '<span class="syntaxdefault">&lt;?php </span>';
			$str_to[] = '';
			$str_from[] = '<span class="syntaxdefault">&lt;?php&nbsp;';
			$str_to[] = '<span class="syntaxdefault">';
		}

		$code = str_replace($str_from, $str_to, $code);
		$code = preg_replace('#^(<span class="[a-z_]+">)\n?(.*?)\n?(</span>)$#is', '$1$2$3', $code);

		$code = preg_replace('#^<span class="[a-z]+"><span class="([a-z]+)">(.*)</span></span>#s', '<span class="$1">$2</span>', $code);
		$code = preg_replace('#(?:\s++|&nbsp;)*+</span>$#u', '</span>', $code);

		// remove newline at the end
		if (!empty($code) && substr($code, -1) == "\n")
		{
			$code = substr($code, 0, -1);
		}

		return $code;
	}

	/**
	 * Format the stack trace for the currently loaded exception
	 * @return string - The string containing the formatted HTML stack trace
	 */
	public static function formatStackTrace()
	{
		$return = array();
		$stack = self::$exception->getTrace();

		if(!$stack)
			return array();

		$return[] = '<ol style="list-style-type: none;">' . "\n";
		foreach($stack as $id => $trace)
		{
			$arg_count = sizeof($trace['args']);
			if($arg_count)
			{
				$i = 1;
				reset($trace['args']);

				$arg = current($trace['args']);
				if(is_string($arg))
				{
					if(strlen($arg) > 30)
						$arg = '{oversize string}';
					$arg = '\'' . $arg . '\'';
				}
				elseif(is_array($arg))
				{
					$arg = 'Array';
				}
				elseif(is_object($arg))
				{
					$arg = 'Object ' . get_class($arg);
				}

				$args = '<span style="color: #0000BB;">' . $arg . '</span>';
				if($arg_count > 1)
				{
					while($i++ < $arg_count);
					{
						$arg = next($trace['args']);
						if(is_string($arg))
						{
							if(strlen($arg) > 30 || strpos($arg, "\n"))
								$arg = '{oversize string}';
							$arg = '\'' . $arg . '\'';
						}
						elseif(is_array($arg))
						{
							$arg = 'Array';
						}
						elseif(is_object($arg))
						{
							$arg = 'Object ' . get_class($arg);
						}
						$args .= '<span style="color: #007700; font-weight: bold;">,</span> <span style="color: #0000BB;">' . $arg . '</span>';
					}
				}
			}
			else
			{
				$args = '';
			}

			$callback = (isset($trace['class']) ? $trace['class'] . '<span style="color: #007700; font-weight: bold;">' . $trace['type'] . '</span>' : '') . '<span style="color: #0000BB; font-weight: bold;">' . $trace['function'] . '</span><span style="color: #007700; font-weight: bold;">(</span>' . $args . '<span style="color: #007700; font-weight: bold;">)</span>';
			$return[] = <<<EOD
				<li style="padding-left: 0px;">
					<span style="font-weight: bold;">#{$id}</span><br />
					<span style="padding-left: 20px;">callback: {$callback}</span><br /><br />
					<span style="padding-left: 20px;">on line <span style="font-weight: bold;">{$trace['line']}</span> of file: <span style="font-weight: bold; font-family: monospace; background: #ffffff; color: #007700; padding: 1px 3px; border: solid 1px #007700; font-size: 1.1em;">{$trace['file']}</span></span>

				</li>
EOD;
		}
		$return[] = '</ol>';
		return join($return);
	}
}
