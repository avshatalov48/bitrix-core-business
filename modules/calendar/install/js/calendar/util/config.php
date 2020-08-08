<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/util.bundle.css',
	'js' => 'dist/util.bundle.js',
	'rel' => [
		'main.core',
		'ui.notification',
	],
	'skip_core' => false,
];