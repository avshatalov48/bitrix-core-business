<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/auto-delete.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.lib.permission',
		'im.v2.const',
	],
	'skip_core' => false,
];
