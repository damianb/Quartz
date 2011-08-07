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

class QueryBuilder extends \OpenFlame\Dbal\QueryBuilder
{
	/**
	 * Chains onto the original method, tacks on some timing mechanisms
	 */
	public function build()
	{
		$quartz = Quartz::getInstance();

		$instance = NULL;
		// Fire the debug timing tick
		$quartz->debugtime->newEntry('querybuilder->build', '', $instance);

		// Chain the call to the parent's method
		parent::build();

		// Fire the debug timing tick
		$quartz->debugtime->newEntry('querybuilder->build', '\\OpenFlame\\Dbal\\QueryBuilder->build() executed', $instance,
			array(
				'querytype'		=> $this->getQueryTypeString(),
				'selects'		=> count($this->select),
				'tables'		=> count($this->tables),
				'updatesets'	=> count($this->sets),
				'incrementsets'	=> count($this->rawSets),
				'insertrows'	=> count($this->rows),
				'wheres'		=> count($this->wheres),
			)
		);

		// Return $this to behave like the parent object

		return $this;
	}

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
			$quartz->debugtime->newEntry('querybuilder->query', '', $instance);

			parent::_query($hard);

			// Fire the debug timing tick
			$quartz->debugtime->newEntry('querybuilder->query', '\\OpenFlame\\Dbal\\QueryBuilder->_query() executed', $instance,
				array(
					'querytype'		=> $this->getQueryTypeString(),
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

	protected function getQueryTypeString()
	{
		switch($this->type)
		{
			case self::TYPE_SELECT:
				return 'SELECT';
			case self::TYPE_UPDATE:
				return 'UPDATE';
			case self::TYPE_INSERT:
				return 'INSERT';
			case self::TYPE_MULTII:
				return 'MULTIINSERT';
			case self::TYPE_DELETE:
				return 'DELETE';
			case self::TYPE_UPSERT:
				return 'UPSERT';
			default:
				return 'UNKNOWN';
		}
	}
}
