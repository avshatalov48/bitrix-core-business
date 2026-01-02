<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/list-loading-state.bundle.css',
	'js' => 'dist/list-loading-state.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
