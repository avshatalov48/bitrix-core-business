<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'rel' => [
		'ui.polyfill.closest',
		'ui.polyfill.find',
		'ui.polyfill.includes',
		'ui.polyfill.intersectionobserver',
		'ui.polyfill.matches',
		'ui.polyfill.promise'
	],

	'bundle_js' => 'main_polyfill'
];