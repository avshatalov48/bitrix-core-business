<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/text-highlighter.bundle.css',
	'js' => 'dist/text-highlighter.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];