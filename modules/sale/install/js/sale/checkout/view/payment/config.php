<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.vue',
		'main.core',
		'currency.currency-core',
		'sale.payment-pay.components',
	],
	'skip_core' => false,
];