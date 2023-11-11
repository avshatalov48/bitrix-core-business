<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat-creation.bundle.css',
	'js' => 'dist/chat-creation.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.public',
		'im.v2.component.elements',
		'im.v2.component.entity-selector',
		'im.v2.component.message.base',
	],
	'skip_core' => true,
];