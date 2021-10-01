<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.core.events',
		'sale.checkout.view.mixins',
		'ui.vue',
		'main.core',
		'sale.checkout.const',
	],
	'skip_core' => false,
];