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
		'main.core.events',
		'landing.backend',
		'landing.pageobject',
		'main.core',
	],
	'skip_core' => false,
];