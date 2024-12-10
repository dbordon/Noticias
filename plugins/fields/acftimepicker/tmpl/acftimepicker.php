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

if (!$value = $field->value)
{
	return;
}

if (!$time = DateTime::createFromFormat('H:i', $value))
{
	return;
}

echo $time->format($fieldParams->get('timeformat', 'H:i'));