<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/launch.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.logger',
	],
	'skip_core' => true,
];