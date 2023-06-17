<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/left-panel.bundle.css',
	'js' => 'dist/left-panel.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.component.left-panel',
	],
	'skip_core' => true,
];