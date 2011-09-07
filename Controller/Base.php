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

namespace Codebite\Quartz\Controller;
use \OpenFlame\Framework\Core;

/**
 * Quartz - Base controller class,
 * 		Provides a base for contollers to extend.
 *
 *
 * @category    Quartz
 * @package     Quartz
 * @author      Damian Bushong
 * @license     http://opensource.org/licenses/mit-license.php The MIT License
 * @link        https://github.com/damianb/Quartz
 */
abstract class Base
{
	protected $route;

	protected $template_name = '';

	public function setRoute(\OpenFlame\Framework\Router\RouteInstance $route)
	{
		$this->route = $route;
		return $this;
	}

	public static function newInstance()
	{
		return Core::setObject('controller.instance', new static());
	}

	public static function newRoutedInstance(\OpenFlame\Framework\Router\RouteInstance $route)
	{
		$self = static::newInstance();
		$self->setRoute($route);

		return $self;
	}

	public function getTemplateName()
	{
		return $this->template_name;
	}

	abstract public function executePage();
}
