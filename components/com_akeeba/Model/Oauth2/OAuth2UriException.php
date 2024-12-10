<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model\Oauth2;

defined('_JEXEC') || die;

use RuntimeException;
use Throwable;

/**
 * OAuth2 Helper error redirecting to a URL
 *
 * @since   8.4.0
 */
class OAuth2UriException extends RuntimeException
{
	/** @var string  */
	private $url;

	public function __construct(string $url, Throwable $previous = null)
	{
		$message = sprintf('For more information please visit %s', $url);
		$this->url = $url;

		parent::__construct($message, 500, $previous);
	}

	public function getUrl(): string
	{
		return $this->url;
	}
}