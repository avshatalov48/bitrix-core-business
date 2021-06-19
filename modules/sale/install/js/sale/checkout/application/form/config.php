<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/form.bundle.css',
	'js' => 'dist/form.bundle.js',
	'rel' => [
		'sale.checkout.lib',
		'main.core',
		'ui.vue.vuex',
		'sale.checkout.controller',
		'sale.checkout.model',
		'ui.vue',
		'main.core.events',
		'sale.checkout.const',
		'sale.checkout.view.total',
		'sale.checkout.view.product',
		'sale.checkout.view.property',
		'sale.checkout.view.user-consent',
		'sale.checkout.view.element.button.checkout',
		'sale.checkout.view.successful',
		'sale.checkout.view.empty-cart',
		'sale.checkout.view.payment',
		'sale.checkout.view.alert',
	],
	'skip_core' => false,
];