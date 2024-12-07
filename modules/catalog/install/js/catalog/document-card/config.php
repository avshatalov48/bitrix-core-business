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
		'catalog.store-enable-wizard',
		'main.popup',
		'ui.buttons',
		'currency.currency-core',
		'ui.entity-selector',
		'main.core.events',
		'ui.feedback.form',
		'main.core',
	],
	'skip_core' => false,
];