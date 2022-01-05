<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'sale.checkout.const',
		'sale.checkout.view.product.info',
		'sale.checkout.view.product.price',
		'sale.checkout.view.product.info-deleted',
	],
	'skip_core' => true,
];