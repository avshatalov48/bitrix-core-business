<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/logger.bundle.js',
	],
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];