<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/call-message.bundle.css',
	'js' => 'dist/call-message.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.default',
		'call.component.call-message',
	],
	'skip_core' => true,
];
