<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/generation-observer.bundle.css',
	'js' => 'dist/generation-observer.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
