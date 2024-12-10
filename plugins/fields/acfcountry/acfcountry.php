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

class PlgFieldsACFCountry extends ACF_Field
{	
	/**
	 *  Override the field type
	 *
	 *  @var  string
	 */
	protected $overrideType = 'NR_Geo';

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

        foreach ($options as $option)
		{
			if (!$country = \NRFramework\Countries::getCountry($option->getValue()))
			{
				continue;
			}
			
			$option->setLabel($country['name']);
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
			return $fieldNode;
		}

        $fieldNode->setAttribute('layout', 'joomla.form.field.list-fancy-select');

		$fieldNode->setAttribute('multiple', (bool) $field->fieldparams->get('multiple_selection', false));
		$fieldNode->setAttribute('detect_visitor_country', (bool) $field->fieldparams->get('detect_visitor_country', false));
		return $fieldNode;
	}

	/**
	 * Prepares the field value for the (front-end) layout
	 *
	 * @param   string    $context  The context.
	 * @param   stdclass  $item     The item.
	 * @param   stdclass  $field    The field.
	 *
	 * @return  string
	 */
	public function onCustomFieldsPrepareField($context, $item, $field)
	{
		// Check if the field should be processed by us
		if (!$this->isTypeSupported($field->type))
		{
			return parent::onCustomFieldsPrepareField($context, $item, $field);
		}

		$countries = $field->value;
		
		if (!is_array($countries))
		{
			$countries = [$countries];
		}

		if ($field->fieldparams->get('countrydisplay', 'name') == 'name')
		{
			if (!is_array($countries))
			{
				$countries = [$countries];
			}
			
			$countries_temp = [];
			
			foreach ($countries as $c)
			{
				if (!$country = \NRFramework\Countries::getCountry($c))
				{
					continue;
				}
				
				$countries_temp[] = $country['name'];
			}
		
			$countries = $countries_temp;
		}

		$field->value = implode (', ', $countries);

		return parent::onCustomFieldsPrepareField($context, $item, $field);
	}
}
