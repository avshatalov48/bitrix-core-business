<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/main/polyfill/includes/js/includes.js'
	],

	'rel' => [
		'main.polyfill.find'
	],

	'bundle_js' => 'main_polyfill_includes'
];