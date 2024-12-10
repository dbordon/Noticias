<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            http://www.tassos.gr
 *  @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignment;

class TimeOnSite extends Assignment
{
    /**
	 *  Pass - Check User's Time on Site
	 *
	 *  @return  bool
	 */
	public function pass()
	{
        $pass = false;
        
        $diffInSeconds = $this->getTimeOnSite();
        if (!$diffInSeconds)
        {
            return $pass;
        }

		if (intval($this->selection) <= $diffInSeconds)
		{
			$pass = true;
		}

		return $pass;
	}

    /**
     *  Returns the assignment's value
     * 
     *  @return int Time on site in seconds
     */
	public function value()
	{
		return $this->getTimeOnSite();
    }
    
    /**
     *  Returns the user's time on site in seconds
     * 
     *  @return int
     */
    public function getTimeOnSite()
    {
        $sessionStartTime = strtotime($this->getSessionStartTime());

		if (!$sessionStartTime)
		{
			return;
		}

		$dateTimeNow = strtotime(\NRFramework\Functions::dateTimeNow());
		return $dateTimeNow - $sessionStartTime;
    }

    /**
     *  Returns the sessions start time
     * 
     *  @return string
     */
    private function getSessionStartTime()
    {
        $session = $this->factory->getSession();
        
        $var = 'starttime';
        $sessionStartTime = $session->get($var);

        if (!$sessionStartTime)
        {
            $date = \NRFramework\Functions::dateTimeNow();
            $session->set($var, $date);
        }

        return $session->get($var);
    }
}