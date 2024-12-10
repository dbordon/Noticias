<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/
namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignments\DateTimeBase;

class Time extends DateTimeBase
{
	/**
	 * If set to True, dates will be constructed with modified offset based on the passed timezone
	 *
	 * @var Boolean
	 */
	protected $modify_offset = false;

    /**
	 * Checks if current time passes the given time range
	 *
	 * @return bool
	 */
	public function pass()
	{
        $up   = $this->date->format('Y-m-d', true) . ' ' . $this->params->publish_up;
        $down = $this->date->format('Y-m-d', true) . ' ' . $this->params->publish_down;

        $up   = $this->factory->getDate((string) $up, $this->tz);
        $down = $this->factory->getDate((string) $down, $this->tz);

        return $this->checkRange($up, $down);
    }
    
    /**
     *  Returns the assignment's value
     * 
     *  @return \Date Current date
     */
	public function value()
	{
		return $this->date;
	}
}