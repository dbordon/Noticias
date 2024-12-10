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
use Joomla\CMS\HTML\HTMLHelper;
use NRFramework\Countries;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFTelephone extends ACF_Field
{
	/**
	 *  Field's Class
	 *
	 *  @var  string
	 */
	protected $class = 'input-xlarge w-100';

	/**
	 *  Field's Hint Description
	 *
	 *  @var  string
	 */
	protected $hint = '+123 456 789';

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
			$value = $option->getValue();

			if (is_string($value) && json_decode($value, true))
			{
				$value = json_decode($value, true);
			}

			if (is_array($value))
			{
				$countryCode = isset($value['code']) ? $value['code'] : '';
				$phoneNumber = isset($value['value']) ? $value['value'] : '';

				if ($phoneNumber)
				{
					$calling_code = Countries::getCallingCodeByCountryCode($countryCode);
					$calling_code = $calling_code !== '' ? '+' . $calling_code : '';

					$value = $calling_code . $phoneNumber;
				}
				else
				{
					$value = '';
				}
			}

			$option->setLabel($value);
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

		$inputmask = $field->fieldparams->get('tel_mask', '');
		
		// Set custom class and type
		$fieldNode->setAttribute('class', $this->class);
		$fieldNode->setAttribute('type', 'tftel');

		// Get the Input Mask entered on Field settings
		$fieldNode->setAttribute('inputmask', $inputmask);

		$fieldValue = isset($field->value) ? $field->value : $field->default_value;
		$fieldValue = json_decode($fieldValue, true) ? json_decode($fieldValue, true) : $fieldValue;

		$countryCodeSelectorEnabled = $field->fieldparams->get('enable_country_selector', '0') === '1';

		
		
		$fieldValue = is_array($fieldValue) && isset($fieldValue['value']) ? $fieldValue['value'] : $fieldValue;

		
		
		// Load input mask script
		if ($inputmask)
		{
			if (!$countryCodeSelectorEnabled)
			{
				$fieldNode->setAttribute('class', 'acf-input-mask');
			}
			
			$fieldNode->setAttribute('input_class', 'acf-input-mask');
			$fieldNode->setAttribute('data-imask', $inputmask);

			HTMLHelper::script('plg_system_nrframework/vendor/inputmask.min.js', ['relative' => true, 'version' => 'auto']);
			HTMLHelper::script('plg_fields_acftelephone/script.js', ['relative' => true, 'version' => 'auto']);
		}

		return $fieldNode;
	}

	
}