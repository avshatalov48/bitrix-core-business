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
		'im.v2.application.core',
	],
	'skip_core' => false,
];