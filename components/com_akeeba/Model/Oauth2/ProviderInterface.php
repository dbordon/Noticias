<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Site\Model\Oauth2;

defined('_JEXEC') || die;

use FOF40\Input\Input;

/**
 * OAuth2 Helper provider interface
 *
 * @since    8.2.2
 */
interface ProviderInterface
{
	/**
	 * Get the URL to redirect to for the first authentication step (consent screen).
	 *
	 * @return  string
	 * @since   8.4.0
	 */
	public function getAuthenticationUrl(): string;

	/**
	 * Handles the second step of the authentication (exchange code for tokens)
	 *
	 * @param   Input  $input  The raw application input object
	 *
	 * @return  TokenResponse
	 * @since   8.4.0
	 */
	public function handleResponse(Input $input): TokenResponse;

	/**
	 * Handles exchanging a refresh token for an access token
	 *
	 * @param   Input  $input  The raw application input object
	 *
	 * @return  TokenResponse
	 * @since   8.4.0
	 */
	public function handleRefresh(Input $input): TokenResponse;

	public function getEngineNameForHumans(): string;
}