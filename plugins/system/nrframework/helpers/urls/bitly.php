<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

class nrURLShortBitly extends NRURLShortener
{

    function baseURL()
    {
        return 'http://api.bit.ly/v3/shorten?login='.$this->service->login.'&apiKey='.$this->service->api.'&format=txt&uri='.urlencode($this->url);
    }
}
