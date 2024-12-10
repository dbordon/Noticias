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

use Joomla\CMS\Language\Text;

class PlgFieldsACFTrueFalse extends FieldsListPlugin
{
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
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Joomla\CMS\Form\Form $form)
	{
		if (!$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form))
		{
			return $fieldNode;
		}

		$fieldNode->setAttribute('default', $field->fieldparams->get('default', ''));
		$fieldNode->setAttribute('class', 'chzn-color-state');
		$fieldNode->setAttribute('type', 'list');

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
	public function onContentPrepareForm(Joomla\CMS\Form\Form $form, $data)
	{
		// Make sure we are manipulating the right field.
		if (isset($data->type) && ($data->type == $this->_name))
		{
			$form->removeField('default_value');
		}
		
		return parent::onContentPrepareForm($form, $data);
	}

	/**
	 * Returns an array of key values to put in a list from the given field.
	 *
	 * @param   stdClass  $field  The field.
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public function getOptionsFromField($field)
	{
		return array(
			''  => '',
			'1' => $field->fieldparams->get('true', Text::_('JTRUE')),
			'0' => $field->fieldparams->get('false', Text::_('JFALSE'))
		);
	}
}
