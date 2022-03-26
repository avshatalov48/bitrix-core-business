<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/actionbutton.bundle.css',
	'js' => 'dist/actionbutton.bundle.js',
	'rel' => [
		'landing.ui.button.basebutton',
		'main.core',
		'ui.label',
	],
	'skip_core' => false,
];