<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/tabs.bundle.css',
	'js' => 'dist/tabs.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
