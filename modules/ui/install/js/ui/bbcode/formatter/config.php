<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/formatter.bundle.css',
	'js' => 'dist/formatter.bundle.js',
	'rel' => [
		'ui.bbcode.parser',
		'main.core',
		'ui.bbcode.model',
	],
	'skip_core' => false,
];
