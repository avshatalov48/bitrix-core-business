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
		'landing.imageuploader',
		'landing.ui.button.basebutton',
		'landing.ui.button.aiimagebutton',
		'landing.pageobject',
		'landing.env',
		'ai.picker',
		'ui.fonts.opensans',
		'ui.forms',
	],
	'skip_core' => false,
];