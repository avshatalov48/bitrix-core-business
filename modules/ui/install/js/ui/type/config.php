<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/type.bundle.css',
	'js' => 'dist/type.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
