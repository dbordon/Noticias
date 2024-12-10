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

class URL extends URLBase
{
   	/**
   	 *  Pass URL. 
   	 *
   	 *  @return  bool   Returns true if the current URL contains any of the selection URLs 
   	 */
   	public function pass()
   	{
		return $this->passURL();
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return string Current URL
     */
	public function value()
	{
		return $this->factory->getURL();
	}
}