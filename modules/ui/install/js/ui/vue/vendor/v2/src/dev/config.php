<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'main.polyfill.core',
	],
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];