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

if (!$telephone = htmlentities($field->value, ENT_COMPAT, 'UTF-8'))
{
	return;
}

// Remove underscores
$telephone = str_replace('_', '', $telephone);

$click_to_call = (bool) $fieldParams->get('click_to_call', true);

$buffer = $telephone;

// Output
if ($click_to_call)
{
	// Remove hyphens
	$telephoneCode = str_replace('-', '', $telephone);
	
	$buffer = '<a href="tel:' . $telephoneCode . '">' . $telephone . '</a>';
}

echo $buffer;
