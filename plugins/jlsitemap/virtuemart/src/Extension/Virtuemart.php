<?php
/**
 * @package    JLSitemap - VirtueMart Plugin
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

namespace Joomla\Plugin\JLSitemap\Virtuemart\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

final class Virtuemart extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var  boolean
     *
     * @since  1.6.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetUrls' => 'onGetUrls',
        ];
    }

    /**
     * Method to get urls array
     *
     * @param   Event  $event
     *
     *
     * @since 1.6.0
     */
    public function onGetUrls(Event $event): void
    {
        if(!ComponentHelper::isEnabled('com_virtuemart'))
        {
            return;
        }

        /**
         * @param   array     $urls    Urls array
         * @param   Registry  $config  Component config
         */
        [$urls, $config] = $event->getArguments();

        if (!$this->params->get('products_enable')
            && !$this->params->get('categories_enable')
            && !$this->params->get('manufacturers_enable')
            && !$this->params->get('vendors_enable')) {
            $event->setArgument(0, $urls);
        }

        // Add config
        \JLoader::register('\VmConfig', JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');

        $db = $this->getDatabase();

        // Get default language
        if ($defaultLanguage = \VmConfig::get('vmDefLang')) {
            $defaultLanguageKey = str_replace('-', '_', strtolower($defaultLanguage));
        } else {
            $defaultLanguage    = $this->getApplication()->getLanguage()->getTag();
            $defaultLanguageKey = str_replace('-', '_', strtolower($defaultLanguage));;
        }
        $languages = (!empty($defaultLanguage) && !empty($defaultLanguageKey)) ?
            [$defaultLanguageKey => $defaultLanguage] : [];

        // Get other languages
        $activeLanguages = \VmConfig::get('active_languages');
        $multilanguage   = ($config->get('multilanguage') && !empty($activeLanguages));
        if ($multilanguage) {
            foreach ($activeLanguages as $language) {
                $key = str_replace('-', '_', strtolower($language));
                if (!empty($key) && !empty($language)) {
                    $languages[$key] = $language;
                }
            }
        }

        // Check languages
        if (empty($languages)) {
            $event->setArgument(0, $urls);
        }

        // Get products
        if ($this->params->get('products_enable')) {
            $query = $db->getQuery(true)
                ->select([
                    'p.virtuemart_product_id as id',
                    'p.published',
                    'p.metarobot',
                    'c.virtuemart_category_id as catid',
                    'product_canon_category_id as canon_catid',
                ])
                ->leftJoin(
                    $db->quoteName('#__virtuemart_product_categories', 'c')
                    . '  ON c.virtuemart_product_id = p.virtuemart_product_id'
                )
                ->from($db->quoteName('#__virtuemart_products', 'p'))
                ->group('p.virtuemart_product_id');

            foreach ($languages as $key => $code) {
                $query->select([$key . '.product_name as ' . 'product_name_' . $key])
                    ->leftJoin(
                        $db->quoteName('#__virtuemart_products_' . $key, $key)
                        . '  ON ' . $key . '.virtuemart_product_id = p.virtuemart_product_id'
                    );
            }

            $rows       = $db->setQuery($query)->loadObjectList();
            $changefreq = $this->params->get('products_changefreq', $config->get('changefreq', 'weekly'));
            $priority   = $this->params->get('products_priority', $config->get('priority', '0.5'));

            foreach ($rows as $row) {
                // Prepare default title
                $selector     = 'product_name_' . $defaultLanguageKey;
                $defaultTitle = $row->$selector;

                // Prepare catid
                $catid = (!empty($row->canon_catid)) ? $row->canon_catid : $row->catid;

                // Prepare default loc attribute
                $defaultLoc = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='
                    . $row->id . '&virtuemart_category_id=' . $catid;

                // Prepare exclude attribute
                $exclude = [];
                if (preg_match('/noindex/', $row->metarobot)) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_PRODUCT'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_PRODUCT_ROBOTS'),
                    ];
                }
                if (!$row->published) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_PRODUCT'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_PRODUCT_UNPUBLISH'),
                    ];
                }

                foreach ($languages as $key => $code) {
                    $selector = 'product_name_' . $key;
                    $title    = (!empty($row->$selector)) ? $row->$selector : $defaultTitle;

                    $loc = $defaultLoc;
                    if ($multilanguage) {
                        $loc .= '&lang=' . $code;
                    }

                    // Prepare product object
                    $product             = new \stdClass();
                    $product->type       = Text::_('PLG_JLSITEMAP_VIRTUEMART_TYPES_PRODUCT');
                    $product->title      = $title;
                    $product->loc        = $loc;
                    $product->changefreq = $changefreq;
                    $product->priority   = $priority;
                    $product->exclude    = (!empty($exclude)) ? $exclude : false;
                    $product->alternates = ($multilanguage) ? [] : false;
                    if ($multilanguage) {
                        foreach ($languages as $alternate) {
                            $product->alternates[$alternate] = $defaultLoc . '&lang=' . $alternate;
                        }
                    }

                    // Add product to urls
                    $urls[] = $product;
                }
            }
        }

        // Get categories
        if ($this->params->get('categories_enable')) {
            $query = $db->getQuery(true)
                ->select(['c.virtuemart_category_id as id', 'c.published', 'c.metarobot'])
                ->from($db->quoteName('#__virtuemart_categories', 'c'));

            foreach ($languages as $key => $code) {
                $query->select([$key . '.category_name as ' . 'category_name_' . $key])
                    ->leftJoin(
                        $db->quoteName('#__virtuemart_categories_' . $key, $key)
                        . '  ON ' . $key . '.virtuemart_category_id = c.virtuemart_category_id'
                    );
            }

            $rows       = $db->setQuery($query)->loadObjectList();
            $changefreq = $this->params->get('categories_changefreq', $config->get('changefreq', 'weekly'));
            $priority   = $this->params->get('categories_priority', $config->get('priority', '0.5'));

            foreach ($rows as $row) {
                // Prepare default title
                $selector     = 'category_name_' . $defaultLanguageKey;
                $defaultTitle = $row->$selector;

                // Prepare default loc attribute
                $defaultLoc = 'index.php?option=com_virtuemart&view=category&virtuemart_manufacturer_id=0&Itemid=0&virtuemart_category_id='
                    . $row->id;

                // Prepare exclude attribute
                $exclude = [];
                if (preg_match('/noindex/', $row->metarobot)) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_CATEGORY'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_CATEGORY_ROBOTS'),
                    ];
                }
                if (!$row->published) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_CATEGORY'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_CATEGORY_UNPUBLISH'),
                    ];
                }

                foreach ($languages as $key => $code) {
                    $selector = 'category_name_' . $key;
                    $title    = (!empty($row->$selector)) ? $row->$selector : $defaultTitle;

                    $loc = $defaultLoc;
                    if ($multilanguage) {
                        $loc .= '&lang=' . $code;
                    }

                    // Prepare category object
                    $category             = new \stdClass();
                    $category->type       = Text::_('PLG_JLSITEMAP_VIRTUEMART_TYPES_CATEGORY');
                    $category->title      = $title;
                    $category->loc        = $loc;
                    $category->changefreq = $changefreq;
                    $category->priority   = $priority;
                    $category->exclude    = (!empty($exclude)) ? $exclude : false;
                    $category->alternates = ($multilanguage) ? [] : false;
                    if ($multilanguage) {
                        foreach ($languages as $alternate) {
                            $category->alternates[$alternate] = $defaultLoc . '&lang=' . $alternate;
                        }
                    }

                    // Add category to urls
                    $urls[] = $category;
                }
            }
        }

        // Get manufacturers
        if ($this->params->get('manufacturers_enable')) {
            $query = $db->getQuery(true)
                ->select(['m.virtuemart_manufacturer_id as id', 'm.published', 'm.metarobot'])
                ->from($db->quoteName('#__virtuemart_manufacturers', 'm'));

            foreach ($languages as $key => $code) {
                $query->select([$key . '.mf_name as ' . 'manufacturer_name_' . $key])
                    ->leftJoin(
                        $db->quoteName('#__virtuemart_manufacturers_' . $key, $key)
                        . '  ON ' . $key . '.virtuemart_manufacturer_id = m.virtuemart_manufacturer_id'
                    );
            }

            $rows       = $db->setQuery($query)->loadObjectList();
            $changefreq = $this->params->get('manufacturers_changefreq', $config->get('changefreq', 'weekly'));
            $priority   = $this->params->get('manufacturers_priority', $config->get('priority', '0.5'));

            foreach ($rows as $row) {
                // Prepare default title
                $selector     = 'manufacturer_name_' . $defaultLanguageKey;
                $defaultTitle = $row->$selector;

                // Prepare default loc attribute
                $defaultLoc = 'index.php?option=com_virtuemart&view=manufacturer&layout=details&Itemid=0&virtuemart_manufacturer_id='
                    . $row->id;

                // Prepare exclude attribute
                $exclude = [];
                if (preg_match('/noindex/', $row->metarobot)) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_MANUFACTURER'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_MANUFACTURER_ROBOTS'),
                    ];
                }
                if (!$row->published) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_MANUFACTURER'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_MANUFACTURER_UNPUBLISH'),
                    ];
                }

                foreach ($languages as $key => $code) {
                    $selector = 'category_name_' . $key;
                    $title    = (!empty($row->$selector)) ? $row->$selector : $defaultTitle;

                    $loc = $defaultLoc;
                    if ($multilanguage) {
                        $loc .= '&lang=' . $code;
                    }

                    // Prepare manufacturer object
                    $manufacturer             = new \stdClass();
                    $manufacturer->type       = Text::_('PLG_JLSITEMAP_VIRTUEMART_TYPES_MANUFACTURER');
                    $manufacturer->title      = $title;
                    $manufacturer->loc        = $loc;
                    $manufacturer->changefreq = $changefreq;
                    $manufacturer->priority   = $priority;
                    $manufacturer->exclude    = (!empty($exclude)) ? $exclude : false;
                    $manufacturer->alternates = ($multilanguage) ? [] : false;
                    if ($multilanguage) {
                        foreach ($languages as $alternate) {
                            $manufacturer->alternates[$alternate] = $defaultLoc . '&lang=' . $alternate;
                        }
                    }

                    // Add manufacturer to urls
                    $urls[] = $manufacturer;
                }
            }
        }

        // Get vendors
        if ($this->params->get('vendors_enable')) {
            $query = $db->getQuery(true)
                ->select(['m.virtuemart_vendor_id as id', 'm.metarobot'])
                ->from($db->quoteName('#__virtuemart_vendors', 'm'));

            foreach ($languages as $key => $code) {
                $query->select([$key . '.vendor_store_name as ' . 'vendor_name_' . $key])
                    ->leftJoin(
                        $db->quoteName('#__virtuemart_vendors_' . $key, $key)
                        . '  ON ' . $key . '.virtuemart_vendor_id = m.virtuemart_vendor_id'
                    );
            }

            $rows       = $db->setQuery($query)->loadObjectList();
            $changefreq = $this->params->get('vendors_changefreq', $config->get('changefreq', 'weekly'));
            $priority   = $this->params->get('vendors_priority', $config->get('priority', '0.5'));

            foreach ($rows as $row) {
                // Prepare default title
                $selector     = 'vendor_name_' . $defaultLanguageKey;
                $defaultTitle = $row->$selector;

                // Prepare default loc attribute
                $defaultLoc = 'index.php?option=com_virtuemart&view=vendor&layout=tos&Itemid=&virtuemart_vendor_id='
                    . $row->id;

                // Prepare exclude attribute
                $exclude = [];
                if (preg_match('/noindex/', $row->metarobot)) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_VENDOR'),
                        'msg'  => Text::_('PLG_JLSITEMAP_VIRTUEMART_EXCLUDE_VENDOR_ROBOTS'),
                    ];
                }

                foreach ($languages as $key => $code) {
                    $selector = 'vendor_name_' . $key;
                    $title    = (!empty($row->$selector)) ? $row->$selector : $defaultTitle;

                    $loc = $defaultLoc;
                    if ($multilanguage) {
                        $loc .= '&lang=' . $code;
                    }

                    // Prepare vendor object
                    $vendor             = new \stdClass();
                    $vendor->type       = Text::_('PLG_JLSITEMAP_VIRTUEMART_TYPES_VENDOR');
                    $vendor->title      = $title;
                    $vendor->loc        = $loc;
                    $vendor->changefreq = $changefreq;
                    $vendor->priority   = $priority;
                    $vendor->exclude    = (!empty($exclude)) ? $exclude : false;
                    $vendor->alternates = ($multilanguage) ? [] : false;
                    if ($multilanguage) {
                        foreach ($languages as $alternate) {
                            $vendor->alternates[$alternate] = $defaultLoc . '&lang=' . $alternate;
                        }
                    }

                    // Add vendor to urls
                    $urls[] = $vendor;
                }
            }
        }

        $event->setArgument(0, $urls);
    }
}
