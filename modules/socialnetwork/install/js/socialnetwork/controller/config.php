<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/controller.bundle.css',
	'js' => 'dist/controller.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];