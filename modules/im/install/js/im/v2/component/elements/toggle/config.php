<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/toggle.bundle.css',
	'js' => 'dist/toggle.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
