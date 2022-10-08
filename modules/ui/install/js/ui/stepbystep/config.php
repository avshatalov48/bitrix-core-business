<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/stepbystep.bundle.css',
	'js' => 'dist/stepbystep.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.hint',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];