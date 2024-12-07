<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/base-message.bundle.css',
	'js' => 'dist/base-message.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.lib.parser',
		'im.v2.component.message.elements',
		'im.v2.const',
		'im.v2.lib.permission',
		'im.v2.lib.channel',
	],
	'skip_core' => true,
];