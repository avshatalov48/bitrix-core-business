<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/view.bundle.css',
	'js' => 'dist/view.bundle.js',
	'rel' => [
		'ui.info-helper',
		'main.core.events',
		'ui.section',
		'main.popup',
		'ui.switcher',
		'main.core',
		'ui.entity-selector',
	],
	'skip_core' => false,
];