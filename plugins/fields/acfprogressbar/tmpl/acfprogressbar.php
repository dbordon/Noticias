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

use Joomla\CMS\HTML\HTMLHelper;

$percentage = floatval($field->value);

if (empty($percentage))
{
	return;
}

// Add Media Files
HTMLHelper::stylesheet('plg_fields_acfprogressbar/style.css', ['relative' => true, 'version' => 'auto']);

$height         = $fieldParams->get('height', '18');
$color          = $fieldParams->get('color', '#007bff');
$stripped       = $fieldParams->get('stripped', '0');
$animated       = $fieldParams->get('animated', '0');
$roundedcorners = $fieldParams->get('roundedcorners', '1');
$shadow       	= $fieldParams->get('shadow', '1');
$show_label     = $fieldParams->get('show_label', 'center');

$progressbar_atts = 'height: '.$height.'px;';
$progressbar_cls = ($roundedcorners == '1') ? ' acf_progressbar_rounded' : '';
$progressbar_cls .= ($shadow == '1') ? ' acf_progressbar_shadow' : '';

$color_att = 'background-color: '.$color.';';

$bar_cls = '';
$bar_cls .= ($stripped == '1') ? ' acf_progressbar_stripes' : '';
$bar_cls .= ($animated == '1') ? ' acf_progressbar_animated' : '';
$bar_cls .= ($roundedcorners == '1') ? ' acf_progressbar_rounded' : '';

$buffer = '
<div class="acf_progressbar_wrapper">
	<div class="acf_progressbar' . $progressbar_cls . '" style="'.$progressbar_atts.'">
		<div class="acf_bar'.$bar_cls.'" style="'.$color_att.'width: '.$percentage.'%">
';

if ($show_label != '0')
{
	$buffer .= '<div class="acf_progressbar_label acf_' . $show_label . '">' . $percentage . '%</div>';
}

$buffer .= '</div></div></div>';

echo $buffer;