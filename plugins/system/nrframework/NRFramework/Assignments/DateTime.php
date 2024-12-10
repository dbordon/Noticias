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

class DateTime extends Assignment
{
	/**
	 * Server's Timezone
	 *
	 * @var DateTimeZone
	 */
	private $tz;

	/**
	 *  Class constructor
	 *
	 *  @param  object  $assignment
	 */
	public function __construct($assignment)
	{
		parent::__construct($assignment);

		$this->tz = new \DateTimeZone($this->app->getCfg('offset'));
	}
	/**
	 *  Checks if current date passes the date range
	 *
	 *  @return  bool
	 */
	function passDate()
	{
        // No valid dates
		if (!$this->params->publish_up && !$this->params->publish_down)
		{
			return false;
        }
        $format = 'Y-m-d H:i:s';
		$up     = $this->params->publish_up;
		$down   = $this->params->publish_down;		

        // fix the date string
		\NRFramework\Functions::fixDate($up);
		\NRFramework\Functions::fixDate($down);

		$up   = $up   ? \JDate::createFromFormat($format, $up, $this->tz) : null;
		$down = $down ? \JDate::createFromFormat($format, $down, $this->tz) : null;

        return $this->checkRange($up, $down);
	}

	/**
	 * Checks if current time passes the given time range
	 *
	 * @return bool
	 */
	public function passTimeRange()
	{
        if (!is_null($this->params->publish_up))
        {
            list($up_hours, $up_mins) = explode(':', $this->params->publish_up);
        }
        
        if (!is_null($this->params->publish_down))
        {
            list($down_hours, $down_mins) = explode(':', $this->params->publish_down);
        }

        // do comparison using time only
		$up   = is_null($this->params->publish_up)   ? null : \JFactory::getDate()->setTimezone($this->tz)->setTime($up_hours, $up_mins);
		$down = is_null($this->params->publish_down) ? null : \JFactory::getDate()->setTimezone($this->tz)->setTime($down_hours, $down_mins);

		return $this->checkRange($up, $down);
    }
    
    /**
     * Check current weekday
     *
     * @return bool
     */
    public function passDays()
    {
        if (is_array($this->selection) && !empty($this->selection))
        {
            // convert 'weekdays' and 'weekend' values to day ids
            foreach ($this->selection as $d)
            {
                if (preg_match('/^weekdays?$/', trim($d)))
                {
                    $this->selection = array_merge($this->selection, range(1, 5));
                    continue;
                }

                if ($d === 'weekend')
                {
                    $this->selection = array_merge($this->selection, [6, 7]);
                }
            }
            $this->selection = array_unique($this->selection);

            // 'N' -> week day
            // 'l' -> fulltext week day
            // http://php.net/manual/en/function.date.php
            $today      = $this->date->format('N');
            $todayText  = $this->date->format('l');

            if (in_array($today, $this->selection) ||
                in_array($todayText, $this->selection))
            {
                return true;
            }
        }      

        return false;
    }

    /**
     * Check current month
     *
     * @return void
     */
    public function passMonths()
    {
        if (is_array($this->selection) && !empty($this->selection))
        {
            // 'n' -> month number (1 to 12)
            // 'F' -> full-text month name
            // http://php.net/manual/en/function.date.php
            $month      = $this->date->format('n');
            $monthText  = $this->date->format('F');

            if (in_array($month, $this->selection) ||
                in_array($monthText, $this->selection))
            {
                return true;
            }
        }      

        return false;
    }

	/**
	 * Checks if the current datetime is between the specified range
	 *
	 * @param JDate &$up_date
	 * @param JDate &$down_date
	 * 
	 * @return bool
	 */
	private function checkRange(&$up_date, &$down_date)
	{
		$now = $this->date->getTimestamp();

		if (((bool)$up_date   && $up_date->getTimestamp() > $now) ||
			((bool)$down_date && $down_date->getTimestamp() < $now))
		{
			return false;
		}

		return true;
	}
}