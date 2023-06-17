<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/script.css',
	'js' => 'dist/script.js',
	'rel' => [
		'ui.buttons',
		'main.popup',
		'ui.alerts',
		'main.loader',
		'main.core.events',
		'main.core',
		'ui.fonts.opensans',
		'ui.design-tokens',
		'ui.fonts.montserrat',
	],
	'skip_core' => false,
];