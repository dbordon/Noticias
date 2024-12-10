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

use Joomla\CMS\Factory;

if (!$twitter_handle = htmlentities($field->value, ENT_COMPAT, 'UTF-8'))
{
	return;
}

$large_button = $fieldParams->get('follow.large_button', true);
$show_username = $fieldParams->get('follow.show_username', true);
$show_count = $fieldParams->get('follow.show_count', true);

// Load twitter's widgets library
$doc = Factory::getDocument();
$doc->addScript('https://platform.twitter.com/widgets.js');

echo '<a href="https://twitter.com/' . $twitter_handle . '" 
		class="twitter-follow-button" 
		data-show-count="' . ($show_count ? "true" : "false") . '"
		data-size="' . ($large_button ? "large" : "") . '"
		data-show-screen-name="' . ($show_username ? "true" : "false") . '">
      </a>';
      
?>