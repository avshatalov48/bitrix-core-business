<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/label.bundle.css',
	'js' => 'dist/label.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
