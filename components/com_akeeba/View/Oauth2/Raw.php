<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */
namespace Akeeba\Backup\Site\View\Oauth2;

defined('_JEXEC') || die;

use Akeeba\Backup\Site\Model\Oauth2\ProviderInterface;
use Akeeba\Backup\Site\Model\Oauth2\TokenResponse;
use FOF40\View\DataView\Raw as BaseView;

class Raw extends BaseView
{
	/** @var ProviderInterface|null  */
	public $provider = null;

	/** @var TokenResponse|null  */
	public $tokens = null;

	/** @var \Exception|null  */
	public $exception = null;

	/** @var string|null  */
	public $step1url = null;
}