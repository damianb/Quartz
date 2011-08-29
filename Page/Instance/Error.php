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
use \Codebite\Quartz\Site as Quartz;
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
	protected $error_code = 0;

	protected $template_name = 'error.twig.html';

	public function setErrorCode($error_code)
	{
		$this->error_code = (int) $error_code;
	}

	public function executePage()
	{
		$quartz = Quartz::getInstance();

		if(!empty($this->route))
		{
			$this->setErrorCode($this->route->get('code'));
		}

		$quartz->header->setHTTPStatus($this->error_code);
		try
		{
			$error_string = $quartz->header->getHTTPStatusHeader();
		}
		catch(\LogicException $e)
		{
			$quartz->header->setHTTPStatus(500);
			$error_string = $quartz->header->getHTTPStatusHeader();
		}

		$error_string = str_replace('HTTP/1.0 ', '', $error_string);

		$quartz->template->assignVars(array(
			'error_code'	=> $this->error_code,
			'error_string'	=> $error_string,
		));

		return;
	}
}
