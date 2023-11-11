<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat-search-input.bundle.css',
	'js' => 'dist/chat-search-input.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.component.elements',
	],
	'skip_core' => true,
];