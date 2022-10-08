<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sidepanelwrapper.bundle.css',
	'js' => 'dist/sidepanelwrapper.bundle.js',
	'rel' => [
		'main.core',
		'ui.buttons',
		'ui.forms',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];