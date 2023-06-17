<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/settings.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];