<?php

use Bitrix\Im\V2\Application\Config;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$desktopIsActive = false;
$desktopActiveVersion = 0;
$internetCheckUrl = '';

if (\Bitrix\Main\Loader::includeModule('im'))
{
	$desktopIsActive = CIMMessenger::CheckDesktopStatusOnline();
	$desktopActiveVersion = $desktopIsActive ? CIMMessenger::GetDesktopVersion() : 0;
	$internetCheckUrl = (new Config())->getInternetCheckLink();
}

return [
	'js' => [
		'./dist/desktop-manager.bundle.js',
	],
	'rel' => [
		'ui.vue3',
		'im.v2.component.desktop.update-banner',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.public',
		'im.v2.lib.rest',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.provider.service.chat',
		'im.v2.lib.layout',
		'im.v2.provider.service.recent',
		'im.v2.lib.desktop',
		'im.v2.lib.utils',
		'im.v2.lib.logger',
		'main.core',
		'im.v2.const',
		'im.v2.lib.call',
		'im.v2.lib.message-notifier',
		'im.v2.lib.desktop-api',
	],
	'skip_core' => false,
	'settings' => [
		'desktopIsActive' => $desktopIsActive,
		'desktopActiveVersion' => $desktopActiveVersion,
		'internetCheckUrl' => $internetCheckUrl,
	]
];