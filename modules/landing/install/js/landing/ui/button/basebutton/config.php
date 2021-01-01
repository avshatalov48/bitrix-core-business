<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/basebutton.bundle.css',
	'js' => 'dist/basebutton.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];