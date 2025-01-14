<?php
/**
 * @package    JLSitemap Component
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

namespace Joomla\Component\JLSitemap\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

class JLSitemapHelper extends ContentHelper
{
    /**
     * Configure the Linkbar.
     *
     * @param   string  $vName  The name of the active view.
     *
     * @return  void
     *
     * @since  0.0.1
     */
    static function addSubmenu($vName)
    {
        Sidebar::addEntry(
            Text::_('COM_JLSITEMAP_CONTROL_PANEL'),
            'index.php?option=com_jlsitemap&view=controlpanel',
            $vName == 'controlpanel'
        );

        Sidebar::addEntry(
            Text::_('COM_JLSITEMAP_GENERATION'),
            'index.php?option=com_jlsitemap&task=sitemap.generate',
            $vName == 'generation'
        );

        $filename = ComponentHelper::getParams('com_jlsitemap')->get('filename', 'sitemap');
        if (File::exists(JPATH_ROOT . '/' . $filename . '.xml')) {
            Sidebar::addEntry(
                Text::_('COM_JLSITEMAP_SITEMAP'),
                Uri::root() . 'sitemap.xml',
                $vName == 'sitemap'
            );

            Sidebar::addEntry(
                Text::_('COM_JLSITEMAP_SITEMAP_DELETE'),
                'index.php?option=com_jlsitemap&task=delete',
                $vName == 'delete'
            );
        }

        Sidebar::addEntry(
            Text::_('COM_JLSITEMAP_PLUGINS'),
            'index.php?option=com_plugins&filter[folder]=jlsitemap',
            $vName == 'plugins'
        );

        if ($cron = PluginHelper::getPlugin('system', 'jlsitemap_cron')) {
            Sidebar::addEntry(
                Text::_('COM_JLSITEMAP_CRON'),
                'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $cron->id,
                $vName == 'cron'
            );
        }

        Sidebar::addEntry(
            Text::_('COM_JLSITEMAP_CONFIG'),
            'index.php?option=com_config&view=component&component=com_jlsitemap',
            $vName == 'config'
        );
    }
}