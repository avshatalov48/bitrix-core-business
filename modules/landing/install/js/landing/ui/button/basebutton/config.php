<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/basebutton.bundle.css',
	'js' => 'dist/basebutton.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];