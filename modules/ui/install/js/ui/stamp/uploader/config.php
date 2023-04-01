<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/uploader.bundle.css',
	'js' => 'dist/uploader.bundle.js',
	'rel' => [
		'ui.uploader.core',
		'ui.dialogs.messagebox',
		'ui.sidepanel.layout',
		'main.loader',
		'ui.draganddrop.draggable',
		'main.core',
		'main.core.events',
		'ui.buttons',
	],
	'skip_core' => false,
];