<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/item-mobile-menu.bundle.css',
	'js' => 'dist/item-mobile-menu.bundle.js',
	'rel' => [
		'ui.vue',
		'main.core',
		'sale.checkout.const',
	],
	'skip_core' => false,
];