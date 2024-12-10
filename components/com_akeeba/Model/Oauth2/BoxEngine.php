<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model\Oauth2;

defined('_JEXEC') || die;

class BoxEngine extends AbstractProvider implements ProviderInterface
{
	/** @var string  */
	protected $tokenEndpoint = 'https://api.box.com/oauth2/token';

	/** @var string  */
	protected $engineNameForHumans = 'Box.com';

	public function getAuthenticationUrl(): string
	{
		$this->checkConfiguration();

		[$id, $secret] = $this->getIdAndSecret();

		$params = [
			'response_type' => 'code',
			'client_id'     => $id,
			'redirect_uri'  => $this->getUri('step2'),
		];

		return 'https://account.box.com/api/oauth2/authorize?' . http_build_query($params);
	}
}