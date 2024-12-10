<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model;

defined('_JEXEC') || die;

use Akeeba\Backup\Site\Model\Oauth2\ProviderInterface;
use FOF40\Model\Model;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Custom OAuth2 Helper model
 *
 * @since   8.4.0
 */
class Oauth2 extends Model
{
	/**
	 * Returns the provider object for the requested engine
	 *
	 * @param   string  $engine  The requested engine
	 *
	 * @return  ProviderInterface  The provider object
	 * @throws  \InvalidArgumentException  If the engine is not available
	 * @since   8.4.0
	 */
	public function getProvider(string $engine): ProviderInterface
	{
		$className = __NAMESPACE__ . '\\Oauth2\\' . ucfirst(strtolower($engine)) . 'Engine';

		if (!class_exists($className))
		{
			throw new \InvalidArgumentException(sprintf("Invalid engine: %s", $engine));
		}

		return new $className;
	}

	/**
	 * Is the requested provider enabled in the component options?
	 *
	 * @param   string  $engine  The requested engine
	 *
	 * @return  bool
	 * @since   8.4.0
	 */
	public function isEnabled(string $engine): bool
	{
		$key     = sprintf('oauth2_client_%s', strtolower($engine));
		$cParams = ComponentHelper::getParams('com_akeeba');

		return $cParams->get($key, 0) != 0;
	}
}