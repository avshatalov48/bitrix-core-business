<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/send-button.bundle.css',
	'js' => 'dist/send-button.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.const',
		'im.v2.lib.utils',
	],
	'skip_core' => true,
];
