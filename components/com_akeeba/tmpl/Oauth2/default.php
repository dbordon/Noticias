<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var \Akeeba\Backup\Site\View\OAuth2\Raw $this
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/** @var JDocumentRaw $doc */
$doc = Factory::getDocument();
$app = Factory::getApplication();

$app->setHeader('Pragma', 'public');
$app->setHeader('Expires', '0');
$app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
$app->setHeader('Cache-Control', 'public');
$doc->setMimeEncoding('text/html');

$title = Text::sprintf('COM_AKEEBA_OAUTH2_TITLE', $this->provider->getEngineNameForHumans());
?>
<html lang="<?= Factory::getApplication()->getLanguage()->getTag() ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= Text::sprintf('COM_AKEEBA_OAUTH2_TITLE', $this->provider->getEngineNameForHumans()) ?></title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
	      rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
	      crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
	        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
	        defer
	        crossorigin="anonymous"></script>
</head>
<body>

<div class="card m-2">
	<div class="card-body">
		<h1>
			<?= Text::sprintf('COM_AKEEBA_OAUTH2_AUTH_ALMOST_COMPLETE', $this->provider->getEngineNameForHumans()) ?>
		</h1>
		<p>
			<?= Text::_('COM_AKEEBA_OAUTH2_AUTH_COPY') ?>
		</p>
		<p>
			<strong><?= Text::_('COM_AKEEBA_OAUTH2_ACCESS') ?></strong><br/>
			<code><?= $this->escape($this->tokens['accessToken']) ?></code><br/>
			<strong><?= Text::_('COM_AKEEBA_OAUTH2_REFRESH') ?></strong><br/>
			<code><?= $this->escape($this->tokens['refreshToken']) ?></code><br/><br/>
		</p>
	</div>
</div>

</body>
</html>
