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

class Pageviews extends Assignment
{
    /**
	 * Check the number of pageviews
	 *
	 * @return bool
	 */
	public function pass()
	{
		if (is_null($this->params->views) || !is_numeric($this->params->views))
		{
			return;
		}

		$pageviews = intval($this->params->views);
		$visits    = $this->getVisits();
		$pass      = false;

		switch ($this->selection)
		{
			case 'fewer':
				$pass = $visits < $pageviews;
				break;
			case 'greater':
				$pass = $visits > $pageviews;
				break;
			default: // 'exactly'
				$pass = $visits === $pageviews;
				break;
		}

		return $pass;
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return int Number of page visits
     */
	public function value()
	{
		return $this->getVisits();
	}
    
    /**
     *  Returns the number of page visits
     * 
     *  @return int
     */
    public function getVisits()
    {
        return $this->factory->getSession()->get('session.counter', 0);
    }
}