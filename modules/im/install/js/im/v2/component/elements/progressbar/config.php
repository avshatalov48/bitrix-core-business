<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/progressbar.bundle.css',
	'js' => 'dist/progressbar.bundle.js',
	'rel' => [
		'ui.icon-set.api.vue',
		'im.v2.const',
		'main.core',
	],
	'skip_core' => false,
];
