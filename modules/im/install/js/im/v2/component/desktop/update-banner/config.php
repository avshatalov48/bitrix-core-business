<?php

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Loader::includeModule('im'))
{
	return [];
}

$config = new \Bitrix\Im\V2\Application\Config();

return [
	'css' => 'dist/update-banner.bundle.css',
	'js' => 'dist/update-banner.bundle.js',
	'rel' => [
		'main.core',
		'ui.vue3.components.rich-loc',
		'im.v2.lib.analytics',
		'im.v2.lib.desktop-api',
		'im.v2.lib.utils',
		'im.v2.lib.helpdesk',
	],
	'skip_core' => false,
	'settings' => [
		'desktopDownloadUrl' => $config->getDesktopDownloadLink(),
	],
];
