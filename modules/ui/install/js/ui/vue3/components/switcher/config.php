<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/switcher.bundle.css',
	'js' => 'dist/switcher.bundle.js',
	'rel' => [
		'main.core',
		'ui.switcher',
	],
	'skip_core' => false,
];
