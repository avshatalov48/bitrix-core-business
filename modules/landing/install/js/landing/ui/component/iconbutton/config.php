<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iconbutton.bundle.css',
	'js' => 'dist/iconbutton.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];