<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/summary.bundle.css',
	'js' => 'dist/summary.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'currency.currency-core',
	],
	'skip_core' => true,
];