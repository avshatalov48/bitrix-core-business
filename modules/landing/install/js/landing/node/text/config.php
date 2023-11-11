<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/text.bundle.css',
	'js' => 'dist/text.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.node',
		'landing.node.text.tableeditor',
	],
	'skip_core' => true,
];