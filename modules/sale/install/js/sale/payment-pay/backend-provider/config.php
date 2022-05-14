<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/backend-provider.bundle.css',
	'js' => 'dist/backend-provider.bundle.js',
	'rel' => [
		'main.core',
		'sale.payment-pay.lib',
	],
	'skip_core' => false,
];