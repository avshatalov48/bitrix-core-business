<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bulk-actions.bundle.css',
	'js' => 'dist/bulk-actions.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.application.core',
		'im.v2.lib.esc-manager',
	],
	'skip_core' => true,
];
