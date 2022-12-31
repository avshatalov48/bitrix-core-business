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
		'ui.entity-selector',
		'main.popup',
		'catalog.store-use',
		'ui.feedback.form',
		'main.core',
	],
	'skip_core' => false,
];