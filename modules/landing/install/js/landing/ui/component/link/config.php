<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/link.bundle.css',
	'js' => 'dist/link.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'main.core',
		'main.core.events',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];