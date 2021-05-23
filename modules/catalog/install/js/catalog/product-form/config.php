<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/product-form.bundle.css',
	'js' => 'dist/product-form.bundle.js',
	'rel' => [
		'ui.notification',
		'currency',
		'ui.layout-form',
		'ui.forms',
		'ui.buttons',
		'catalog.product-selector',
		'ui.common',
		'ui.alerts',
		'catalog.product-calculator',
		'ui.vue.vuex',
		'main.popup',
		'main.core',
		'ui.vue',
		'main.core.events',
		'currency.currency-core',
	],
	'skip_core' => false,
];