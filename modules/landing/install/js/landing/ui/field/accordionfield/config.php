<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/accordionfield.bundle.css',
	'js' => 'dist/accordionfield.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'landing.ui.field.basefield',
		'main.core',
		'landing.ui.field.smallswitch',
	],
	'skip_core' => false,
];