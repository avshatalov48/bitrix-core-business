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
		'sale.checkout.view.element.button.item-mobile-menu',
		'sale.checkout.view.element.button.remove',
		'sale.checkout.view.element.button.plus',
		'sale.checkout.view.element.button.minus',
		'sale.checkout.view.element.animate-price',
		'sale.checkout.view.element.button.resotre',
		'sale.checkout.view.mixins',
		'currency.currency-core',
		'ui.vue',
		'main.core.events',
		'sale.checkout.const',
	],
	'skip_core' => true,
];