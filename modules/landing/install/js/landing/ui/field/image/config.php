<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/image.bundle.css',
	'js' => 'dist/image.bundle.js',
	'rel' => [
		'main.core',
		'landing.loc',
		'landing.main',
		'landing.ui.field.textfield',
		'landing.ui.panel.iconpanel',
		'landing.imageuploader',
		'landing.ui.button.basebutton',
		'landing.imageeditor',
	],
	'skip_core' => false,
];