<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace ACF\Helpers;

defined('_JEXEC') or die;

class Field
{
	/**
	 * Returns the widget for which the ACF custom field is based upon.
	 * 
	 * @param   string  $type
	 * @param   object  $field_options
	 * 
	 * @return  string
	 */
	public static function isWidgetBased($type = '', $field_options = [])
	{
		if (!$type)
		{
			return false;
		}

		$widget_name = self::getWidgetName($type);
		
		$widget = \NRFramework\Widgets\Helper::find($widget_name);
		
		if ($widget === 'Map' && !is_null($field_options->fieldparams))
		{
			return !empty($field_options->fieldparams['provider']) ? $field_options->fieldparams['provider'] : $widget;
		}
		
		return $widget;
	}

	/**
	 * Returns the widget name of an ACF custom field.
	 * 
	 * @param   string  @type
	 * 
	 * @return  string
	 */
	public static function getWidgetName($type = '')
	{
		// Remove the "acf" prefix
		$type = str_replace('acf', '', $type);

		// Map for any ACF fields that do not automatically translate to a Widget
		$map = [
			'address' => 'MapAddress'
		];

		// Transform a field type to its corresponding widget from the map
		return isset($map[$type]) ? $map[$type] : $type;
	}
}