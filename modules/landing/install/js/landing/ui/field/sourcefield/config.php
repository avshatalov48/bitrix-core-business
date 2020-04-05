<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sourcefield.bundle.css',
	'js' => 'dist/sourcefield.bundle.js',
	'rel' => [
		'main.core',
		'landing.loc',
		'landing.env',
		'landing.ui.field.basefield',
	],
	'skip_core' => false,
];