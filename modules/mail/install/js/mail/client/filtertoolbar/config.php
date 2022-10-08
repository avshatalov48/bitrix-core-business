<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/filtertoolbar.bundle.css',
	'js' => 'dist/filtertoolbar.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];