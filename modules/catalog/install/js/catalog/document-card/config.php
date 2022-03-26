<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/document-card.bundle.css',
	'js' => 'dist/document-card.bundle.js',
	'rel' => [
		'catalog.entity-card',
		'main.core.events',
		'currency.currency-core',
		'main.core',
		'ui.entity-selector',
		'main.popup',
		'ui.tour',
	],
	'skip_core' => false,
];