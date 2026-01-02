<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/router.bundle.css',
	'js' => 'dist/router.bundle.js',
	'rel' => [
		'sidepanel',
		'main.core',
	],
	'skip_core' => false,
];
