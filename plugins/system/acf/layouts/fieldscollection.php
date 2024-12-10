<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!defined('nrJ4'))
{
	HTMLHelper::_('behavior.modal');
}

$modalRel = 'rel="{handler: \'iframe\', size: {x: 1000, y: 630}}"';
$product_url = 'https://www.tassos.gr/joomla-extensions/advanced-custom-fields';

?>

<div class="acft">
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?php echo Text::_('ACF_FIELDS_COLLECTION') ?></th>
				<th width="140px"></th>
			</tr>
		</thead>
	    <?php foreach ($displayData as $key => $field) { ?>
			<tr>
	            <td class="acft-text">
	                <h4><?php echo $field['label'] ?></h4>
	                <?php echo $field['description'] ?>
	            </td>
	            <td class="acft-btn">
					<div class="acft-btn">
						<?php if ($field['comingsoon']) { ?>
							<a class="btn" href="<?php echo $product_url ?>/roadmap" target="_blank">
								On the roadmap
							</a>
						<?php } ?>
						
						
						<?php 
							if ($field['proonly'])
							{ 
								NRFramework\HTML::renderProButton($field['label'] . ' Field');
							}
						?>
						

						<?php if ($field["extensionid"]) { ?>
							<a class="btn <?php echo !defined('nrJ4') ? 'modal' : '' ?> btn-outline-secondary btn-sm" <?php echo $modalRel; ?> href="<?php echo $field['backendurl'] ?>">
								<span class="icon-options"></span>
							</a>
						<?php } ?>

						<a class="btn btn-outline-secondary btn-sm" href="<?php echo $field['docurl'] ?>" target="_blank">
							<span class="icon-help"></span>
						</a>
					</div>
	            </td>
	        </tr>
	    <?php } ?>
		<tr>
        	<td>
                <div><strong><?php echo Text::_("ACF_MISSING_FIELD") ?></strong></div>
                <?php echo Text::_("ACF_MISSING_FIELD_DESC") ?>
        	</td>
			<td class="acft-btn">
				<a class="btn btn-primary" target="_blank" href="https://www.tassos.gr/contact?extension=Advanced Custom Fields">
					<?php echo Text::_("NR_DROP_EMAIL")?>
				</a>
			</td>
        </tr>
	</table>
</div>

<style>
	.acft-btn {
	    text-align: right !important;
	    white-space: nowrap;
	}
	.acft td, .acft th {
		padding:13px;
		vertical-align: middle;
	}
	.acft h4 {
		margin:0 0 5px 0;
		padding:0;
		color:#3071a9;
	}	
</style>