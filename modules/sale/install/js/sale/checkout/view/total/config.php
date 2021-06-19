<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/total.bundle.css',
	'js' => 'dist/total.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'sale.checkout.const',
		'main.core.events',
		'ui.vue',
		'currency.currency-core',
	],
	'skip_core' => true,
];