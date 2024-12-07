<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/encoder.bundle.css',
	'js' => 'dist/encoder.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
