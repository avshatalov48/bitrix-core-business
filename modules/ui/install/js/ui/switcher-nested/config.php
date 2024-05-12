<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/switcher-nested.bundle.js',
	'rel' => [
		'ui.draganddrop.draggable',
		'main.core',
		'main.core.events',
		'main.popup',
		'ui.section',
		'ui.switcher',
	],
	'skip_core' => false,
];