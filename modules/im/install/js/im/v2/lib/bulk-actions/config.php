<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bulk-actions.bundle.css',
	'js' => 'dist/bulk-actions.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'im.v2.const',
		'im.v2.application.core',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];
