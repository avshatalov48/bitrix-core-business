<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/canvas.bundle.css',
	'js' => 'dist/canvas.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];