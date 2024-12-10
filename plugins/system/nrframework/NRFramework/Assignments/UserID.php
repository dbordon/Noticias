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

class UserID extends Assignment 
{
	/**
	 * Check User ID
	 *
	 * @return bool
	 */
	public function pass()
	{
		$this->selection = is_array($this->selection) ? $this->selection : explode(',', $this->selection);

		// prepare an array(of ints) from the supplied IDs(string)		
		$ids = array_map('intval', array_map('trim', $this->selection));

		if (in_array($this->user->id, $ids))
		{
			return true;
		}

		return false;
	}

	/**
     *  Returns the assignment's value
     * 
     *  @return int User ID
     */
	public function value()
	{
		return $this->user->id;
	}
}