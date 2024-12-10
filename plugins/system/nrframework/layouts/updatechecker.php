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

?>

<div class="nr-updatechecker">
    <div class="nr-wrap">
        <div class="nruc_header">
            <div class="nruc_title">
                <?php echo JText::sprintf('NR_EXTENSION_NEW_VERSION_IS_AVAILABLE', $title . ' ' . $version_latest) ?>
            </div>
            <div class="nruc_subtitle">
                <?php echo JText::sprintf('NR_YOU_ARE_USING_EXTENSION', $title, $version_installed) ?>
                <a href="<?php echo $product_url ?>/changelog" target="_blank">
                    <?php echo JText::_('NR_CHANGELOG'); ?>
                </a>
            </div>
        </div>
        <div class="nruc_toolbar">
            <a class="btn btn-success" href="<?php echo JURI::base() ?>index.php?option=com_installer&view=update">
                <span class="icon-download"></span>
                <?php echo JText::_('NR_UPDATE'); ?>
            </a>
            <?php if (!$ispro) { ?>
                <a target="_blank" href="<?php echo $upgradeurl ?>" class="btn btn-danger" title="<?php echo strip_tags(JText::sprintf('NR_PROFEATURE_DISCOUNT', $title)) ?>">
                    <span class="icon-heart"></span>
                    <?php echo JText::_('NR_UPGRADE_TO_PRO') ?>
                </a>
            <?php } ?>
        </div>
    </div>
</div>