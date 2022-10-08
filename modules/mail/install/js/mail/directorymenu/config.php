<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/directorymenu.bundle.css',
	'js' => 'dist/directorymenu.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];