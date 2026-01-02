<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/conference-creation.bundle.css',
	'js' => 'dist/conference-creation.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.public',
		'im.v2.component.elements.button',
		'im.v2.component.message.base',
		'im.v2.lib.notifier',
		'call.lib.analytics',
	],
	'skip_core' => true,
];