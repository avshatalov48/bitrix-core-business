<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/messagegrid.bundle.css',
	'js' => 'dist/messagegrid.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.buttons',
		'ui.fonts.opensans',
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];