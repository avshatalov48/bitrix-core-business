<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat.bundle.css',
	'js' => 'dist/chat.bundle.js',
	'rel' => [
		'main.core',
		'ai.copilot-chat.core',
		'ai.copilot-chat.ui',
	],
	'skip_core' => false,
];
