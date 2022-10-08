<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/main.popup.bundle.css',
	'js' => 'dist/main.popup.bundle.js',
	'rel' => [
		'main.core.z-index-manager',
		'main.core.events',
		'main.core',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];