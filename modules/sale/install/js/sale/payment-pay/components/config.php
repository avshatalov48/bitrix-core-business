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
		'sale.payment-pay.lib',
		'sale.payment-pay.mixins.application',
		'sale.payment-pay.backend-provider',
		'sale.payment-pay.mixins.payment-system',
		'ui.vue',
		'sale.payment-pay.const',
		'main.core.events',
	],
	'skip_core' => true,
];