<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/avatar.bundle.css',
	'js' => 'dist/avatar.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.avatar',
		'ui.vue3',
	],
	'skip_core' => true,
];
