<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'sale.checkout.view.element.input',
		'main.core',
		'ui.vue',
		'sale.checkout.const',
		'sale.checkout.view.property',
	],
	'skip_core' => false,
];