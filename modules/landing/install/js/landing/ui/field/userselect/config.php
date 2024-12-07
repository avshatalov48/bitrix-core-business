<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/userselect.bundle.css',
	'js' => 'dist/userselect.bundle.js',
	'rel' => [
		'landing.ui.field.basefield',
		'main.core',
		'ui.entity-selector',
	],
	'skip_core' => false,
];