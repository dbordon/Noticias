<?php
/**
 * @package    System - JLSitemap Cron Plugin
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

namespace Joomla\Plugin\System\Jlsitemap_cron\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

class KeyField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  0.0.2
	 */
	protected $type = 'key';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var  string
	 *
	 * @since  0.0.2
	 */
	protected $layout = 'plugins.system.jlsitemap_cron.fields.key';

 	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since  0.0.2
	 */
	protected function getLabel()
	{
		return '';
	}
}