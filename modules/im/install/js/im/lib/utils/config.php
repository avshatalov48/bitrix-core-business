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
		'main.core',
		'im.const',
		'main.date',
	],
	'skip_core' => false,
];