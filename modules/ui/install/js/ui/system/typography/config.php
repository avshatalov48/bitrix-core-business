<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/typography.bundle.css',
	'js' => 'dist/typography.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
