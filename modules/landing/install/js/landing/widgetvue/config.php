<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/widgetvue.bundle.css',
	'js' => 'dist/widgetvue.bundle.js',
	'rel' => [
		'landing.backend',
		'main.core',
	],
	'skip_core' => false,
];
