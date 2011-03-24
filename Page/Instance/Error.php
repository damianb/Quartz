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

namespace Codebite\Quartz\Page\Instance;
use \OpenFlame\Framework\Core;

/**
 * Quartz - Error page object,
 * 		The error page.
 *
 *
 * @category    Quartz
 * @package     Quartz
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/Quartz
 */
class Error extends \Codebite\Quartz\Page\Instance\Base
{
	protected $template_name = 'error.twig.html';

	public function executePage()
	{
		$template = Core::getObject('template');
		$input = Core::getObject('input');

		$error_default = (!empty($this->route) && $this->route->getRequestDataPoint('code')) ? $this->route->getRequestDataPoint('code') : 500;
		$error = $input->getInput('REQUEST::e')
			->setDefault($error_default)
			->disableFieldJuggling()
			->getClean();

		// the compendium of all the recognized types of server errors.  Oh the joy!
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
		$error = isset($server_errors[(int) $error]) ? (int) $error : 404;
		$error_message = $server_errors[(int) $error];
		header("HTTP/1.0 {$error} {$error_message}");

		\Codebite\Quartz\Exception\Handler::asplode('Server error', sprintf('An error was encountered while processing your request.<br /><br /><strong>Error:</strong> %1$d %2$s', $error, $error_message));
		exit;
	}
}
