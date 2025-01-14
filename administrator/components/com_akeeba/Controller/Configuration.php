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
use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Engine\Platform;
use FOF40\Container\Container;
use FOF40\Controller\Controller;
use FOF40\Input\Input;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Configuration page controller
 */
class Configuration extends Controller
{
	use CustomACL;

	/** @var bool  */
	private $noFlush = false;

	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		$this->noFlush = ComponentHelper::getParams('com_akeeba')->get('no_flush', 0) == 1;
	}


	/**
	 * Handle the apply task which saves the configuration settings and shows the page again
	 */
	public function apply()
	{
		// CSRF prevention
		$this->csrfProtection();

		// Which input am I going to use?
		$jsonFormData = $this->input->getString('jsonForm', null);
		$jsonFormData = is_string($jsonFormData) ? @json_decode($jsonFormData, true) : $jsonFormData;

		if (empty($jsonFormData))
		{
			$input = $this->input;
		}
		else
		{
			$rawData = [];

			foreach ($jsonFormData as $k => $v)
			{
				if (substr($k, 0, 4) !== 'var[')
				{
					$rawData[$k] = $v;

					continue;
				}

				$k                  = substr($k, 4, -1);
				$rawData['var']     = $rawData['var'] ?? [];
				$rawData['var'][$k] = $v;
			}

			$input = new Input($rawData);
		}

		// Get the var array from the request
		$data                        = $input->get('var', [], 'array', 4);
		$data['akeeba.flag.confwiz'] = 1;

		/** @var \Akeeba\Backup\Admin\Model\Configuration $model */
		$model = $this->getModel();
		$model->setState('engineconfig', $data);
		$model->saveEngineConfig();

		// Finally, save the profile description if it has changed
		$profileid = Platform::getInstance()->get_active_profile();

		// Get profile name
		/** @var Profiles $profileRecord */
		$profileRecord = $this->container->factory->model('Profiles')->tmpInstance();
		$profileRecord->findOrFail($profileid);
		$oldProfileName = $profileRecord->description;
		$oldQuickIcon   = $profileRecord->quickicon;

		$profileName = $input->getString('profilename', null);
		$profileName = trim($profileName);

		$quickIconValue = $input->getCmd('quickicon', '');
		$quickIcon      = (int) !empty($quickIconValue);

		$mustSaveProfile = !empty($profileName) && ($profileName != $oldProfileName);
		$mustSaveProfile = $mustSaveProfile || ($quickIcon != $oldQuickIcon);

		if ($mustSaveProfile)
		{
			$profileRecord->save([
				'description' => $profileName,
				'quickicon'   => $quickIcon
			]);
		}

		$this->setRedirect(\Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba&view=Configuration', \Joomla\CMS\Language\Text::_('COM_AKEEBA_CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the save task which saves the configuration settings and returns to the Control Panel page
	 */
	public function save()
	{
		$this->apply();
		$this->setRedirect(\Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba', \Joomla\CMS\Language\Text::_('COM_AKEEBA_CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the save & new task which saves settings, creates a new backup profile, activates it and proceed to the
	 * configuration page once more.
	 */
	public function savenew()
	{
		// Save the current profile
		$this->apply();

		// Create a new profile
		$profileid = Platform::getInstance()->get_active_profile();

		/** @var Profiles $profile */
		$profile = $this->container->factory->model('Profiles')->tmpInstance();
		$profile
			// Load and clone the record we just saved
			->findOrFail($profileid)
			->getClone()
		;
		// Must unset ID before save. The ID cannot be bound with bind()/save(), hence the need to do it the hard way.
		$profile->id = null;
		$profile
			->save([
				'description' => \Joomla\CMS\Language\Text::_('COM_AKEEBA_CONFIG_SAVENEW_DEFAULT_PROFILE_NAME')
			])
		;

		// Activate and edit the new profile
		$returnUrl = base64_encode($this->redirect);
		$token     = $this->container->platform->getToken(true);
		$url       = \Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba&task=SwitchProfile&profileid=' . $profile->getId() .
			'&returnurl=' . $returnUrl . '&' . $token . '=1';
		$this->setRedirect($url);
	}

	/**
	 * Handle the cancel task which doesn't save anything and returns to the Control Panel page
	 */
	public function cancel()
	{
		// CSRF prevention
		$this->csrfProtection();
		$this->setRedirect(\Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba');
	}

	/**
	 * Tests the validity of the FTP connection details
	 */
	public function testftp()
	{
		/** @var \Akeeba\Backup\Admin\Model\Configuration $model */
		$model = $this->getModel();
		$input = $this->input;
		$model->setState('isCurl', $input->get('isCurl', 0, 'int'));
		$model->setState('host', $input->get('host', '', 'raw', 2));
		$model->setState('port', $input->get('port', 21, 'int'));
		$model->setState('user', $input->get('user', '', 'raw', 2));
		$model->setState('pass', $input->get('pass', '', 'raw', 2));
		$model->setState('initdir', $input->get('initdir', '', 'raw', 2));
		$model->setState('usessl', $input->getCmd('usessl', "false") == "true");
		$model->setState('passive', $input->getCmd('passive', "false") == "true");
		$model->setState('passive_mode_workaround', $input->getCmd('passive_mode_workaround', "false") == "true");

		try
		{
			$model->testFTP();
			$testResult = true;
		}
		catch (\RuntimeException $e)
		{
			$testResult = $e->getMessage();
		}

		@ob_end_clean();
		echo '###' . json_encode($testResult) . '###';

		if (!$this->noFlush)
		{
			flush();
		}

		$this->container->platform->closeApplication();
	}

	/**
	 * Tests the validity of the SFTP connection details
	 */
	public function testsftp()
	{
		/** @var \Akeeba\Backup\Admin\Model\Configuration $model */
		$model = $this->getModel();
		$model->setState('isCurl', $this->input->get('isCurl', 0, 'int'));
		$model->setState('host', $this->input->get('host', '', 'raw', 2));
		$model->setState('port', $this->input->get('port', 21, 'int'));
		$model->setState('user', $this->input->get('user', '', 'raw', 2));
		$model->setState('pass', $this->input->get('pass', '', 'raw', 2));
		$model->setState('privkey', $this->input->get('privkey', '', 'raw', 2));
		$model->setState('pubkey', $this->input->get('pubkey', '', 'raw', 2));
		$model->setState('initdir', $this->input->get('initdir', '', 'raw', 2));

		try
		{
			$model->testSFTP();
			$testResult = true;
		}
		catch (\RuntimeException $e)
		{
			$testResult = $e->getMessage();
		}

		@ob_end_clean();
		echo '###' . json_encode($testResult) . '###';

		if (!$this->noFlush)
		{
			flush();
		}

		$this->container->platform->closeApplication();
	}

	/**
	 * Opens an OAuth window for the selected data processing engine
	 */
	public function dpeoauthopen()
	{
		/** @var \Akeeba\Backup\Admin\Model\Configuration $model */
		$model = $this->getModel();
		$model->setState('engine', $this->input->get('engine', '', 'raw'));
		$model->setState('params', $this->input->get('params', array(), 'array', 2));

		@ob_end_clean();
		$model->dpeOuthOpen();

		if (!$this->noFlush)
		{
			flush();
		}

		$this->container->platform->closeApplication();
	}

	/**
	 * Runs a custom API call against the selected data processing engine and returns the JSON encoded result
	 */
	public function dpecustomapi()
	{
		/** @var \Akeeba\Backup\Admin\Model\Configuration $model */
		$model = $this->getModel();
		$model->setState('engine', $this->input->get('engine', '', 'raw', 2));
		$model->setState('method', $this->input->get('method', '', 'raw', 2));
		$model->setState('params', $this->input->get('params', array(), 'array', 2));

		@ob_end_clean();
		echo '###' . json_encode($model->dpeCustomAPICall()) . '###';

		if (!$this->noFlush)
		{
			flush();
		}

		$this->container->platform->closeApplication();
	}

	/**
	 * Runs a custom API call against the selected data processing engine and returns the raw result
	 */
	public function dpecustomapiraw()
	{
		/** @var \Akeeba\Backup\Admin\Model\Configuration $model */
		$model = $this->getModel();
		$model->setState('engine', $this->input->get('engine', '', 'raw', 2));
		$model->setState('method', $this->input->get('method', '', 'raw', 2));
		$model->setState('params', $this->input->get('params', array(), 'array', 2));

		@ob_end_clean();
		echo $model->dpeCustomAPICall();

		if (!$this->noFlush)
		{
			flush();
		}

		$this->container->platform->closeApplication();
	}
}
