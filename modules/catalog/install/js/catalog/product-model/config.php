<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/product-model.bundle.css',
	'js' => 'dist/product-model.bundle.js',
	'rel' => [
		'main.core.events',
		'catalog.product-calculator',
		'main.core',
		'catalog.product-model',
	],
	'skip_core' => false,
];