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
use Joomla\CMS\Factory;

class SecretsHelper
{
    /**
     * Method to generate secret
     *
     * @param   int  $length  Secret length
     *
     * @return  string
     *
     * @since  1.4.1
     */
    public static function generateSecret($length = 15)
    {
        $secret = '';
        $chars  = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        for ($i = 0; $i < $length; $i++) {
            $key    = rand(0, count($chars) - 1);
            $secret .= $chars[$key];
        }

        return $secret;
    }

    /**
     * Method to get JLSitemap component access key
     *
     * @return  string
     *
     * @since  1.4.1
     */
    public static function getAccessKey()
    {
        // Check access key
        $params     = ComponentHelper::getComponent('com_jlsitemap')->getParams();
        $access_key = $params->get('access_key');

        // Generate new access key
        if (empty($access_key)) {
            $access_key = self::generateSecret();
            $params->set('access_key', $access_key);

            // Save component params
            $component          = new \stdClass();
            $component->element = 'com_jlsitemap';
            $component->params  = (string)$params;
            Factory::getContainer()->get('DatabaseDriver')->updateObject('#__extensions', $component, ['element']);
        }

        return $access_key;
    }
}