<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/basefield.bundle.css',
	'js' => 'dist/basefield.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'main.core',
		'main.core.events',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];