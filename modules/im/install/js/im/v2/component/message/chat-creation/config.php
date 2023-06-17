<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat-creation-message.bundle.css',
	'js' => 'dist/chat-creation-message.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.component.elements',
		'im.v2.component.entity-selector',
		'im.public',
	],
	'skip_core' => false,
];