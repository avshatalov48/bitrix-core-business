<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/productfield.bundle.css',
	'js' => 'dist/productfield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'catalog.product-form',
		'catalog.product-calculator',
		'landing.pageobject',
		'main.core',
		'main.core.events',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];