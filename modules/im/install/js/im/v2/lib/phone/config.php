<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/phone.bundle.js',
	],
	'rel' => [
		'voximplant',
		'voximplant.phone-calls',
		'main.core',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.lib.logger',
		'im.v2.lib.desktop-api',
		'im.v2.lib.call',
		'im.v2.lib.sound-notification',
	],
	'skip_core' => false,
];