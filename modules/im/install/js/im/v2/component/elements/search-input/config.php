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
		'im.v2.lib.esc-manager',
		'im.v2.component.elements.loader',
	],
	'skip_core' => true,
];
