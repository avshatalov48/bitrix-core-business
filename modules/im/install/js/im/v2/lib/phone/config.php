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
		'main.polyfill.core',
		'voximplant',
		'voximplant.phone-calls',
		'applayout',
		'im.v2.application.core',
	],
	'skip_core' => true,
];