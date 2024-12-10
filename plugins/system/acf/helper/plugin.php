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

if (!@include_once(JPATH_PLUGINS . '/system/nrframework/autoload.php'))
{
	throw new RuntimeException('Novarain Framework is not installed', 500);
}

use \NRFramework\HTML;
use \ACF\Helpers\Field;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

JLoader::register('ACFHelper', __DIR__ . '/helper.php');

class ACF_Field extends FieldsPlugin
{
	/**
	 *  Override the field type
	 *
	 *  @var  string
	 */
	protected $overrideType;

	/**
	 *  Whether the field is required
	 *
	 *  @var  bool
	 */
	protected $required = false;

	/**
	 *  The validation rule will be used to validate the field on saving
	 *
	 *  @var  string
	 */
	protected $validate;

	/**
	 *  Field's Hint Description
	 *
	 *  @var  string
	 */
	protected $hint;

	/**
	 *  Field's Class
	 *
	 *  @var  string
	 */
	protected $class;

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
		if (!$field->rawvalue)
		{
			return parent::onCustomFieldsPrepareField($context, $item, $field);
		}

		// Add Custom CSS
		$custom_css = $field->params->get('acf_custom_css');
		if (!empty($custom_css))
		{
			
			Factory::getDocument()->addStyleDeclaration($custom_css);
		}
		
		return parent::onCustomFieldsPrepareField($context, $item, $field);
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
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
	{
		if (!$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form))
		{
			return;
		}

		// Load framework's fields
		$form->addFieldPath(JPATH_PLUGINS . '/system/nrframework/fields');
		$form->addFieldPath(JPATH_PLUGINS . '/fields/' . $field->type . '/fields');

		// Set Field Class
		if ($this->class)
		{
			$fieldNode->setAttribute('class', $this->class);
		}

		// Set field placeholder
		if ($hint = $field->params->get('hint', $this->hint))
		{
			$fieldNode->setAttribute('hint', $hint);
		}

		// Set Field Type
		if ($this->overrideType)
		{
			$fieldNode->setAttribute('type', $this->overrideType);
		}

		// Set Field Required
		if ($this->required)
		{
			$fieldNode->setAttribute('required', $this->required);
		}

		// Set validation rule
		if ($this->validate)
		{
			$form->addRulePath(JPATH_PLUGINS . '/system/nrframework/NRFramework/Rules');
			$form->addRulePath(JPATH_PLUGINS . '/system/acf/form/rules');
			$fieldNode->setAttribute('validate', $this->validate);
		}

		// Set Field Description
		$desc_def  = Text::_(str_replace('ACF', 'ACF_', strtoupper($field->type)) . '_VALUE_DESC');
		$desc_user = $fieldNode->getAttribute('description');
		$desc      = !empty($desc_user) ? $desc_user : $desc_def;
	
		$fieldNode->setAttribute('description', $desc);

		// Gather all Joomla 3 CSS fixes in a file and load it.
		if (!defined('nrJ4'))
		{
			HTMLHelper::stylesheet('plg_system_nrframework/joomla3.css', ['relative' => true, 'version' => 'auto']);
			HTMLHelper::stylesheet('plg_system_acf/joomla3.css', ['relative' => true, 'version' => 'auto']);
		}
		else
		{
			HTMLHelper::stylesheet('plg_system_nrframework/joomla4.css', ['relative' => true, 'version' => 'auto']);
		}

		return $fieldNode;
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
		$data = (object) $data;

		// Make sure we are manipulating the right field.
		if (!isset($data->type) || (isset($data->type) && ($data->type != $this->_name)))
		{
			return;
		}

		if (isset($data->type))
		{
			// Remove "layout" from ACF - Options if the current custom field is not widget-based
			$widget = Field::isWidgetBased($data->type, $data);
			if (Factory::getApplication()->isClient('administrator') && !$widget)
			{
				$form->removeField('acf_layout_override', 'params');
			}
		}

		// Load framework's fields
		$form->addFieldPath(JPATH_PLUGINS . '/system/nrframework/fields');

		// Load ACF fields
		$form->addFieldPath(JPATH_PLUGINS . '/system/acf/form/fields');
		
		if (Factory::getApplication()->isClient('administrator'))
		{
			// Set the wiget in the "acf_layout_override" field in order to retrieve all layout overrides created by the user for this field.
			if (isset($widget))
			{
				$form->setFieldAttribute('acf_layout_override', 'widget_name', $widget, 'params');
				if (isset($data->type))
				{
					$form->setFieldAttribute('acf_layout_override', 'field_type', $data->type, 'params');
				}
			}

			// load ACF backend style
			HTMLHelper::stylesheet('plg_system_acf/acf-backend.css', ['relative' => true, 'version' => 'auto']);
			
			if (defined('nrJ4'))
			{
				HTMLHelper::stylesheet('plg_system_nrframework/joomla4.css', ['relative' => true, 'version' => 'auto']);
				HTML::fixFieldTooltips();
			}
		
			// Display extension notices
			\NRFramework\Notices\Notices::getInstance([
				'ext_element' => 'acf',
				'ext_type' => 'plugin',
				'ext_xml' => 'plg_system_acf',
				'exclude' => [
					'Geolocation'
				]
			])->show();
		}

		return parent::onContentPrepareForm($form, $data);
	}
}
