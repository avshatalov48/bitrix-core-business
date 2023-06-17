<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.old-chat-embedding.lib.utils',
		'ui.fonts.opensans',
		'ui.vue3',
		'im.old-chat-embedding.const',
	],
	'skip_core' => true,
];