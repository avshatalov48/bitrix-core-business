<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/creation-guide.bundle.css',
	'js' => 'dist/creation-guide.bundle.js',
	'rel' => [
		'main.core',
		'sidepanel',
	],
	'skip_core' => false,
];
