<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/tool-availability-manager.bundle.css',
	'js' => 'dist/tool-availability-manager.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];