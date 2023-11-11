<?
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
		'main.core.events',
		'main.core',
		'im.v2.lib.utils',
		'im.v2.lib.desktop-api',
		'im.v2.application.core',
		'im.v2.lib.logger',
		'im.v2.lib.rest',
		'im.v2.const',
	],
	'skip_core' => false,
	'settings' => [
		'desktopIsActive' => CIMMessenger::CheckDesktopStatusOnline()
	]
];