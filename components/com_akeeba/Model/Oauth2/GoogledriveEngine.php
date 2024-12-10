<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model\Oauth2;

defined('_JEXEC') || die;

use FOF40\Input\Input;

class GoogledriveEngine extends AbstractProvider implements ProviderInterface
{
	/** @var string  */
	protected $tokenEndpoint = 'https://www.googleapis.com/oauth2/v4/token';

	/** @var string  */
	protected $engineNameForHumans = 'Google Drive';

	public function getAuthenticationUrl(): string
	{
		$this->checkConfiguration();

		[$id, $secret] = $this->getIdAndSecret();

		$params = [
			'client_id'     => $id,
			'redirect_uri'  => $this->getUri('step2'),
			'scope'         => 'https://www.googleapis.com/auth/drive',
			'access_type'   => 'offline',
			'prompt'        => 'consent',
			'response_type' => 'code',
		];

		return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
	}

	protected function getResponseCustomFields(Input $input): array
	{
		return array_merge(
			parent::getResponseCustomFields($input),
			[
				'redirect_uri' => $this->getUri('step2'),
			]
		);
	}
}