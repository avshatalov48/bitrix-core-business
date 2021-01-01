<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/presetfield.bundle.css',
	'js' => 'dist/presetfield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'main.core',
		'landing.loc',
	],
	'skip_core' => false,
];