<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.popup',
		'main.core.events',
		'ui.dialogs.messagebox',
		'im.old-chat-embedding.const',
		'main.core',
	],
	'skip_core' => false,
];