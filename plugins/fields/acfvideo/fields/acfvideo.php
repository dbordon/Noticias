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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class JFormFieldACFVideo extends TextField
{
    /**
	 * Renders the input field with the video previewer.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$provider = (string) $this->element['provider'];
		
		$xml = '
			<field name="' . $this->fieldname . '"
				type="TFVideoInput"
				provider="' . ($provider) . '"
				previewer_enabled="' . ($this->isPreviewerEnabled() ? 'true' : 'false') . '"
			/>
		';

		$this->form->setField(new SimpleXMLElement($xml));
		$field = $this->form->getField($this->fieldname, null, $this->value);
		$field->name = $this->name;
		$field->id = $this->id;
		
		return $field->getInput();
	}

	private function isPreviewerEnabled()
	{
		$plugin = PluginHelper::getPlugin('fields', 'acfvideo');
		$params = new Registry($plugin->params);

		return $params->get('enable_previewer', '1') === '1';
	}
}
