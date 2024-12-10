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

class GroupLevel extends Assignment
{
	/**
     *  md5 hash used for caching the groups map
     *
     *  @var string
     */
    protected $_groupsHash;

    /**
     *  Constructor
     *
     *  @param object $options
     *  @param object $factory
     */
    public function __construct($options, $factory)
    {
        parent::__construct($options, $factory);

        $_groupsHash = md5('NRFramework\Assignments\User_groupsHash');
	}
	
   	/**
   	 *  Check user grouplevel
   	 *
   	 *  @return  bool   Returns true if the Referrer URL contains any of the selection URLs 
   	 */
	public function pass()
	{
		$groups = $this->getGroups();

		// replace group names with ids in selection
		foreach ($this->selection as $key => $id)
		{
			if (!is_numeric($id))
			{
				$this->selection[$key] = array_search(strtolower($id), $groups);
			}
		}

		return $this->passSimple($this->value(), $this->selection); 
	}

    /**
     *  Returns the assignment's value
     * 
     *  @return array User groups
     */
	public function value()
	{
		return !empty($this->user->groups) ? array_values($this->user->groups) : $this->user->getAuthorisedGroups();
	}

	/**
     *  Returns User Groups map (ID => Name)
     *
     *  @return array
     */
    protected function getGroups()
    {
		$cache = $this->factory->getCache();
        if ($cache->has($this->_groupsHash))
        {
            return $cache->get($this->_groupsHash);
        }

        $db = $this->factory->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('id, title')
            ->from('#__usergroups');
        $db->setQuery($query);
        
        $res = $db->loadObjectList();
        $groups = [];
        foreach ($res as $r)
        {
            $groups[$r->id] = strtolower($r->title);
        }
        $cache->set($this->_groupsHash, $groups);

        return $groups;
    }
}