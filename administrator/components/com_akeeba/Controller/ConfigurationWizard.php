<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Controller\Mixin\CustomACL;
use Akeeba\Backup\Admin\Controller\Mixin\PredefinedTaskList;
use FOF40\Container\Container;
use FOF40\Controller\Controller;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Controller for the configuration wizard
 */
class ConfigurationWizard extends Controller
{
	use CustomACL;
	use PredefinedTaskList;

	/** @var bool  */
	private $noFlush = false;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList(['main', 'ajax']);

		$this->noFlush = ComponentHelper::getParams('com_akeeba')->get('no_flush', 0) == 1;
	}

	/**
	 * Handles AJAX request by proxying the call to the Model, which does all the work, and returning the JSON encoded
	 * result back to the browser.
	 */
	public function ajax()
	{
		/** @var \Akeeba\Backup\Admin\Model\ConfigurationWizard $model */
		$model = $this->getModel();
		$model->setState('act', $this->input->get('act', '', 'cmd'));
		$ret = $model->runAjax();

		@ob_end_clean();
		echo '###' . json_encode($ret) . '###';

		if (!$this->noFlush)
		{
			flush();
		}

		$this->container->platform->closeApplication();
	}

}
