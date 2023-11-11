<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/hotkey.bundle.css',
	'js' => 'dist/hotkey.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.utils',
	],
	'skip_core' => true,
];