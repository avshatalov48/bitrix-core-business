<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/price.bundle.css',
	'js' => 'dist/price.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'sale.checkout.view.element.animate-price',
	],
	'skip_core' => true,
];