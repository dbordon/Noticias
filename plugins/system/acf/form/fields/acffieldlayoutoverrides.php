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

use Joomla\CMS\Form\Field\ListField;
use \NRFramework\Widgets\Helper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldACFFieldLayoutOverrides extends ListField
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return    array   An array of options.
     */
    protected function getOptions()
    {
        $layouts = [
            'default' => 'JOPTION_USE_DEFAULT'
        ];

		$field_type = (string) $this->element['field_type'];
		$field_widget_name = (string) $this->element['widget_name'];
        $override_widget_directory_name = $this->getOverrideWidgetDirectoryName($field_widget_name, $field_type);
		if ($layout_overrides = Helper::getLayoutOverrides($override_widget_directory_name))
		{
			$layouts = array_merge($layouts, $layout_overrides);
		}

        foreach ($layouts as $key => $title)
        {
            $options[] = HTMLHelper::_('select.option', $key, Text::_($title));
        }   

        return $options;
    }

	public function getInput()
	{
		$field_type = (string) $this->element['field_type'];
		$field_widget_name = (string) $this->element['widget_name'];
        $override_widget_directory_name = $this->getOverrideWidgetDirectoryName($field_widget_name, $field_type);

        if (!$override_widget_directory_name)
        {
            return Text::_('ACF_OVERRIDE_WIDGET_LAYOUT_PLEASE_SAVE_FIRST');
        }

		$original_path = implode(DIRECTORY_SEPARATOR, [JPATH_PLUGINS, 'system', 'nrframework', 'layouts', 'widgets', $override_widget_directory_name]). '/default.php';

		$new_path = Helper::getLayoutOverridePath($override_widget_directory_name) . '/LAYOUTNAME.php';
		
		$note = '<div class="acf-field-setting-note" style="padding-top:5px;">' . sprintf(Text::_('ACF_OVERRIDE_WIDGET_BASED_FIELD_LAYOUT_DESC'), Text::_('PLG_FIELDS_ACF' . $this->getHelpName($field_widget_name, $field_type) . '_LABEL'), $original_path, $new_path) . '</div>';
		
		return parent::getInput() . $note;
	}

    /**
     * Some widgets require a different directory to be used for layout overrides.
     * 
     * @param   string  $field_widget_name  The widget name
     * @param   string  $field_type         The field type
     * 
     * @return  string
     */
    private function getOverrideWidgetDirectoryName($field_widget_name = null, $field_type = null)
    {
        if (!$field_widget_name || !$field_type)
        {
            return;
        }

        switch ($field_type)
        {
            case 'acfvideo':
                $field_widget_name = strtolower($this->form->getData()->get('fieldparams.provider', ''));
                break;
            case 'acfgallery':
                if ($this->form->getData()->get('fieldparams.style', '') === 'slideshow')
                {
                    $field_widget_name = 'slideshow';
                }
                break;
        }
        
        return strtolower($field_widget_name);
    }

    private function getHelpName($field_widget_name = null, $field_type = null)
    {
        if (!$field_widget_name || !$field_type)
        {
            return;
        }

        switch ($field_type)
        {
            case 'acfaddress':
                $field_widget_name = 'Address';
                break;
            case 'acfmap':
                $field_widget_name = 'Map';
                break;
        }
        
        return strtoupper($field_widget_name);
    }
}