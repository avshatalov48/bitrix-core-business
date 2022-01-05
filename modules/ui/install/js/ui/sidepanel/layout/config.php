<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bundle.css',
	'js' => 'dist/bundle.js',
	'rel' => [
		'sidepanel',
		'main.core',
		'ui.buttons',
		'ui.sidepanel.menu',
		'main.core.events',
	],
	'skip_core' => false,
];
