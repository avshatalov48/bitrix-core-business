<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/limit.bundle.css',
	'js' => 'dist/limit.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];