<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace ACF\Helpers;

defined('_JEXEC') or die;

use NRFramework\Functions;
use YOOtheme\Builder\Joomla\Fields\FieldsHelper;
use YOOtheme\Application as YooApplication;
use YOOtheme\Event;

class Yoo
{
    public static function initFieldParser()
    {
        // Ensure YOOtheme Pro is ready
        if (!class_exists(YooApplication::class, false))
        {
            return;
        }

        Event::on('source.com_fields.field', function($config, $field, $source, $context) {
            // If it's not an ACF Field, return current config
            if (substr($field->type, 0, 3) !== 'acf')
            {
                return $config;
            }

            // Get the helper class of the field
            $helperClass = '\ACF\Helpers\Fields\\' . ucfirst(substr($field->type, 3));

            // If it does not exist, return current config
            if (!class_exists($helperClass))
            {
                return $config;
            }

            // If the method does not have a resolve method, return current config
            if (!method_exists($helperClass, 'yooResolve'))
            {
                return $config;
            }

            $payload = [
                'extensions' => [
                    'call' => [
                        'func' => $helperClass . '::yooResolve',
                        'args' => ['context' => $context, 'field_id' => $field->id]
                    ]
                ],
            ] + $config;

            // Get and set the type
            $type = method_exists($helperClass, 'getYooType') ? $helperClass::getYooType($field, $source) : '';
            if (!empty($type))
            {
                $payload['type'] = $type;
            }

            return $payload;
        });
    }

    public static function getSubfield($id, $context)
    {
        static $fields = [];

        if (!isset($fields[$context]))
		{
            $fields[$context] = [];
			
            foreach (FieldsHelper::getFields($context, null, true) as $field)
			{
                $fields[$context][$field->id] = $field;
            }
        }

        return !empty($fields[$context][$id]) ? $fields[$context][$id] : null;
    }
}