<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model\Oauth2;

defined('_JEXEC') || die;

class TokenResponse implements \ArrayAccess
{
	/** @var string|null */
	private $accessToken = null;

	/** @var string|null */
	private $refreshToken = null;

	/** @inheritDoc */
	public function offsetExists($offset)
	{
		return isset($this->{$offset});
	}

	/** @inheritDoc */
	public function offsetGet($offset)
	{
		return $this->{$offset} ?? null;
	}

	/** @inheritDoc */
	public function offsetSet($offset, $value)
	{
		if ($this->offsetExists($offset))
		{
			return;
		}

		$this->{$offset} = $value;
	}

	/** @inheritDoc */
	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException(
			sprintf(
				'You cannot unset an offset in %s',
				__CLASS__
			)
		);
	}

	/**
	 * Casts the data into a plain array
	 *
	 * @return  array
	 * @since   8.4.0
	 */
	public function toArray()
	{
		return [
			'accessToken'  => $this->accessToken,
			'refreshToken' => $this->refreshToken,
		];
	}
}