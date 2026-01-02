<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/dropdown.bundle.css',
	'js' => 'dist/dropdown.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.popup',
		'ui.forms',
	],
	'skip_core' => true,
];
