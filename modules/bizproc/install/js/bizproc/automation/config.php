<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/automation.bundle.css',
	'js' => 'dist/automation.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];