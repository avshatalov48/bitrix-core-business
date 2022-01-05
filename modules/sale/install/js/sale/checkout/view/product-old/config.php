<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/product.bundle.css',
	'js' => 'dist/product.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'sale.checkout.view.product.list',
		'sale.checkout.view.product.summary',
	],
	'skip_core' => true,
];