<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/animate-price.bundle.css',
	'js' => 'dist/animate-price.bundle.js',
	'rel' => [
		'ui.vue',
		'main.core',
		'currency.currency-core',
		'sale.checkout.view.element.animate-price',
	],
	'skip_core' => false,
];