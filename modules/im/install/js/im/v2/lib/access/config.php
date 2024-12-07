<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/access.bundle.css',
	'js' => [
		'./dist/access.bundle.js',
	],
	'rel' => [
		'im.v2.lib.rest',
		'main.core',
		'main.popup',
		'im.v2.const',
		'im.v2.lib.feature',
	],
	'skip_core' => false,
];