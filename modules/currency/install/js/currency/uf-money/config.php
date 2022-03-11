<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/uf-money.bundle.css',
	'js' => 'dist/uf-money.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
