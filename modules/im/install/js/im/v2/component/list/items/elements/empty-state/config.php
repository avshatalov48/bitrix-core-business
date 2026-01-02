<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/empty-state.bundle.css',
	'js' => 'dist/empty-state.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];