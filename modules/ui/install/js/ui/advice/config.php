<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/advice.bundle.css',
	'js' => 'dist/advice.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];