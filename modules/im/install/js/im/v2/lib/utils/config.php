<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/utils.bundle.js',
	],
	'rel' => [
		'im.v2.const',
		'main.core',
		'main.date',
	],
	'skip_core' => false,
];