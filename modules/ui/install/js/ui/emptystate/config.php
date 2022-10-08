<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/emptystate.bundle.css',
	'js' => 'dist/emptystate.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];