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
		'main.date',
		'main.core',
		'im.v2.const',
	],
	'skip_core' => false,
];