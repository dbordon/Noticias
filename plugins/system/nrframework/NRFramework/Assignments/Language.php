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

class Language extends Assignment
{
	/**
     *  Returns the assignment's value
     * 
     *  @return array Language strings
     */
	public function value()
	{
		return $this->getLanguage();
	}

	public function getLanguage()
	{
		$lang_strings 	= $this->factory->getLanguage()->getLocale();
		$lang_strings[] = $this->factory->getLanguage()->getTag();
		
		return $lang_strings;
	}
}