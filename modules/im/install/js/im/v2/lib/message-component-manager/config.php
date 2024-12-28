<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/message-component-manager.bundle.css',
	'js' => 'dist/message-component-manager.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.utils',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.smile-manager',
		'imopenlines.v2.lib.message-manager',
	],
	'skip_core' => true,
];
