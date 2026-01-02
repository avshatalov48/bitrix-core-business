<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/menu.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.system.menu.vue',
	],
	'skip_core' => true,
];
