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

if (!$videoURL = $field->value)
{
	return;
}

$payload = [
	'value' => $videoURL,
	'width' => $fieldParams->get('width', '480px'),
	'height' => $fieldParams->get('height', '270px'),
	'privacy' => $fieldParams->get('privacyMode', '0') === '1',
	
];

// Set custom layout
if ($field->params->get('acf_layout_override'))
{
	$payload['layout'] = $field->params->get('acf_layout_override');
}

echo \NRFramework\Widgets\Helper::render('YouTube', $payload);