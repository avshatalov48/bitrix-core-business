<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/openline-container.bundle.css',
	'js' => 'dist/openline-container.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'imopenlines.v2.component.list.container.recent',
	],
	'skip_core' => true,
];