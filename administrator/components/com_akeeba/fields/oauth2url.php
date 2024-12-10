<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Form\FormHelper;

if (class_exists('JFormFieldNote'))
{
	return;
}

FormHelper::loadFieldClass('note');

class JFormFieldOauth2url extends JFormFieldNote
{
	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$engine = $this->element['engine'] ?? 'example';
		$uri = rtrim(\JUri::base() ,'/');

		if (substr($uri, -14) === '/administrator')
		{
			$uri = substr($uri, 0, -14);
		}

		$text1 = JText::_('COM_AKEEBA_CONFIG_OAUTH2URLFIELD_YOU_WILL_NEED');
		$text2 = JText::_('COM_AKEEBA_CONFIG_OAUTH2URLFIELD_CALLBACK_URL');
		$text3 = JText::_('COM_AKEEBA_CONFIG_OAUTH2URLFIELD_HELPER_URL');
		$text4 = JText::_('COM_AKEEBA_CONFIG_OAUTH2URLFIELD_REFRESH_URL');

		return <<< HTML
<div class="alert alert-info mx-2 my-2">
	<p>
		$text1
	</p>
	<p>
		<strong>$text2</strong>:
		<br/>
		<code>$uri/index.php?option=com_akeeba&view=oauth2&task=step2&format=raw&engine={$engine}</code>
	</p>
	<p>
		<strong>$text3</strong>:
		<br/>
		<code>$uri/index.php?option=com_akeeba&view=oauth2&task=step1&format=raw&engine={$engine}</code>
	</p>
	<p>
		<strong>$text4</strong>:
		<br/>
		<code>$uri/index.php?option=com_akeeba&view=oauth2&task=refresh&format=raw&engine={$engine}</code>
	</p>
</div>
HTML;

	}
}
