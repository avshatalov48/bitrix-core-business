<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/popupcomponentsmader.bundle.css',
	'js' => 'dist/popupcomponentsmader.bundle.js',
	'rel' => [
		'main.core.events',
		'main.loader',
		'main.core',
		'main.popup',
	],
	'skip_core' => false,
];