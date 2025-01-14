<?php
/**
 * @package    JLSitemap - Contact Plugin
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

namespace Joomla\Plugin\JLSitemap\Contact\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

final class Contact extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var  boolean
     *
     * @since  1.3.0
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
     * @since 1.3.0
     */
    public function onGetUrls(Event $event): void
    {
        if(!ComponentHelper::isEnabled('com_contact'))
        {
            return;
        }

        /**
         * @param array $urls Urls array
         * @param Registry $config Component config
         */
        [$urls, $config] = $event->getArguments();

        $categoryExcludeStates = [
            0  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY_UNPUBLISH'),
            -2 => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY_TRASH'),
            2  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY_ARCHIVE'),
        ];

        $contactExcludeStates = [
            0  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_UNPUBLISH'),
            -2 => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_TRASH'),
            2  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_ARCHIVE'),
        ];

        $multilanguage = $config->get('multilanguage');

        // Categories
        if ($this->params->get('categories_enable', false)) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select(
                    [
                        'c.id',
                        'c.title',
                        'c.published',
                        'c.access',
                        'c.metadata',
                        'c.language',
                        'MAX(a.modified) as modified',
                    ]
                )
                ->from($db->quoteName('#__categories', 'c'))
                ->join('LEFT', '#__contact_details AS a ON a.catid = c.id')
                ->where($db->quoteName('c.extension') . ' = ' . $db->quote('com_contact'))
                ->group('c.id')
                ->order($db->escape('c.lft') . ' ' . $db->escape('asc'));

            // Join over associations
            if ($multilanguage) {
                $query->select('assoc.key as association')
                    ->join(
                        'LEFT',
                        '#__associations AS assoc ON assoc.id = c.id AND assoc.context = ' .
                        $db->quote('com_categories.item')
                    );
            }

            $db->setQuery($query);
            $rows = $db->loadObjectList();

            $nullDate   = $db->getNullDate();
            $changefreq = $this->params->get('categories_changefreq', $config->get('changefreq', 'weekly'));
            $priority   = $this->params->get('categories_priority', $config->get('priority', '0.5'));

            // Add categories to arrays
            $categories = [];
            $alternates = [];
            foreach ($rows as $row) {
                // Prepare loc attribute
                $loc = 'index.php?option=com_contact&view=category&id=' . $row->id;
                if (!empty($row->language) && $row->language !== '*' && $multilanguage) {
                    $loc .= '&lang=' . $row->language;
                }

                // Prepare exclude attribute
                $metadata = new Registry($row->metadata);
                $exclude  = [];
				$robots = $metadata->get('robots', $config->get('siteRobots'));
                if (!empty($robots) && preg_match('/noindex/', $robots)) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY_ROBOTS'),
                    ];
                }

                if (isset($categoryExcludeStates[$row->published])) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY'),
                        'msg'  => $categoryExcludeStates[$row->published],
                    ];
                }

                if (!in_array($row->access, $config->get('guestAccess', []))) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY_ACCESS'),
                    ];
                }

                // Prepare lastmod attribute
                $lastmod = (!empty($row->modified) && $row->modified != $nullDate) ? $row->modified : false;

                // Prepare category object
                $category             = new \stdClass();
                $category->type       = Text::_('PLG_JLSITEMAP_CONTACT_TYPES_CATEGORY');
                $category->title      = $row->title;
                $category->loc        = $loc;
                $category->changefreq = $changefreq;
                $category->priority   = $priority;
                $category->lastmod    = $lastmod;
                $category->exclude    = (!empty($exclude)) ? $exclude : false;
                $category->alternates = ($multilanguage && !empty($row->association)) ? $row->association : false;

                // Add category to array
                $categories[] = $category;

                // Add category to alternates array
                if ($multilanguage && !empty($row->association) && empty($exclude)) {
                    if (!isset($alternates[$row->association])) {
                        $alternates[$row->association] = [];
                    }

                    $alternates[$row->association][$row->language] = $loc;
                };
            }

            // Add alternates to categories
            if (!empty($alternates)) {
                foreach ($categories as &$category) {
                    $category->alternates = ($category->alternates) ? $alternates[$category->alternates] : false;
                }
            }

            // Add categories to urls
            $urls = array_merge($urls, $categories);
        }

        // Contacts
        if ($this->params->get('contacts_enable', false)) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    'a.id',
                    'a.name',
                    'a.alias',
                    'a.published',
                    'a.modified',
                    'a.publish_up',
                    'a.publish_down',
                    'a.access',
                    'a.metadata',
                    'a.language',
                    'c.id as category_id',
                    'c.published as category_published',
                    'c.access as category_access',
                ])
                ->from($db->quoteName('#__contact_details', 'a'))
                ->join('LEFT', '#__categories AS c ON c.id = a.catid')
                ->group('a.id')
                ->order($db->escape('a.ordering') . ' ' . $db->escape('asc'));

            // Join over associations
            if ($multilanguage) {
                $query->select('assoc.key as association')
                    ->join(
                        'LEFT',
                        '#__associations AS assoc ON assoc.id = a.id AND assoc.context = ' .
                        $db->quote('com_contact.item')
                    );
            }

            $db->setQuery($query);
            $rows = $db->loadObjectList();

            $nullDate   = $db->getNullDate();
            $nowDate    = Factory::getDate()->toUnix();
            $changefreq = $this->params->get('contacts_changefreq', $config->get('changefreq', 'weekly'));
            $priority   = $this->params->get('contacts_priority', $config->get('priority', '0.5'));

            // Add contacts to arrays
            $contacts   = [];
            $alternates = [];
            foreach ($rows as $row) {
                // Prepare loc attribute
                $slug = ($row->alias) ? ($row->id . ':' . $row->alias) : $row->id;
                $loc  = 'index.php?option=com_contact&view=contact&id=' . $slug . '&catid=' . $row->category_id;
                if (!empty($row->language) && $row->language !== '*' && $config->get('multilanguage')) {
                    $loc .= '&lang=' . $row->language;
                }

                // Prepare exclude attribute
                $metadata = new Registry($row->metadata);
                $exclude  = [];
                if (preg_match('/noindex/', $metadata->get('robots', $config->get('siteRobots')))) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_ROBOTS'),
                    ];
                }

                if (isset($contactExcludeStates[$row->published])) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT'),
                        'msg'  => $contactExcludeStates[$row->published],
                    ];
                }

                if ($row->publish_up != $nullDate && Factory::getDate($row->publish_up)->toUnix() > $nowDate) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_PUBLISH_UP'),
                    ];
                }

                if ($row->publish_down != $nullDate && Factory::getDate($row->publish_down)->toUnix() < $nowDate) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_PUBLISH_DOWN'),
                    ];
                }

                if (!in_array($row->access, $config->get('guestAccess', []))) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CONTACT_ACCESS'),
                    ];
                }

                if (isset($categoryExcludeStates[$row->category_published])) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY'),
                        'msg'  => $categoryExcludeStates[$row->category_published],
                    ];
                }

                if (!in_array($row->category_access, $config->get('guestAccess', []))) {
                    $exclude[] = [
                        'type' => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY'),
                        'msg'  => Text::_('PLG_JLSITEMAP_CONTACT_EXCLUDE_CATEGORY_ACCESS'),
                    ];
                }

                // Prepare lastmod attribute
                $lastmod = (!empty($row->modified) && $row->modified != $nullDate) ? $row->modified : false;

                // Prepare contact object
                $contact             = new \stdClass();
                $contact->type       = Text::_('PLG_JLSITEMAP_CONTACT_TYPES_CONTACT');
                $contact->title      = $row->name;
                $contact->loc        = $loc;
                $contact->changefreq = $changefreq;
                $contact->priority   = $priority;
                $contact->lastmod    = $lastmod;
                $contact->exclude    = (!empty($exclude)) ? $exclude : false;
                $contact->alternates = ($multilanguage && !empty($row->association)) ? $row->association : false;

                // Add contact to array
                $contacts[] = $contact;

                // Add contact to alternates array
                if ($multilanguage && !empty($row->association) && empty($exclude)) {
                    if (!isset($alternates[$row->association])) {
                        $alternates[$row->association] = [];
                    }

                    $alternates[$row->association][$row->language] = $loc;
                };
            }

            // Add alternates to contacts
            if (!empty($alternates)) {
                foreach ($contacts as &$contact) {
                    $contact->alternates = ($contact->alternates) ? $alternates[$contact->alternates] : false;
                }
            }

            // Add contacts to urls
            $urls = array_merge($urls, $contacts);
        }

        $event->setArgument(0, $urls);
    }
}
