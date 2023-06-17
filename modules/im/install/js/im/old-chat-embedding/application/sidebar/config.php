<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sidebar.bundle.css',
	'js' => 'dist/sidebar.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.component.recent-list',
	],
	'skip_core' => true,
];