<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'rel' => [
		'main.polyfill.core',
		'main.polyfill.customevent'
	],
	'skip_core' => true,
];