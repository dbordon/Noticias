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

class Month extends DateTimeBase
{
    /**
     * Check current month
     *
     * @return void
     */
    public function pass()
    {
        if (is_array($this->selection) && !empty($this->selection))
        {
            // convert selection values to lowercase
            $this->selection = array_map("strtolower", $this->selection);

            // 'n' -> month number (1 to 12)
            // 'F' -> full-text month name
            // 'M' -> Abbreviated month name (Jan to Dec) 
            // http://php.net/manual/en/function.date.php
            $month          = $this->date->format('n');
            $monthText      = strtolower($this->date->format('F'));
            $monthTextAbbrv = strtolower($this->date->format('M'));
            if (in_array($month, $this->selection) ||
                in_array($monthText, $this->selection) ||
                in_array($monthTextAbbrv, $this->selection))
            {
                return true;
            }
        }      

        return false;
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return string Name of the current month
     */
	public function value()
	{
		return $this->date->format('F');
	}
}