<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/loc-mixin.bundle.css',
	'js' => 'dist/loc-mixin.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
