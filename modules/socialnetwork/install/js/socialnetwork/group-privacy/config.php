<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/group-privacy.bundle.css',
	'js' => 'dist/group-privacy.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
	],
	'skip_core' => false,
];