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

defined('_JEXEC') or die('Restricted access');

require_once JPATH_PLUGINS . '/system/nrframework/helpers/fieldlist.php';

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldACFArticles extends NRFormFieldList
{
    public function getInput()
    {
        $this->hint = $this->getFieldHint();

        return parent::getInput();
    }

    private function getFieldHint()
    {
        $hint = (string) $this->element['hint'];
        return !empty($hint) ? $hint : Text::_('ACF_ARTICLES_SELECT_ARTICLES');
    }
    
    /**
     * Method to get a list of options for a list input.
     *
     * @return  array
	 */
	protected function getOptions()
	{
        require_once 'acfarticlesfilters.php';
        
        // The layout param in the field XML overrides $this->layout and thus we need to set it again.
        $this->layout = 'joomla.form.field.list-fancy-select';

        $query = $this->db->getQuery(true)
            ->select('a.*, c.lft as category_lft, c.title as category_title')
            ->from($this->db->quoteName('#__content', 'a'))
            ->join('LEFT', $this->db->quoteName('#__categories', 'c') . ' ON c.id = a.catid');

        // Get current item id and exclude it from the list
        if ($current_item_id = Factory::getApplication()->input->getInt('id'))
        {
            $query->where($this->db->quoteName('a.id') . ' != ' . (int) $current_item_id);
        }

        $field_attributes = (array) $this->element->attributes();

        $payload = new Registry($field_attributes['@attributes']);
        
        // Apply filters
        $filters = new ACFArticlesFilters($query, $payload);
        $query = $filters->apply();

        $this->db->setQuery($query);

        // Get all articles
        if (!$items = $this->db->loadObjectList())
        {
            return;
        }

        // Get all dropdown choices
        $options = [];

        // Add hint to single value field
        if ($payload->get('max_articles') == '1')
        {
            $options[] = HTMLHelper::_('select.option', '', $this->getFieldHint());
        }
        
        foreach ($items as $item)
        {
            $options[] = HTMLHelper::_('select.option', $item->id, $item->title);
        }

        return $options;
    }

    /**
     * Return all categories child ids.
     * 
     * @param   array  $categories
     * 
     * @return  array
     */
    private function getCategoriesChildIds($categories = [])
    {
        $query = $this->db->getQuery(true)
            ->select('a.id')
            ->from($this->db->quoteName('#__categories', 'a'))
            ->where('a.extension = ' . $this->db->quote('com_content'))
            ->where('a.published = 1');

        $children = [];

        while (!empty($categories))
        {
            $query
                ->clear('where')
                ->where($this->db->quoteName('a.parent_id') . ' IN (' . implode(',', $categories) . ')');
            
            $this->db->setQuery($query);

            $categories = $this->db->loadColumn();

            $children = array_merge($children, $categories);
        }

        return $children;
    }
}