<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/whats-new.bundle.css',
	'js' => 'dist/whats-new.bundle.js',
	'rel' => [
		'main.core.events',
		'main.popup',
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];