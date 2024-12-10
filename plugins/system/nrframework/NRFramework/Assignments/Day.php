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

class Day extends DateTimeBase
{
    /**
     * Check current weekday
     *
     * @return bool
     */
    public function pass()
    {
        if (is_array($this->selection) && !empty($this->selection))
        {
            // convert selection values to lowercase
            $this->selection = array_map("strtolower", $this->selection);

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
            // 'D' -> Abbreviated day name (Mon to Sun) 
            // http://php.net/manual/en/function.date.php
            $today          = $this->date->format('N');
            $todayText      = strtolower($this->date->format('l'));
            $todayTextAbbrv = strtolower($this->date->format('D'));
            if (in_array($today, $this->selection) ||
                in_array($todayText, $this->selection) ||
                in_array($todayTextAbbrv, $this->selection))
            {
                return true;
            }
        }      

        return false;
    }
    
    /**
     *  Returns the assignment's value
     * 
     *  @return string Name of the current day
     */
	public function value()
	{
		return $this->date->format('l');
	}
}