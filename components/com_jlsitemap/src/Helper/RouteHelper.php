<?php
/**
 * @package    JLSitemap Component
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

namespace Joomla\Component\JLSitemap\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\RouteHelper as CMSRouteHelper;

class RouteHelper extends CMSRouteHelper
{
    /**
     * Fetches html route.
     *
     * @return  string  HTML view link.
     *
     * @since  1.6.0
     */
    public static function getHTMLRoute()
    {
        return 'index.php?option=com_jlsitemap&view=html&key=1';
    }
}