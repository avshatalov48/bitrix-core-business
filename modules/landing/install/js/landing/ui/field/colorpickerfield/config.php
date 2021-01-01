<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/colorpickerfield.bundle.css',
	'js' => 'dist/colorpickerfield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'main.core.events',
		'main.core',
		'ui.draganddrop.draggable',
		'landing.ui.component.internal',
		'landing.loc',
		'landing.pageobject',
	],
	'skip_core' => false,
];