<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/main.popup.bundle.css',
	'js' => 'dist/main.popup.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];