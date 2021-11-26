<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/controller.bundle.css',
	'js' => 'dist/controller.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'sale.checkout.provider.rest',
		'sale.checkout.const',
		'sale.checkout.lib',
	],
	'skip_core' => false,
];