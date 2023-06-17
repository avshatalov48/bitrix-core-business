<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/event-handler.bundle.css',
	'js' => 'dist/event-handler.bundle.js',
	'rel' => [
		'im.lib.clipboard',
		'im.lib.timer',
		'im.lib.uploader',
		'im.lib.utils',
		'main.core.events',
		'im.const',
		'im.lib.logger',
		'main.core',
	],
	'skip_core' => false,
];