<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/init.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.lib.call',
		'im.v2.lib.phone',
		'im.v2.lib.smile-manager',
		'im.v2.lib.user',
		'im.v2.lib.counter',
		'im.v2.lib.logger',
		'im.v2.lib.notifier',
		'im.v2.lib.market',
		'im.v2.lib.desktop',
		'im.v2.lib.promo',
		'im.v2.lib.permission',
		'im.v2.lib.update-state.manager',
	],
	'skip_core' => true,
];