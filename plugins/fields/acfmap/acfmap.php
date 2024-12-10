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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFMap extends ACF_Field
{
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

		$fieldNode->setAttribute('filter', 'raw');

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
		// When editing a new and unsaved field the $data variable is passed as an array for a reason.
		$data = is_array($data) ? (object) $data : $data;

		// Make sure we are manipulating the right field.
		if (!isset($data->type) || $data->type != $this->_name)
		{
			return;
		}

		// Get provider
		$provider = isset($data->fieldparams['provider']) ? $data->fieldparams['provider'] : false;

		// Abort if no provider is set
		if (!$provider)
		{
			return parent::onContentPrepareForm($form, $data);
		}

		// For OSM, we only check its key when we want to use satellite
		if ($provider === 'OpenStreetMap')
		{
			$osm_maptype = isset($data->fieldparams['openstreetmap_maptype']) ? $data->fieldparams['openstreetmap_maptype'] : 'road';
			if ($osm_maptype === 'road')
			{
				return parent::onContentPrepareForm($form, $data);
			}
		}
		
		$key = $this->params->get(strtolower($provider) . '_key');

		// Display a warning message to set the API key if empty
		if (empty($key))
		{
			$extensionID = NRFramework\Functions::getExtensionID('acfmap', 'fields');
			$backEndURL  = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extensionID;
			$url = Uri::base() . $backEndURL;

			$provider_name = $provider;
			
			switch (strtolower($provider))
			{
				case 'bingmap':
					$provider_name = 'Bing Maps';
					break;
				case 'googlemap':
					$provider_name = 'Google Maps';
					break;
			}

			Factory::getApplication()->enqueueMessage(Text::sprintf('ACF_MAP_API_KEY_WARNING', $provider_name, $url), 'warning');
		}

		return parent::onContentPrepareForm($form, $data);
	}
}
