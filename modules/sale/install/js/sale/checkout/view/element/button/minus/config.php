<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/minus.bundle.css',
	'js' => 'dist/minus.bundle.js',
	'rel' => [
		'ui.vue',
		'main.core',
		'sale.checkout.const',
	],
	'skip_core' => false,
];