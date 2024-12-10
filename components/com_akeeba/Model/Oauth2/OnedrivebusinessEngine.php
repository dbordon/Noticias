<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model\Oauth2;

defined('_JEXEC') || die;

use FOF40\Input\Input;

class OnedrivebusinessEngine extends AbstractProvider implements ProviderInterface
{
	/** @var string  */
	protected $tokenEndpoint = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

	/** @var string  */
	protected $engineNameForHumans = 'OneDrive';

	public function getAuthenticationUrl(): string
	{
		$this->checkConfiguration();

		[$id, $secret] = $this->getIdAndSecret();

		$params = [
			'client_id'     => $id,
			'response_type' => 'code',
			'redirect_uri'  => $this->getUri('step2'),
			'response_mode' => 'query',
			'scope'         => implode(
				' ', [
					'files.readwrite.all',
					'user.read',
					'offline_access',
				]
			),
		];

		return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . http_build_query($params);
	}

	protected function getResponseCustomFields(Input $input): array
	{
		return array_merge(
			parent::getResponseCustomFields($input),
			[
				'scope'        => 'files.readwrite.all user.read offline_access',
				'redirect_uri' => $this->getUri('step2'),
			]
		);
	}

	protected function getRefreshCustomFields(Input $input): array
	{
		return array_merge(
			parent::getRefreshCustomFields($input),
			[
				'redirect_uri' => $this->getUri('step2'),
			]
		);
	}
}