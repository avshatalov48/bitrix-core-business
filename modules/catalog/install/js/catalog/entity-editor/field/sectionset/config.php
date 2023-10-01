<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sectionset.bundle.css',
	'js' => 'dist/sectionset.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.entity-selector',
		'catalog.entity-editor.field.productset',
		'main.core.events',
	],
	'skip_core' => true,
];