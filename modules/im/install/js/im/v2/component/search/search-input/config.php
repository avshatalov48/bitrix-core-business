<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/search-input.bundle.css',
	'js' => 'dist/search-input.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.utils',
	],
	'skip_core' => true,
];