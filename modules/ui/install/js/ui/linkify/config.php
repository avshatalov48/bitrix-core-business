<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/linkify.bundle.css',
	'js' => 'dist/linkify.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
