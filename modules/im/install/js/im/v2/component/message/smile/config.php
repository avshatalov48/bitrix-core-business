<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/smile.bundle.css',
	'js' => 'dist/smile.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
		'im.v2.lib.parser',
	],
	'skip_core' => true,
];