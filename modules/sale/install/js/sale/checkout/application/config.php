<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/application.bundle.css',
	'js' => 'dist/application.bundle.js',
	'rel' => [
		'sale.checkout.lib',
		'ui.vue.vuex',
		'sale.checkout.controller',
		'sale.checkout.model',
		'ui.vue',
		'main.core.events',
		'main.core',
		'sale.checkout.const',
		'sale.checkout.view.total',
		'sale.checkout.view.product',
		'sale.checkout.view.property',
		'sale.checkout.view.user-consent',
		'sale.checkout.view.element.button',
		'sale.checkout.view.successful',
		'sale.checkout.view.empty-cart',
		'sale.checkout.view.payment',
		'sale.checkout.view.alert',
	],
	'skip_core' => false,
];