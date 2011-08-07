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
		if (!$this->queryRan || $hard)
		{
			$quartz = Quartz::getInstance();

			$instance = NULL;
			// Fire the debug timing tick
			$quartz->debugtime->newEntry('query->query', '', $instance);

			parent::_query($hard);

			// Fire the debug timing tick
			$quartz->debugtime->newEntry('query->query', '\\OpenFlame\\Dbal\\Query->_query() executed', $instance,
				array(
					'sql'			=> $this->sql,
				)
			);
		}
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
