<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/avatar.bundle.css',
	'js' => 'dist/avatar.bundle.js',
	'rel' => [
		'ui.design-tokens.air',
		'main.core',
	],
	'skip_core' => false,
];