<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/logo.bundle.css',
	'js' => 'dist/logo.bundle.js',
	'rel' => [
		'main.core',
		'socialnetwork.common',
	],
	'skip_core' => false,
];