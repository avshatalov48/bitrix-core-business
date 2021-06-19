<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/payment.bundle.css',
	'js' => 'dist/payment.bundle.js',
	'rel' => [
		'ui.vue',
		'sale.checkout.const',
		'main.core',
		'sale.checkout.view.mixins',
	],
	'skip_core' => false,
];