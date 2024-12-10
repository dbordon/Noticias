<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

use NRFramework\Conditions\Conditions\Component\ContentBase;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use NRFramework\Cache;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFArticles extends ACF_Field
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

        $contentAssignment = new ContentBase();

        foreach ($options as $option)
		{
			if (!$article = $contentAssignment->getItem($option->getLabel()))
			{
				continue;
			}

			$option->setLabel($article->title);
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

		$field_type = $field->fieldparams->get('articles_type', 'default');

		$fieldNode->setAttribute('multiple', true);

		

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

		$value = array_filter((array) $field->value);

		

		if (!$value)
		{
			return parent::onCustomFieldsPrepareField($context, $item, $field);
		}

		$cache_key = 'ACFArticles_Auto_' . $item->id . '_' . md5(implode(',', $value));
			
		if (Cache::has($cache_key))
		{
			$field->value = Cache::get($cache_key);
		}
		else
		{
			// Default articles
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.*')
				->from($db->quoteName('#__content', 'a'))
				->where($db->quoteName('a.id') . ' IN (' . implode(',', array_map('intval', $value)) . ')');
	
			$this->prepareArticles($context, $query, $field, $value);

			Cache::set($cache_key, $field->value);
		}

		return parent::onCustomFieldsPrepareField($context, $item, $field);
	}

	/**
	 * Prepares the articles.
	 * 
	 * @param   string    $context
	 * @param   object    $query
	 * @param   object    $field
	 * @param   array     $articles
	 * @param   bool      $all_filters
	 * 
	 * @return  void
	 */
	private function prepareArticles($context, $query, &$field, $articles, $all_filters = true)
	{
		$db = Factory::getDbo();

		// Filter results
        require_once 'fields/acfarticlesfilters.php';
		$payload = $all_filters ? $field->fieldparams : ['order' => $field->fieldparams->get('order')];
        $filters = new ACFArticlesFilters($query, $payload);
		$query = $filters->apply();

		// Set query
		$db->setQuery($query);

		// Get articles
		if (!$articles = $db->loadAssocList())
		{
			$field->value = [];
			return;
		}

		

		$field->value = $articles;
	}

	
}