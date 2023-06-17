<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/color_field.bundle.css',
	'js' => 'dist/color_field.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'main.popup',
		'ui.fonts.opensans',
		'ui.design-tokens',
		'main.core.events',
		'landing.ui.field.image',
		'landing.backend',
		'landing.pageobject',
		'main.core',
	],
	'skip_core' => false,
];