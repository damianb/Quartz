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

namespace Codebite\Quartz\Debug;
use \Codebite\Quartz\Site as Quartz;
use \OpenFlame\Framework\Utility\JSON;

// Tracks time points in the script, useful for profiling, finding slow points, inefficient queries, etc.
class Tracker
{
	protected $data = array();
	protected $types = array();

	public function __construct()
	{
		$quartz = Quartz::getInstance();
		$this->data[] = array(
			'type'				=> 'app',
			'instance'			=> 0,
			'time'				=> round((float) 0, 7),
			'timespan'			=> (float) 0,
			'description'		=> 'Application timing start',
			'data'				=> '',

		);
	}

	public function newEntry($type, $description, &$instance, $data = NULL)
	{
		$quartz = Quartz::getInstance();

		$time = $quartz->timer->mark(sprintf('tracker_%s', $type));
		if(empty($instance))
		{
			if(isset($this->types[(string) $type]))
			{
				$instance = $this->types[(string) $type]['count']++;
			}
			else
			{
				$instance = $this->types[(string) $type] = array(
					'count'		=> 1,
					'basetime'	=> $time,
				);
			}
		}

		$this->data[] = array(
			'type'				=> (string) $type,
			'instance'			=> (int) $instance,
			'time'				=> $time,
			'timespan'			=> $time - $this->types[(string) $type]['basetime'],
			'description'		=> ($description) ?: 'No description',
			'data'				=> JSON::encode($data),
		);

		return $time;
	}

	public function getEntries()
	{
		return $this->data;
	}
}
