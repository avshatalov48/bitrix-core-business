<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/core.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.controller',
		'im.application.launch',
	],
	'skip_core' => true,
];