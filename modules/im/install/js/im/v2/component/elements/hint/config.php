<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/hint.bundle.css',
	'js' => 'dist/hint.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.components.hint',
	],
	'skip_core' => true,
];
