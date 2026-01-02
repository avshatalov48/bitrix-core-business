<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/view.bundle.css',
	'js' => 'dist/view.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.section',
		'main.popup',
		'ui.switcher',
		'ui.entity-selector',
		'ui.info-helper',
		'ui.form-elements.view',
		'main.core',
	],
	'skip_core' => false,
];