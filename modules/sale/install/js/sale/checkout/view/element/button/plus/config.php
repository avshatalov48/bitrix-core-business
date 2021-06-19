<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/plus.bundle.css',
	'js' => 'dist/plus.bundle.js',
	'rel' => [
		'ui.vue',
		'main.core',
		'sale.checkout.const',
	],
	'skip_core' => false,
];