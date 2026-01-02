<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/disk.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'rest.client',
		'im.v2.lib.rest',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => true,
];
