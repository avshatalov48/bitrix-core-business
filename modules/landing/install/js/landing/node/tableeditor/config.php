<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/tableeditor.bundle.css',
	'js' => 'dist/tableeditor.bundle.js',
	'rel' => [
		'ui.draganddrop.draggable',
		'main.core',
	],
	'skip_core' => false,
];