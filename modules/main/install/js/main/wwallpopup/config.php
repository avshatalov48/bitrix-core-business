<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/wwallpopup.bundle.css',
	'js' => 'dist/wwallpopup.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.buttons',
	],
	'skip_core' => false,
];