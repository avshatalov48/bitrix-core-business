<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'ui.progressround.css',
	'js' => 'dist/progressround.bundle.js',
	'rel' => [
		'ui.fonts.opensans',
		'main.core',
	],
	'skip_core' => false,
];
