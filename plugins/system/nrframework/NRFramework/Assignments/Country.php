<?php

/**
 *  @author          Tassos Marinos <info@tassos.gr>
 *  @link            http://www.tassos.gr
 *  @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignments\GeoIPBase;

class Country extends GeoIPBase
{
    /**
     *  Country check
     * 
     *  @return bool
     */
    public function pass()
    {
        // try to convert country names to codes
        $this->selection = array_map(function($c) {
            if (strlen($c) > 2)
            {
                $c = \NRFramework\Countries::getCode($c);
            }
            return $c;
        }, $this->selection);

        return $this->passSimple($this->value(), $this->selection);
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return string Country code
     */
	public function value()
	{
		return $this->geo->getCountryCode();
	}
}