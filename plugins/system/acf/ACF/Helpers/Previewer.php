<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace ACF\Helpers;

defined('_JEXEC') or die;

class Previewer
{
	/**
	 * Returns the field previewer data.
	 * 
	 * @return  array
	 */
	public static function getFieldPreviewData($field = '')
	{
		if (empty($field))
		{
			return;
		}

		// Get path to file
		$file = implode(DIRECTORY_SEPARATOR, [self::getJsonDirectory(), $field . '.json']);

		if (!file_exists($file))
		{
			return;
		}

		return file_get_contents($file);
	}

	/**
	 * Returns the path to the fields previewer JSON directory.
	 * 
	 * @return  string
	 */
	public static function getJsonDirectory()
	{
		return implode(DIRECTORY_SEPARATOR, [JPATH_SITE, 'media', 'plg_system_acf', 'data', 'previewer']);
	}
}