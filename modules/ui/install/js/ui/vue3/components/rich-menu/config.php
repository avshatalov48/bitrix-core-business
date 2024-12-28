<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rich-menu.bundle.css',
	'js' => 'dist/rich-menu.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.components.hint',
		'ui.vue3.components.popup',
	],
	'skip_core' => true,
];
