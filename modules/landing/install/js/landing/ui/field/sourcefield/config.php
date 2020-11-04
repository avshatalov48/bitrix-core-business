<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sourcefield.bundle.css',
	'js' => 'dist/sourcefield.bundle.js',
	'rel' => [
		'landing.env',
		'landing.ui.field.basefield',
		'landing.loc',
		'main.core',
	],
	'skip_core' => false,
];