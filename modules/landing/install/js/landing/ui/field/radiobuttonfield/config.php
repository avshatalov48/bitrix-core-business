<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/radiobuttonfield.bundle.css',
	'js' => 'dist/radiobuttonfield.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'landing.ui.field.basefield',
		'ui.buttons',
		'landing.ui.component.internal',
		'landing.loc',
	],
	'skip_core' => false,
];