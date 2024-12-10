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

class URLBase extends Assignment 
{   	
   	/**
   	 *  Pass URL
   	 *
   	 *  @param   mixed  $url    If null, the current URL will be used. Otherwise we need a valid absolute URL.
   	 *
   	 *  @return  bool   		Returns true if the URL contains any of the selection URLs 
   	 */
	public function passURL($url = null)
	{
		// Get the current URL if none is passed
		$url = is_null($url) ? $this->factory->getURL() : $url;

		// Create an array with all possible values of the URL
		$urls = array(
			html_entity_decode(urldecode($url), ENT_COMPAT, 'UTF-8'),
			urldecode($url),
			html_entity_decode($url, ENT_COMPAT, 'UTF-8'),
			$url
		);

		// Remove duplicates and invalid URLs
		$urls = array_filter(array_unique($urls));

		// Validate Selection
		if (!is_array($this->selection))
		{
			$this->selection = $this->splitKeywords($this->selection);
		}

		$regex = isset($this->params->regex) ? (bool)$this->params->regex : false;
		$pass  = false;

		foreach ($urls as $url)
		{
			foreach ($this->selection as $s)
			{
				// Skip empty selection URLs
				$s = trim($s);
				if (empty($s))
				{
					continue;
				}

				// Regular expression check
				if ($regex)
				{
					$url_part = str_replace(array('#', '&amp;'), array('\#', '(&amp;|&)'), $s);
					$s = '#' . $url_part . '#si';

					if (@preg_match($s . 'u', $url) || @preg_match($s, $url))
					{
						$pass = true;
						break;
					}

					continue;
				}

				// String check
				if (strpos($url, $s) !== false)
				{
					$pass = true;
					break;
				}
			}

			if ($pass)
			{
				break;
			}
		}

		return $pass;
	}
}