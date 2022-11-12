<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/left-panel.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.component.old-chat-embedding.recent-list',
		'im.v2.component.old-chat-embedding.search',
		'im.v2.const',
	],
	'skip_core' => true,
];