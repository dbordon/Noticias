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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldACFArticlesFields extends NRFormFieldList
{
    public function getInput()
    {
        $this->hint = Text::_('ACF_ARTICLES_SELECT_ACF_ARTICLES');

        return parent::getInput();
    }
    
    /**
     * Method to get a list of options for a list input.
     *
     * @return  array
	 */
	protected function getOptions()
	{
        // The layout param in the field XML overrides $this->layout and thus we need to set it again.
        $this->layout = 'joomla.form.field.list-fancy-select';

        $query = $this->db->getQuery(true)
            ->select('id, label')
            ->from($this->db->quoteName('#__fields'))
            ->where('type = ' . $this->db->quote('acfarticles'))
            ->where('state = 1');

        // Get current item id and exclude it from the list
        if ($current_item_id = Factory::getApplication()->input->getInt('id'))
        {
            $query->where($this->db->quoteName('id') . ' != ' . (int) $current_item_id);
        }

        $this->db->setQuery($query);

        // Get all fields
        if (!$items = $this->db->loadObjectList())
        {
            return;
        }

        // Get all dropdown choices
        $options = [];

        foreach ($items as $item)
        {
            $options[] = HTMLHelper::_('select.option', $item->id, $item->label);
        }

        return $options;
    }
}