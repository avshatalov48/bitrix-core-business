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
		'im.v2.application.core',
		'im.v2.component.old-chat-embedding.left-panel',
	],
	'skip_core' => true,
];