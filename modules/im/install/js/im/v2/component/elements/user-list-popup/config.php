<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/user-list-popup.bundle.css',
	'js' => 'dist/user-list-popup.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.elements.popup',
		'im.v2.component.elements.loader',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.user',
		'im.public',
		'im.v2.model',
		'im.v2.component.elements.avatar',
		'im.v2.component.elements.chat-title',
	],
	'skip_core' => true,
];
