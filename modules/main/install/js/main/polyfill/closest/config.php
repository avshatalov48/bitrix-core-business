<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/polyfill/closest/js/closest.js'
	],

	'rel' => [
		'main.polyfill.matches'
	],

	'bundle_js' => 'main_polyfill_closest'
];