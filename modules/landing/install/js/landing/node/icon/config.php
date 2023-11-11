<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/icon.bundle.css',
	'js' => 'dist/icon.bundle.js',
	'rel' => [
		'main.core',
		'landing.node.img',
	],
	'skip_core' => false,
];