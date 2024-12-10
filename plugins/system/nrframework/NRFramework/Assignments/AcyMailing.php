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

class AcyMailing extends Assignment
{
	/**
	 *  Returns the assignment's value
	 * 
	 *  @return  array  AcyMailing lists
	 */
	public function value()
	{
		return $this->getSubscribedLists();
	}

	/**
	 *  Returns all AcyMailing lists the user is subscribed to
	 *
	 *  @return  array  AcyMailing lists
	 */
	private function getSubscribedLists()
	{
		if (!$user = $this->user->id)
		{
			return false;
		}

		// Get a db connection.
		$db = $this->db;
		 
		// Create a new query object.
		$query = $db->getQuery(true);

		$query
			->select(array('list.listid'))
			->from($db->quoteName('#__acymailing_listsub', 'list'))
			->join('INNER', $db->quoteName('#__acymailing_subscriber', 'sub') . ' ON (' . $db->quoteName('list.subid') . '=' . $db->quoteName('sub.subid') . ')')
			->where($db->quoteName('list.status') . ' = 1')
			->where($db->quoteName('sub.userid') . ' = ' . $user)
			->where($db->quoteName('sub.confirmed') . ' = 1')
			->where($db->quoteName('sub.enabled') . ' = 1');
		 
		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		return $db->loadColumn();
	}
}
