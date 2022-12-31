<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.type',
		'ui.vue',
		'main.core',
		'main.core.events',
		'sale.checkout.const',
		'ui.entity-selector',
	],
	'skip_core' => false,
];