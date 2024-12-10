<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

if (!$markers = $field->value)
{
	return;
}

if (is_string($markers) && !$markers = json_decode($markers, true))
{
	return;
}

// Get Plugin Params
$plugin = PluginHelper::getPlugin('fields', 'acfmap');
$params = new Registry($plugin->params);

// Find provider
$provider = $fieldParams->get('provider', 'OpenStreetMap');
$provider_key = $params->get(strtolower($provider) . '_key');
$maptype = $fieldParams->get(strtolower($provider) . '_maptype');

$maptype = is_null($maptype) ? ($provider === 'GoogleMap' ? 'roadmap' : 'road') : $maptype;

$payload = $fieldParams->flatten();
$payload = array_merge($payload, [
	'replaceSmartTags' => true,
	'provider_key' => $provider_key,
	'width' => $fieldParams->get('width_control.width'),
	'height' => $fieldParams->get('height_control.height'),
	'markers' => $markers,
	'map_center' => $fieldParams->get('map_center.coordinates'),
	'enable_info_window' => $fieldParams->get('enable_info_window', '0') !== '0' ? $fieldParams->get('enable_info_window', '0') : false,
	
	'scale' => false,
	'view' => 'road' . ($provider === 'GoogleMap' ? 'map' : ''),
	
	
]);

// Set custom layout
if ($field->params->get('acf_layout_override'))
{
	$payload['layout'] = $field->params->get('acf_layout_override');
}

echo \NRFramework\Widgets\Helper::render($provider, $payload);