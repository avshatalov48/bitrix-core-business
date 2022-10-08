<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/smallswitch.bundle.css',
	'js' => 'dist/smallswitch.bundle.js',
	'rel' => [
		'landing.ui.field.switch',
		'main.core',
		'ui.fonts.opensans',
		'landing.loc',
	],
	'skip_core' => false,
];