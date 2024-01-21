<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/aibutton.bundle.css',
	'js' => 'dist/aibutton.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.ui.button.basebutton',
		'ui.fonts.opensans',
	],
	'skip_core' => true,
];