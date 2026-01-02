<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/block-diagram.bundle.css',
	'js' => 'dist/block-diagram.bundle.js',
	'rel' => [
		'main.popup',
		'main.polyfill.intersectionobserver',
		'ui.icon-set.api.vue',
		'main.core',
		'ui.vue3',
	],
	'skip_core' => false,
];