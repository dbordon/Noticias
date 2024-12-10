<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            http://www.tassos.gr
 *  @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework;

defined('_JEXEC') or die;

use NRFramework\Extension;

class ConditionBuilder
{    
    public static function render($id, $loadData = array(), $conditions_list = array())
    {
        // Condition Builder relies on com_ajax for AJAX requests.
        if (!Extension::componentIsEnabled('ajax'))
        {
            \JFactory::getApplication()->enqueueMessage(\JText::_('AJAX Component is not enabled.'), 'error');
        }

        // Initialize a new empty condition
        if (empty($loadData))
        {
            $loadData = [0 => ['']];
        } else 
        {
            // Fix indexes
            $loadData = array_values($loadData);
        }

        $options = [
            'id' => $id,
            'data' => $loadData,
            'conditions_list' => $conditions_list,
            'maxIndex' => count($loadData) - 1
        ];

        return self::getLayout('conditionbuilder', $options);
    }
   
    public static function add($controlGroup, $groupKey, $conditionKey, $condition = null, $conditions_list = array())
    {
        $controlGroup_ = $controlGroup . "[$groupKey][$conditionKey]";
        $form = self::getForm('/conditionbuilder/base.xml', $controlGroup_, $condition);
        $form->setFieldAttribute('name', 'conditions_list', is_array($conditions_list) ? implode(',', $conditions_list) : $conditions_list);

        $options = [
            'toolbar'      => $form,
            'conditionKey' => $conditionKey,
            'options'      => ''
        ];

        if (isset($condition['name']))
        {
            $optionsHTML = self::renderOptions($condition['name'], $controlGroup_, $condition);
            $options['options'] = $optionsHTML;
        }

        return self::getLayout('conditionbuilder_row', $options);
    }

    public static function renderOptions($name, $controlGroup = null, $formData = null)
    {
        $form = self::getForm('/conditions/' . $name . '.xml', $controlGroup, $formData);
        return $form->renderFieldset('general');
    }

    private static function getLayout($name, $data)
    {
        $layout = new \JLayoutFile($name, JPATH_PLUGINS . '/system/nrframework/layouts');
        return $layout->render($data);
    }

    private static function getForm($name, $controlGroup, $data = null)
    {
        $form = new \JForm('cb', ['control' => $controlGroup]);

        $form->addFieldPath(JPATH_PLUGINS . '/system/nrframework/fields');
        $form->loadFile(JPATH_PLUGINS . '/system/nrframework/xml/' . $name);

        if (!is_null($data))
        {
            $form->bind($data);
        }

        return $form;
    }
}