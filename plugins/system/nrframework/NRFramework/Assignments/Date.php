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

class Date extends DateTimeBase
{
    /**
	 *  Checks if current date passes the given date range. 
	 *  Dates must be always passed in format: Y-m-d H:i:s
	 *
	 *  @return  bool
	 */
	public function pass()
	{
        // No valid dates
		if (!$this->params->publish_up && !$this->params->publish_down)
		{
			return false;
		}
		
		$up   = $this->params->publish_up   ? $this->getDate($this->params->publish_up)   : null;
		$down = $this->params->publish_down ? $this->getDate($this->params->publish_down) : null;

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