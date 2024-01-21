<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/rest.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.application.core',
	],
	'skip_core' => false,
];