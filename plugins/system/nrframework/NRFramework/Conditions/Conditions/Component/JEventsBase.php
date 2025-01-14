<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class JEventsBase extends ComponentBase
{
    /**
     * The component's Single Page view name
     *
     * @var string
     */
    protected $viewSingle = 'icalrepeat';

    /**
     * The component's option name
     *
     * @var string
     */
    protected $component_option = 'com_jevents';

    /**
     *  Indicates whether the page is a single page
     *
     *  @return  boolean
     */
    public function isSinglePage()
    {
        return ($this->request->task == 'icalrepeat.detail' || $this->request->task == 'icalrepeat');
    }
    
    /**
     * Class Constructor
     *
     * @param object $options
     * @param object $factory
     */
    public function __construct($options, $factory)
	{
        parent::__construct($options, $factory);
        $this->request->id = $this->app->input->get('evid');
        $this->request->task = $this->app->input->get('jevtask');
    }
    
    /**
     * Get single page's assosiated categories
     *
     * @param   Integer  The Single Page id
	 * 
     * @return  array
     */
	protected function getSinglePageCategories($id)
	{
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select('catid')
            ->from('#__jevents_vevent')
            ->where($db->quoteName('ev_id') . '=' . $id);

        $db->setQuery($query);

		return $db->loadColumn();
	}
}