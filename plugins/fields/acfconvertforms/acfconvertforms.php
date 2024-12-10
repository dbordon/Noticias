<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFConvertForms extends ACF_Field
{
	/**
	 * Update the label of the field in filters.
     * 
     * @param \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection $options
	 * 
     * @return \Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection
     */
    public function onJFiltersOptionsAfterCreation(\Bluecoder\Component\Jfilters\Administrator\Model\Filter\Option\Collection $options) 
    {
		// Make sure it is a field of that type
        if ($options->getFilterItem()->getAttributes()->get('type') !== $this->_name)
		{
            return $options;
        }

		if (!class_exists('\ConvertForms\Form'))
		{
			return $options;
		}

        foreach ($options as $option)
		{
			if (!$form = ConvertForms\Form::load($option->getLabel()))
			{
				continue;
			}

			$option->setLabel($form['name']);
        }

        return $options;
	}

	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   Form        $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Joomla\CMS\Form\Form $form)
	{
		if (!$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form))
		{
			return;
		}

		$form->addFieldPath(JPATH_ADMINISTRATOR . '/components/com_convertforms/models/forms/fields');

		$fieldNode->setAttribute('type', 'convertforms');

		$option = new \DOMElement('option');
		$option->textContent = htmlspecialchars('- ' . Text::_('JSELECT') . ' -', ENT_COMPAT, 'UTF-8');
		$element = $fieldNode->appendChild($option);

		return $fieldNode;
	}
}
