<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.core',
		'sale.checkout.lib',
		'currency.currency-core',
		'ui.vue',
		'sale.checkout.view.element.button',
		'sale.checkout.const',
	],
	'skip_core' => false,
];