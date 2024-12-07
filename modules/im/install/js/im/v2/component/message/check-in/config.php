<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/check-in.bundle.css',
	'js' => 'dist/check-in.bundle.js',
	'rel' => [
		'main.core',
		'stafftrack.user-statistics-link',
		'im.v2.lib.analytics',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
		'im.v2.component.message.default',
	],
	'skip_core' => false,
];
