<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/
namespace NRFramework\Assignments;

defined('_JEXEC') or die;

use NRFramework\Assignment;

class Cookie extends Assignment
{
    public function pass()
    {
        $pass = false;
        $cookie_data        = $this->value();
        $user_content       = empty($this->params->content) ? '' : $this->params->content;
        // return false if the cookie is not found
        if($cookie_data === null)
        {
            return false;
        }

        // return true if the user selected the 'exists' option
        if ($this->selection === 'exists')
        {
            return true;
        }

        switch ($this->selection)
        {
            case 'equal':
                $pass = $user_content === $cookie_data;
                break;
            case 'contains':
                if ($user_content !== '' && strpos($cookie_data, $user_content) !== FALSE)
                {
                    $pass = true;
                }
                break;
            case 'starts':
                if ($user_content !== '' && substr($cookie_data, 0, strlen($user_content)) === $user_content)
                {
                    $pass = true;
                }
                break;
            case 'ends':
                if ($user_content !== '' && substr($cookie_data, -strlen($user_content)) === $user_content)
                {
                    $pass = true;
                }
                break;
        }

        return $pass;
    }

    /**
     *  Returns the assignment's value
     * 
     *  @return string Cookie data
     */
	public function value()
	{
		return $this->app->input->cookie->get($this->params->name);
	}
}