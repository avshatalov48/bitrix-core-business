<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/datetimefield.bundle.css',
	'js' => 'dist/datetimefield.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.field.basefield',
		'landing.loc',
	],
	'skip_core' => false,
];