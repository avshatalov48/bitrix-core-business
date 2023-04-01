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
		'main.date',
		'ui.notification',
		'main.popup',
		'pull.client',
	],
	'skip_core' => false,
];