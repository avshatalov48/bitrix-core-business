<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/text-formatter.bundle.css',
	'js' => 'dist/text-formatter.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.bbcode.formatter',
	],
	'skip_core' => true,
];
