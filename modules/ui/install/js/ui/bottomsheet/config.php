<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bottomsheet.bundle.css',
	'js' => 'dist/bottomsheet.bundle.js',
	'rel' => [
		'main.core',
		'ui.fonts.roboto',
	],
	'skip_core' => false,
];