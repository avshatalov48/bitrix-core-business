<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/ul.bundle.css',
	'js' => 'dist/ul.bundle.js',
	'rel' => [
		'main.core',
		'landing.node',
	],
	'skip_core' => false,
];