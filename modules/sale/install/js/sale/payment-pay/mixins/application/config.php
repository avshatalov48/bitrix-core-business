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
		'sale.payment-pay.const',
		'main.core.events',
		'sale.payment-pay.lib',
	],
	'skip_core' => true,
];