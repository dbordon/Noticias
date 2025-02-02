<?php
/**
 * @package    JLSitemap Component
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

if (!$displayData) {
    return;
}

$result   = $displayData;
$includes = (is_array($result->includes)) ? $result->includes : [];
$excludes = (is_array($result->excludes)) ? $result->excludes : [];

$multilanguage = Multilanguage::isEnabled();
?>
<!DOCTYPE html>
<html lang="<?php
echo Factory::getApplication()->getLanguage()->getTag(); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php
        echo Text::_('COM_JLSITEMAP') . ': ' . Text::_('COM_JLSITEMAP_SITEMAP_DEBUG'); ?></title>
    <link rel="stylesheet" href="<?php
    echo HTMLHelper::stylesheet(
        'com_jlsitemap/sitemap.min.css',
        ['version' => 'auto', 'relative' => true, 'pathOnly' => true]
    ); ?>"/>
    <style>
        #excludes {
            margin-top: 30px;
        }

        #excludes .reason {
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?php
        echo Text::_('COM_JLSITEMAP') . ': ' . Text::_('COM_JLSITEMAP_SITEMAP_DEBUG'); ?></h1>
    <div class="description">
        <div>
            <?php
            echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_DESCRIPTION'); ?>
        </div>
        <div>
            <?php
            echo Text::sprintf(
                'COM_JLSITEMAP_SITEMAP_DEBUG_INCLUDES',
                '<a href="#includes">' . count($includes) . '</a>'
            ); ?>
        </div>
        <div>
            <?php
            echo Text::sprintf(
                'COM_JLSITEMAP_SITEMAP_DEBUG_EXCLUDES',
                '<a href="#excludes">' . count($excludes) . '</a>'
            ); ?>
        </div>
    </div>
    <div id="includes">
        <h2><?php
            echo Text::sprintf('COM_JLSITEMAP_SITEMAP_DEBUG_INCLUDES', count($includes)); ?></h2>
        <?php
        if (!empty($includes)): ?>
            <table>
                <thead>
                <tr>
                    <th class="center" width="1%">#</th>
                    <th><?php
                        echo Text::_('JGLOBAL_TITLE'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_TYPE'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_LINK'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_CHANGEFREQ'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_PRIORITY'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_LAST_MODIFIED'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                foreach ($includes as $include):?>
                    <tr>
                        <td><?php
                            echo $i; ?></td>
                        <td><?php
                            echo $include->get('title', ''); ?></td>
                        <td class="nowrap"><?php
                            echo implode('<br>', $include->get('type', [])); ?></td>
                        <td>
                            <div>
                                <?php
                                if ($include->get('link', false)): ?>
                                    <a href="<?php
                                    echo $include->get('loc', '/'); ?>" target="_blank">
                                        <?php
                                        echo $include->get('link', '/'); ?>
                                    </a>
                                <?php
                                endif; ?>
                            </div>
                            <div class="additions">
                                <?php
                                if ($multilanguage && is_array($include->get('alternates', false))): ?>
                                    <?php
                                    foreach ($include->get('alternates') as $lang => $loc): ?>
                                        <div class="item">
                                            <a href="<?php
                                            echo $loc; ?>" class="alternate" target="_blank">
                                                <?php
                                                echo $lang; ?>
                                            </a>
                                        </div>
                                    <?php
                                    endforeach; ?>
                                <?php
                                endif; ?>
                                <?php
                                if (is_array($include->get('images', false))): ?>
                                    <?php
                                    foreach ($include->get('images') as $i => $src): ?>
                                        <div class="item">
                                            <a href="<?php
                                            echo $src; ?>" title="<?php
                                            echo $src; ?>" class="image"
                                               target="_blank">
                                                <svg width="20" height="20" viewBox="0 0 20 20"
                                                     xmlns="http://www.w3.org/2000/svg" data-svg="image">
                                                    <rect fill="none" stroke="#1e87f0" x=".5" y="2.5" width="19"
                                                          height="15"></rect>
                                                    <polyline fill="none" stroke="#1e87f0" stroke-width="1.01"
                                                              points="4,13 8,9 13,14"></polyline>
                                                    <polyline fill="none" stroke="#1e87f0" stroke-width="1.01"
                                                              points="11,12 12.5,10.5 16,14"></polyline>
                                                </svg>
                                            </a>
                                        </div>
                                    <?php
                                    endforeach; ?>
                                <?php
                                endif; ?>
                            </div>
                        </td>
                        <td><?php
                            echo $include->get('changefreq', ''); ?></td>
                        <td><?php
                            echo $include->get('priority', ''); ?></td>
                        <td class="nowrap">
                            <?php
                            echo (!$lastmod = $include->get('lastmod', '')) ? ''
                                : HTMLHelper::_('date', $lastmod, Text::_('DATE_FORMAT_LC6')); ?>
                        </td>
                    </tr>
                    <?php
                    $i++;
                endforeach; ?>
                </tbody>
            </table>
        <?php
        endif; ?>
    </div>
    <div id="excludes">
        <h2><?php
            echo Text::sprintf('COM_JLSITEMAP_SITEMAP_DEBUG_EXCLUDES', count($excludes)); ?></h2>
        <?php
        if (!empty($excludes)): ?>
            <table>
                <thead>
                <tr>
                    <th class="center" width="1%">#</th>
                    <th><?php
                        echo Text::_('JGLOBAL_TITLE'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_TYPE'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_LINK'); ?></th>
                    <th><?php
                        echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_EXCLUDE'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                foreach ($excludes as $exclude): ?>
                    <tr>
                        <td><?php
                            echo $i; ?></td>
                        <td><?php
                            echo $exclude->get('title', ''); ?></td>
                        <td class="nowrap"><?php
                            echo implode('<br>', $exclude->get('type', [])); ?></td>
                        <td>
                            <?php
                            if ($exclude->get('link', false)): ?>
                                <a href="<?php
                                echo $exclude->get('loc', '/'); ?>" target="_blank">
                                    <?php
                                    echo $exclude->get('link', '/'); ?>
                                </a>
                            <?php
                            endif; ?>
                        </td>
                        <td>
                            <?php
                            foreach ($exclude->get('exclude', []) as $reason): ?>
                                <div class="reason">
                                    <?php
                                    if ($reason->get('type')): ?>
                                        <div>
                                            <strong>
                                                <?php
                                                echo $reason->get('type'); ?>
                                            </strong>
                                        </div>
                                    <?php
                                    endif; ?>
                                    <div>
                                        <?php
                                        echo $reason->get('msg'); ?>
                                    </div>
                                </div>
                            <?php
                            endforeach; ?>
                        </td>
                    </tr>
                    <?php
                    $i++;
                endforeach; ?>
                </tbody>
            </table>
        <?php
        endif; ?>
    </div>
</div>
</body>
</html>