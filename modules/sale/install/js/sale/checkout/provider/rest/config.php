<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rest.bundle.css',
	'js' => 'dist/rest.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'sale.checkout.const',
	],
	'skip_core' => false,
];