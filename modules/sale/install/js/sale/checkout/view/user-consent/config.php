<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/user-consent.bundle.css',
	'js' => 'dist/user-consent.bundle.js',
	'rel' => [
		'ui.vue',
		'main.core',
		'main.core.events',
		'sale.checkout.const',
	],
	'skip_core' => false,
];