<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework;

defined('_JEXEC') or die;

use \NRFramework\Cache;

/**
 *  Assignment Class
 */
class Assignment
{
	/**
	 *  Application Object
	 *
	 *  @var  object
	 */
	public $app;

	/**
	 *  Document Object
	 *
	 *  @var  object
	 */
	public $doc;

	/**
	 *  Date Object
	 *
	 *  @var  object
	 */
	public $date;

	/**
	 *  Database Object
	 *
	 *  @var  object
	 */
	public $db;

	/**
	 *  User Object
	 *
	 *  @var  object
	 */
	public $user;

	/**
	 *  Assignment Selection
	 *
	 *  @var  mixed
	 */
	public $selection;

	/**
	 *  Assignment Parameters
	 *
	 *  @var  mixed
	 */
	public $params;

	/**
	 *  Assignment State (Include|Exclude)
	 *
	 *  @var  string
	 */
    public $assignment;
    
    /**
     *  Framework factory object
     */
    public $factory;

	/**
	 *  Class constructor
	 *
	 *  @param  object  $assignment
	 *  @param  object  $request
	 *  @param  object  $date
	 */
	public function __construct($options = null, $factory = null)
	{
        // Save the factory object
        $this->factory = is_null($factory) ? new \NRFramework\Factory() : $factory;

		// Set General Joomla Objects
		$this->db   = $this->factory->getDbo();
		$this->app  = $this->factory->getApplication();
		$this->doc  = $this->factory->getDocument();
		$this->user = $this->factory->getUser();

		// Assignment options object is optional as there are Assignments such as the Component-based, which don't rely
		// on user selection. For instance, in the SmartTags\Article, we need to check whether the user sees an Article, 
		// so we don't need any user selection.
		if ($options)
		{
			$this->selection        = $options->selection;
			$this->assignment_state = isset($options->assignment_state) ? $options->assignment_state : 'include';
			$this->params           = isset($options->params) ? $options->params : null;
		}
    }
    
    /**
     *  Base assignment check
     * 
     *  @return bool
     */
	public function pass()
	{
    	return $this->passSimple($this->value(), $this->selection);
	}

	/**
	 *  Checks if a value (needle) exists in an array (haystack)
	 *
	 *  @param   mixed   $needle     The searched value.
	 *  @param   array   $haystack   The array
	 *
	 *  @return  bool
	 */
	public function passSimple($needle, $haystack)
	{
		if (empty($haystack))
		{
			return false;
		}
		
		$needle = $this->makeArray($needle);
		$pass   = false;

		foreach ($needle as $value)
		{
			if (in_array(strtolower($value), array_map('strtolower', $haystack)))
			{
				$pass = true;
				break;
			}
		}

		return $pass;
	}

	/**
	 *  Checks if an array of values (needle) exists in a text (haystack)
	 *
	 *  @param   array   $needle     The searched array of values.
	 *  @param   string  $haystack   The text
	 *
	 *  @return  bool
	 */
	public function passArrayInString($needle, $haystack)
	{
		if (empty($needle) || empty($haystack))
		{
			return false;
		}

		$needle = $this->splitKeywords($needle);
		
		return \NRFramework\Functions::strpos_arr($needle, $haystack);
	}

	/**
	 *  Makes array from object
	 *
	 *  @param   object  $object  
	 *
	 *  @return  array
	 */
	public function makeArray($object)
	{
		if (is_array($object))
		{
			return $object;
		}

		if (!is_array($object))
		{
			$x = explode(' ', $object);
			return $x;
		}
	}

	/**
	 *  Returns all parent rows
	 *
	 *  @param   integer  $id      Row primary key
	 *  @param   string   $table   Table name
	 *  @param   string   $parent  Parent column name
	 *  @param   string   $child   Child column name
	 *
	 *  @return  array             Array with IDs
	 */
	public function getParentIds($id = 0, $table = 'menu', $parent = 'parent_id', $child = 'id')
	{
		if (!$id)
		{
			return [];
		}

		$cache = $this->factory->getCache(); 
		$hash  = md5('getParentIds_' . $id . '_' . $table . '_' . $parent . '_' . $child);

		if ($cache->has($hash))
		{
			return $cache->get($hash);
		}

		$parent_ids = array();

		while ($id)
		{
			$query = $this->db->getQuery(true)
				->select('t.' . $parent)
				->from('#__' . $table . ' as t')
				->where('t.' . $child . ' = ' . (int) $id);
			$this->db->setQuery($query);
			$id = $this->db->loadResult();

			// Break if no parent is found or parent already found before for some reason
			if (!$id || in_array($id, $parent_ids))
			{
				break;
			}

			$parent_ids[] = $id;
		}

		return $cache->set($hash, $parent_ids);
	}
    
    /**
     *  Splits a keyword string on commas and newlines
     *
     *  @param string $keywords
     *  @return array
     */
    protected function splitKeywords($keywords)
    {
        if (empty($keywords) || !is_string($keywords))
        {
            return [];
        }

        // replace newlines with commas
        $keywords = str_replace("\r\n", ',', $keywords);

        // split keywords on commas
        $keywords = explode(',', $keywords);
        
        // trim entries
        $keywords = array_map(function($str)
        {
            return trim($str);
        }, $keywords);

        // filter out empty strings and return the resulting array
        return array_filter($keywords, function($str)
        {
            return !empty($str);
        });
    }
}