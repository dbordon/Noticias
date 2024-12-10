<?php
/**
 * @package         Advanced Custom Fields
 * @version         2.0.0 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

extract($displayData);

if (defined('nrJ4'))
{
	// Include the Bootstrap component
	\JFactory::getApplication()
		->getDocument()
		->getWebAssetManager()
		->useScript('bootstrap.alert');
}
?>
<div class="nr-outdated-extension alert alert-warning alert-dismissible text-center" role="alert">
	<?php echo sprintf(JText::_('NR_OUTDATED_EXTENSION'), $extension, $days_old, '<a href="' . JURI::base() . 'index.php?option=com_installer&view=update" class="alert-link">', '</a>'); ?>
	<button type="button" class="close btn-close" data-<?php echo defined('nrJ4') ? 'bs-' : ''; ?>dismiss="alert" aria-label="Close"><?php echo !defined('nrJ4') ? '<span aria-hidden="true">&times;</span>' : ''; ?></button>
</div>