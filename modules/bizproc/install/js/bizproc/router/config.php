<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/router.bundle.css',
	'js' => 'dist/router.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'sidepanel',
	],
	'skip_core' => true,
];
