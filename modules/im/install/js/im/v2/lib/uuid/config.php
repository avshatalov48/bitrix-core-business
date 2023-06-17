<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/uuid.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.utils',
	],
	'skip_core' => true,
];