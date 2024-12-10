<?php

/**
 *  @author          Tassos Marinos <info@tassos.gr>
 *  @link            http://www.tassos.gr
 *  @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignment;

/**
 *  IP addresses sample
 *
 *  Greece / Dodecanese:  94.67.238.3
 *  Belgium / Flanders:   37.62.255.255
 *  USA / New York:       72.229.28.185
 */
class GeoIPBase extends Assignment
{
    /**
     *  GeoIP Class
     *
     *  @var  class
     */
    protected $geo;

    /**
     *  Class constructor
     *
     *  @param  object  $assignment
     *  @param  object  $factory
     */
    public function __construct($assignment, $factory)
    {
        // Load Geo Class
        $ip = isset($assignment->params->ip) ? $assignment->params->ip : null;
        $this->loadGeo($ip);

        if (!$this->geo)
        {
            return false;
        }

        parent::__construct($assignment, $factory);

        // Convert a comma/newline separated selection string into an array
        if (!is_array($this->selection))
        {
            $this->selection = $this->splitKeywords($this->selection);
        }
    }

    /**
     *  Load GeoIP Classes
     *
     *  @return  void
     */
    private function loadGeo($ip)
    {
        if (!class_exists('TGeoIP'))
        {
            $path = JPATH_PLUGINS . '/system/tgeoip';

            if (@file_exists($path . '/helper/tgeoip.php'))
            {
                if (@include_once($path . '/vendor/autoload.php'))
                {
                    @include_once $path . '/helper/tgeoip.php';
                }
            }
        }

        $this->geo = new \TGeoIP($ip);
    }
}