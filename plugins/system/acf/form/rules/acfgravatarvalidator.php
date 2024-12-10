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

use Joomla\CMS\Form\FormRule;

defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class JFormRuleACFGravatarValidator extends FormRule
{
	/**
	 * Method to test the calendar value for a valid parts.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   3.7.0
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, \Joomla\Registry\Registry $input = NULL, \Joomla\CMS\Form\Form $form = NULL)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		// Allow Smile Pack Smart Tags
		if (StringHelper::strpos($value, '{sp') !== false)
		{
			return true;
		}

		// Validate email
		if (filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			return true;
		}

		Factory::getApplication()->enqueueMessage(Text::_('ACF_INVALID_EMAIL'), 'error');
		return false;
	}
}
