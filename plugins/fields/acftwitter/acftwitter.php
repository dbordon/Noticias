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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::register('ACF_Field', JPATH_PLUGINS . '/system/acf/helper/plugin.php');

if (!class_exists('ACF_Field'))
{
	Factory::getApplication()->enqueueMessage('Advanced Custom Fields System Plugin is missing', 'error');
	return;
}

class PlgFieldsACFTwitter extends ACF_Field
{
	/**
	 *  Field's Class
	 *
	 *  @var  string
	 */
	protected $class = 'input-xlarge w-100';
	
	/**
	 *  Field's Hint Description
	 *
	 *  @var  string
	 */
	protected $hint = 'ACF_TWITTER_TWTR_HANDLE';

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

        foreach ($options as $option)
		{
			$option->setLabel('@' . $option->getLabel());
		}

        return $options;
	}
}
