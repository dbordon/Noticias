<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFEmail extends ACF_Field
{
	protected $validate = 'email';
	
	/**
	 *  Override the field type
	 *
	 *  @var  string
	 */
	protected $overrideType = 'email';

	/**
	 *  Field's Hint Description
	 *
	 *  @var  string
	 */
	protected $hint = 'email@example.com';

	/**
	 *  Field's Class
	 *
	 *  @var  string
	 */
	protected $class = 'input-xlarge w-100';
}
