<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/paysystemsselectorfield.bundle.css',
	'js' => 'dist/paysystemsselectorfield.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'main.loader',
		'main.core',
		'landing.loc',
		'landing.ui.field.smallswitch',
	],
	'skip_core' => false,
];