<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/parser.bundle.css',
	'js' => 'dist/parser.bundle.js',
	'rel' => [
		'ui.bbcode.ast-processor',
		'main.core',
		'ui.bbcode.encoder',
		'ui.linkify',
		'ui.bbcode.model',
	],
	'skip_core' => false,
];