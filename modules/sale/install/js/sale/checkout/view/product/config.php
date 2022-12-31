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
		'sale.checkout.lib',
		'sale.checkout.view.element.input',
		'currency.currency-core',
		'sale.checkout.view.mixins',
		'sale.checkout.view.element.animate-price',
		'sale.checkout.view.element.button',
		'ui.vue',
		'catalog.sku-tree',
		'sale.checkout.const',
		'main.core.events',
	],
	'skip_core' => true,
];