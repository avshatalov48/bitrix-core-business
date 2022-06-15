<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/document-grid.bundle.css',
	'js' => 'dist/document-grid.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.buttons',
		'catalog.store-use',
	],
	'skip_core' => false,
];