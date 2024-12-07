<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/engine.bundle.css',
	'js' => 'dist/engine.bundle.js',
	'rel' => [
		'main.loader',
		'ui.vue3',
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];
