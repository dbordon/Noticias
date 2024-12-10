<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace ACF;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class Item
{
	/**
	 * Determines whether we're copying an item.
	 * 
	 * @return  bool
	 */
	public static function isCopying()
	{
		return self::isBatchCopying() || self::isSavingAsCopy();
	}
	
	/**
	 * Determines whether we're batch copying an item.
	 * 
	 * @return  bool
	 */
	public static function isBatchCopying()
	{
		$input = Factory::getApplication()->input;
		$task = $input->getCmd('task', '');
		$batchOptions = $input->post->get('batch', [], 'array');
		$cid = $input->post->get('cid', [], 'array');
		$isCopy = isset($batchOptions['move_copy']) && $batchOptions['move_copy'] === 'c';

		return $task === 'batch' && is_array($cid) && count($cid) && $isCopy;
	}

	/**
	 * Determines whether we're copying an item using the "Save as Copy" button.
	 * 
	 * @return  bool
	 */
	public static function isSavingAsCopy()
	{
		$input = Factory::getApplication()->input;
		$task = $input->getCmd('task', '');
		$id = $input->getInt('id', 0);
		$isCopying = $task === 'save2copy' && $id;

		return $isCopying;
	}
}