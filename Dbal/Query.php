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

namespace Codebite\Quartz\Dbal;
use \Codebite\Quartz\Site as Quartz;

class Query extends \OpenFlame\Dbal\Query
{
	/**
	 * Chains onto the original method, tacks on some timing mechanisms
	 */
	public function _query($hard = false)
	{
		$quartz = Quartz::getInstance();

		$instance = NULL;
		// Fire the debug timing tick
		$quartz->debugtime->newEntry('query->query', 'Debug timing tick fired before Query->_query() execution', $instance,
			array(
				'sql'			=> $this->sql,
			)
		);

		parent::_query($hard);

		// Fire the debug timing tick
		$quartz->debugtime->newEntry('query->query', 'Debug timing tick fired after Query->_query() execution', $instance);
	}

	/**
	 * @ignore
	 * Debugging backdoor to grab built SQL after build() has been called
	 */
	public function _getSQL()
	{
		return $this->sql;
	}
}
