<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.8 Free
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

extract($displayData);
?>
<div class="tf-gallery-media-library-top-note">
    <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 -960 960 960" fill="currentColor"><path d="M454-298h52v-230h-52v230Zm25.79-290.46q11.94 0 20.23-8.08 8.29-8.08 8.29-20.02t-8.08-20.23q-8.08-8.28-20.02-8.28T459.98-637q-8.29 8.08-8.29 20.02t8.08 20.23q8.08 8.29 20.02 8.29Zm.55 472.46q-75.11 0-141.48-28.42-66.37-28.42-116.18-78.21-49.81-49.79-78.25-116.09Q116-405.01 116-480.39q0-75.38 28.42-141.25t78.21-115.68q49.79-49.81 116.09-78.25Q405.01-844 480.39-844q75.38 0 141.25 28.42t115.68 78.21q49.81 49.79 78.25 115.85Q844-555.45 844-480.34q0 75.11-28.42 141.48-28.42 66.37-78.21 116.18-49.79 49.81-115.85 78.25Q555.45-116 480.34-116Zm-.34-52q130 0 221-91t91-221q0-130-91-221t-221-91q-130 0-221 91t-91 221q0 130 91 221t221 91Zm0-312Z"/></svg>
    <?php echo Text::_('NR_GALLERY_MANAGER_MEDIA_TOP_NOTE'); ?>
</div>
<iframe class="iframe" src="<?php echo Uri::root() . $gallery_manager_path; ?>?option=com_media&view=media&tmpl=component" name="Select Gallery Item"></iframe>