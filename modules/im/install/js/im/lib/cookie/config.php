<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/cookie.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.lib.localstorage',
	],
	'skip_core' => true,
];