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
		'im.v2.application.core',
		'im.v2.component.old-chat-embedding.recent-list',
		'im.v2.provider.pull',
		'im.v2.const',
	],
	'skip_core' => true,
];