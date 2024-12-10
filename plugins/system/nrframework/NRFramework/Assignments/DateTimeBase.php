<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/
namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignment;

class DateTimeBase extends Assignment
{
	/**
	 * Server's Timezone
	 *
	 * @var DateTimeZone
	 */
	protected $tz;

	/**
	 * If set to True, dates will be constructed with modified offset based on the passed timezone
	 *
	 * @var Boolean
	 */
	protected $modify_offset = true;

	/**
	 *  Class constructor
	 *
	 *  @param  object  $assignment
	 */
	public function __construct($assignment, $factory)
	{
		parent::__construct($assignment, $factory);

		// Set timezone
		if (property_exists($assignment->params, 'timezone') && !empty($assignment->params->timezone))
		{
			$this->tz = new \DateTimeZone($assignment->params->timezone);
		}
		else
		{
			$this->tz = new \DateTimeZone($this->app->getCfg('offset', 'GMT'));
		}

		// Set modify offset switch
		if (property_exists($this->params, 'modify_offset'))
		{
			$this->modify_offset = (bool) $this->params->modify_offset;
		}

		// Set now date
		$now = property_exists($assignment->params, 'now') ? $assignment->params->now : 'now';
		$this->date = $this->getDate($now);
	}

	/**
	 * Checks if the current datetime is between the specified range
	 *
	 * @param JDate &$up_date
	 * @param JDate &$down_date
	 * 
	 * @return bool
	 */
	protected function checkRange(&$up_date, &$down_date)
	{
        if (!$up_date && !$down_date)
        {
            return false;
		}
 
		$now = $this->date->getTimestamp();

		if (((bool)$up_date   && $up_date->getTimestamp() > $now) ||
			((bool)$down_date && $down_date->getTimestamp() < $now))
		{
			return false;
		}

		return true;
	}

	/**
	 * Create a date object based on the given string and apply timezone.
	 *
	 * @param  String $date
	 *
	 * @return void
	 */
	protected function getDate($date = 'now')
	{
		// Fix the date string
		\NRFramework\Functions::fixDate($date);

		if ($this->modify_offset)
		{
			// Create date, set timezone and modify offset
			$date = $this->factory->getDate($date)->setTimeZone($this->tz);
		} else 
		{
			// Create date and set timezone without modifyig offset
			$date = $this->factory->getDate($date, $this->tz);
		}

		return $date;
	}
}