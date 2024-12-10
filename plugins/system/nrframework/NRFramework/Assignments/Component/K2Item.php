<?php

/**
 * @author          Tassos.gr
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Assignments\Component;

defined('_JEXEC') or die;

class K2Item extends K2Base
{
    /**
     *  Pass check
     *
     *  @return bool
     */
    public function pass()
    {
        $pass = $this->passSinglePage();

        // Keywords Checking
        $contentKeywords = isset($this->params->cont_keywords) ? $this->params->cont_keywords : '';
        $metaKeywords    = isset($this->params->meta_keywords) ? $this->params->meta_keywords : '';

        // If both are empty, do not maky any further check
        if (empty($contentKeywords) && empty($metaKeywords))
        {
            return $pass;
        }

        // Load current K2 Item object
        if (!$item = $this->getK2Item())
        {
            return false;
        }

        // check items's text
        if (!empty($contentKeywords))
        {
            $pass = $this->passArrayInString($contentKeywords, $item->introtext . $item->fulltext);
        }
        
        // check item's metakeywords
        if (!empty($metaKeywords))
        {
            $pass = $this->passArrayInString($metaKeywords, $item->metakey);
        }

        return $pass;
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return int Article ID
     */
    public function value()
    {
        return $this->request->id;
    }
}