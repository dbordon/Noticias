<?php
/**
 * @package    JLSitemap Component
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  string $date Generation date.
 */

$stylesheet = Uri::getInstance()->toString(array('scheme', 'host', 'port')) .
    HTMLHelper::stylesheet(
        'com_jlsitemap/sitemap.min.css',
        array('version' => 'auto', 'relative' => true, 'pathOnly' => true)
    );
$sitename   = htmlspecialchars(Factory::getContainer()->get('config')->get('sitename'));
?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:template match="/">
        <html lang="<?php
        echo Factory::getApplication()->getLanguage()->getTag(); ?>">
        <head>
            <meta charset="UTF-8"/>
            <title><?php
                echo Text::sprintf('COM_JLSITEMAP_XSL_TITLE', $sitename); ?></title>
            <link rel="stylesheet" href="<?php
            echo $stylesheet; ?>"/>
        </head>
        <body>
        <div class="container">
            <h1>
                <?php
                echo Text::sprintf('COM_JLSITEMAP_XSL_TITLE', $sitename); ?>
            </h1>
            <p class="description">
                <?php
                echo Text::sprintf(
                    'COM_JLSITEMAP_XSL_DESCRIPTION_INDEX',
                    '<xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/>'
                ); ?>
            </p>
            <xsl:apply-templates/>
            <div class="center muted">
                <?php
                echo Text::_('COM_JLSITEMAP_XSL_COPYRIGHT'); ?>
            </div>
            <?php
            if ($date): ?>
                <div class="center muted">
                    <?php
                    echo $date; ?>
                </div>
            <?php
            endif; ?>
        </div>
        </body>
        </html>
    </xsl:template>
    <xsl:template match="sitemap:sitemapindex">
        <table>
            <thead>
            <tr>
                <th class="center" width="1%">#</th>
                <th><?php
                    echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_LINK'); ?></th>
                <th><?php
                    echo Text::_('COM_JLSITEMAP_SITEMAP_DEBUG_LAST_MODIFIED'); ?></th>
            </tr>
            </thead>
            <tbody>
            <xsl:for-each select="sitemap:sitemap">
                <xsl:variable name="loc">
                    <xsl:value-of select="sitemap:loc"/>
                </xsl:variable>
                <tr>
                    <td>
                        <xsl:value-of select="position()"/>
                    </td>
                    <td>
                        <div>
                            <a href="{$loc}">
                                <xsl:value-of select="sitemap:loc"/>
                            </a>
                        </div>
                        <xsl:if test="xhtml:link">
                            <div class="alternates">
                                <xsl:apply-templates select="xhtml:link"/>
                            </div>
                        </xsl:if>
                    </td>
                    <td class="nowrap">
                        <xsl:value-of select="sitemap:lastmod"/>
                    </td>
                </tr>
            </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>
