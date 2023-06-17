<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/desktop.bundle.js',
	],
	'rel' => [
		'im.v2.const',
		'im.v2.lib.logger',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'desktopIsActive' => CIMMessenger::CheckDesktopStatusOnline(),
		'desktopVersion' => CIMMessenger::GetDesktopVersion()
	]
];