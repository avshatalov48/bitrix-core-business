<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/openlines-v2-content.bundle.css',
	'js' => 'dist/openlines-v2-content.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'imopenlines.v2.component.content.openlines',
	],
	'skip_core' => true,
];
