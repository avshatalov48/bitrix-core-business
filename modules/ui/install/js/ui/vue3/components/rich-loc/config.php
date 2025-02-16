<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rich-loc.bundle.css',
	'js' => 'dist/rich-loc.bundle.js',
	'rel' => [
		'main.core',
		'ui.vue3',
	],
	'skip_core' => false,
];
