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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use \NRFramework\Conditions\ConditionBuilder;

/**
 *  Advanced Custom Fields Helper
 */
class ACFHelper
{
    /**
     *  Check field publishing assignments.
     *
     *  @param   object  $field  The field object
     *
     *  @return  mixed   Null when the field does not have rules, boolean when the field runs checks
     */
	public static function checkConditions($field)
	{
        $rules = $field->params->get('rules', []);

        if (empty($rules))
        {
            return;
        }

        // Convert object to array recursively
        $rules = json_decode(json_encode($rules), true);
        
        return ConditionBuilder::pass($rules);
	}

    public static function getFileSources($sources, $allowedExtensions = null)
    {
        if (!$sources)
        {
            return;
        }

        // Support comma separated values
        $sources = is_array($sources) ? $sources : explode(',', $sources);
        $result  = array();

        foreach ($sources as $source)
        {
            if (!$pathinfo = pathinfo($source))
            {
                continue;
            }

            if (!isset($pathinfo['extension']))
            {
                continue;
            }

            if ($allowedExtensions && !in_array($pathinfo['extension'], $allowedExtensions))
            {
                continue;
            }

            // Add root path to local source
            if (strpos($source, 'http') === false)
            {
                $source = Uri::root() . ltrim($source, '/');
            }

            $result[] = array(
                'ext'  => $pathinfo['extension'],
                'file' => $source
            );
        }

        return $result;
    }
}