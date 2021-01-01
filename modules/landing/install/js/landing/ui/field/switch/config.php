<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/switch.bundle.css',
	'js' => 'dist/switch.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'main.core',
	],
	'skip_core' => false,
];