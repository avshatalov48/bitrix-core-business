<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/model.bundle.css',
	'js' => 'dist/model.bundle.js',
	'rel' => [
		'ui.bbcode.encoder',
		'main.core',
	],
	'skip_core' => false,
];
