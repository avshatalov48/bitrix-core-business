<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/content.bundle.css',
	'js' => 'dist/content.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'landing.main',
		'landing.ui.panel.base',
		'main.core',
		'landing.utils',
	],
	'skip_core' => false,
];