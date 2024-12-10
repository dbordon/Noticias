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

$qrcode_text = htmlspecialchars($field->value, ENT_COMPAT, 'UTF-8');

if ($qrcode_text == '')
{
	return;
}

// QR Code Label to be used as `alt`
$label = $field->label;

// size, color and bg color
$size	  = $fieldParams->get('size', '100');
$size	  = str_replace('px', '', $size);
$color	  = ltrim($fieldParams->get('color', '#000000'), '#');
$bgcolor  = ltrim($fieldParams->get('bgcolor', '#ffffff'), '#');

// create size, ex. 50x50
$size_att = $size . 'x' . $size;

$buffer = '<img src="https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrcode_text) . '&size=' . $size_att . '&color=' . $color . '&bgcolor=' . $bgcolor . '&format=png" alt="' . $label . '" />';

echo $buffer;