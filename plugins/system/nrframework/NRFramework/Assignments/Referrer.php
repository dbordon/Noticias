<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            http://www.tassos.gr
 *  @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignments\URLBase;

class Referrer extends URLBase
{
   	/**
   	 *  Pass Referrer URL. 
   	 *
   	 *  @return  bool   Returns true if the Referrer URL contains any of the selection URLs 
   	 */
   	public function pass()
   	{
   		// Make sure the referer server variable is available
   		if (!isset($_SERVER['HTTP_REFERER']))
   		{
   			return;
   		}

		return $this->passURL($_SERVER['HTTP_REFERER']);
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return string Referrer URL
     */
	public function value()
	{
		return $_SERVER['HTTP_REFERER'];
	}
}