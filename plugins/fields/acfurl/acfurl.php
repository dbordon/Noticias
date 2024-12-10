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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFUrl extends ACF_Field
{
	/**
	 *  Override the field type
	 *
	 *  @var  string
	 */
	protected $overrideType = 'UrlAdvanced';

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
			$data = $option->getData();
			$value = isset($data->value) ? $data->value : '';

			if ($value)
			{
				$value = json_decode($value, true);
				$value = isset($value['text']) ? Text::_($value['text']) : '';
			}

			$option->setLabel($value);
		}

        return $options;
	}

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   Form      $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Make sure we are manipulating the right field.
		if (isset($data->type) && ($data->type == $this->_name))
		{
			$form->removeField('default_value');
		}
		
		return parent::onContentPrepareForm($form, $data);
	}

	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   JForm       $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
	{
		if (!$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form))
		{
			return;
		}

		$fieldNode->setAttribute('default', json_encode([
			'text'   => $field->fieldparams->get('default_text'),
			'target' => $field->fieldparams->get('default_target', 'same_tab')
		]));

		return $fieldNode;
	}
}