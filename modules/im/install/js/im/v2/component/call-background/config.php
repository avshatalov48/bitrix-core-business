<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/call-background.bundle.css',
	'js' => 'dist/call-background.bundle.js',
	'rel' => [
		'ui.vue3',
		'ui.buttons',
		'ui.fonts.opensans',
		'im.v2.lib.progressbar',
		'ui.info-helper',
		'im.v2.lib.utils',
		'im.v2.lib.desktop-api',
		'rest.client',
		'im.v2.const',
		'main.core',
		'main.core.events',
		'im.v2.lib.logger',
		'im.lib.uploader',
		'ui.notification',
	],
	'skip_core' => false,
];