<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/desktop-manager.bundle.js',
	],
	'rel' => [
		'im.public',
		'im.v2.lib.rest',
		'im.v2.lib.call',
		'im.v2.application.core',
		'main.core.events',
		'main.core',
		'im.v2.lib.utils',
		'im.v2.const',
		'im.v2.lib.desktop-api',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
	'settings' => [
		'desktopIsActive' => CIMMessenger::CheckDesktopStatusOnline(),
		'desktopActiveVersion' => CIMMessenger::CheckDesktopStatusOnline() ? CIMMessenger::GetDesktopVersion() : 0
	]
];