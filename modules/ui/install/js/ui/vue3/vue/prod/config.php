<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.bitrixvue',
	],
	'skip_core' => true,
];