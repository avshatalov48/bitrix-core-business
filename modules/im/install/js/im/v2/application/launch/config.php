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
		'main.core',
		'im.v2.lib.logger',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];